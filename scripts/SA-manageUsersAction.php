<?php
session_start();
header('Content-Type: application/json');

// For debugging, enable error reporting (adjust these settings for production)
error_reporting(E_ALL);
ini_set('log_errors', 1);
// Optionally, set a custom error log file (make sure the file is writable):
// ini_set('error_log', '/path/to/php-error.log');

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Read JSON payload from the request body
$input = file_get_contents("php://input");
error_log("Input payload: " . $input);

$data = json_decode($input, true);

if (!$data) {
    error_log("Invalid JSON payload.");
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload.']);
    exit;
}

// Validate required parameters (user_id and action)
if (!isset($data['user_id']) || !isset($data['action'])) {
    error_log("Missing required parameters. Data received: " . print_r($data, true));
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
    exit;
}

$user_id = $data['user_id'];
$action = $data['action'];
error_log("Received action: $action for user_id: $user_id");

// Include your database connection file (using PDO)
// Make sure this file returns a valid $conn PDO object
include '../app/config/connection.php';

// If there's an error with the connection file, it may output PHP warnings that break JSON output.

// Retrieve session data for the current admin user
if (!isset($_SESSION['user_id'])) {
    error_log("Admin not authenticated for action.");
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$adminUserId = $_SESSION['user_id'];
error_log("Admin user ID: $adminUserId");

// Retrieve the admin user's username and role_id from the database
$stmt = $conn->prepare("SELECT username, role_id FROM users WHERE user_id = ?");
$stmt->execute([$adminUserId]);
$user = $stmt->fetch();

if (!$user) {
    error_log("Admin user not found for user ID: $adminUserId");
    echo json_encode(['success' => false, 'message' => 'Admin user not found.']);
    exit;
}

$adminUsername = $user['username'];
$adminRoleId = $user['role_id'];

// Function to generate description based on action
function generateDescription($action, $user_id, $adminUsername) {
    switch ($action) {
        case 'block':
            return "Admin $adminUsername blocked user with ID $user_id";
        case 'unblock':
            return "Admin $adminUsername unblocked user with ID $user_id";
        case 'delete':
            return "Admin $adminUsername deleted user with ID $user_id";
        default:
            return "Admin $adminUsername performed an unknown action on user with ID $user_id";
    }
}

// Handle "delete" action
// Handle "delete" action
if ($action === 'delete') {
    if (!isset($data['password']) || empty($data['password'])) {
        error_log("Password missing for delete action for user_id: $user_id");
        echo json_encode(['success' => false, 'message' => 'Password is required for deletion.']);
        exit;
    }
    
    // Retrieve the admin user's hashed password from the database
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$adminUserId]);
    $hashedPassword = $stmt->fetchColumn();
    
    if (!$hashedPassword || !password_verify($data['password'], $hashedPassword)) {
        error_log("Invalid password for admin user ID: $adminUserId");
        echo json_encode(['success' => false, 'message' => 'Invalid password.']);
        exit;
    }

    // Begin the transaction to ensure data integrity
    $conn->beginTransaction();
    
    try {
        // Backup configuration
        $backupDir = '../storage/user/deleted_userAccounts/';
        if (!is_dir($backupDir) && !mkdir($backupDir, 0755, true)) {
            throw new Exception('Failed to create backup directory.');
        }

        // Sanitize email for filename
        $stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $userEmail = $stmt->fetchColumn();
        $sanitizedEmail = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $userEmail);

        // Get all tables
        $tablesStmt = $conn->query("SHOW TABLES");
        $tables = $tablesStmt->fetchAll(PDO::FETCH_NUM); // fetch as numeric array

        // Backup phase
        $backupSuccess = true;
        foreach ($tables as $tableRow) {
            $tableName = $tableRow[0];

            // Check if the table has a column named 'user_id'
            $columnsStmt = $conn->query("DESCRIBE `$tableName`");
            $columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);
            $hasUserIdColumn = false;
            foreach ($columns as $column) {
                if (strtolower($column['Field']) === 'user_id') {
                    $hasUserIdColumn = true;
                    break;
                }
            }

            if ($hasUserIdColumn) {
                // Backup data for this table
                $backupStmt = $conn->prepare("SELECT * FROM `$tableName` WHERE user_id = ?");
                $backupStmt->bindValue(1, $user_id, PDO::PARAM_INT);
                $backupStmt->execute();
                $rows = $backupStmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($rows)) {
                    $filename = sprintf('%s%d_%s_%s_%d.csv',
                        $backupDir,
                        $user_id,
                        $sanitizedEmail,
                        $tableName,
                        time()
                    );

                    $file = fopen($filename, 'w');
                    if ($file === false || fputcsv($file, array_keys($rows[0])) === false) {
                        $backupSuccess = false;
                        break;
                    }

                    foreach ($rows as $row) {
                        if (fputcsv($file, $row) === false) {
                            $backupSuccess = false;
                            break 2; // Break out of both loops
                        }
                    }
                    fclose($file);
                }
            }
        }

        if (!$backupSuccess) {
            throw new Exception('Backup failed. Account deletion aborted.');
        }

        // Deletion phase: Loop through each table and delete user data
        foreach ($tables as $tableRow) {
            $tableName = $tableRow[0];

            $columnsStmt = $conn->query("DESCRIBE `$tableName`");
            $columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);
            $hasUserIdColumn = false;
            foreach ($columns as $column) {
                if (strtolower($column['Field']) === 'user_id') {
                    $hasUserIdColumn = true;
                    break;
                }
            }

            if ($hasUserIdColumn) {
                $deleteStmt = $conn->prepare("DELETE FROM `$tableName` WHERE user_id = ?");
                $deleteStmt->bindValue(1, $user_id, PDO::PARAM_INT);
                if (!$deleteStmt->execute()) {
                    throw new Exception('Error during data deletion.');
                }
            }
        }

        // Finally, delete the account from the users table
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            throw new Exception('Error deleting account.');
        }

        // Commit transaction
        $conn->commit();

        // Log the action into the manageuser table
        $description = generateDescription($action, $user_id, $adminUsername);
        $stmt = $conn->prepare("INSERT INTO manageuser (user_id, username, role_id, type_of_action, to_whom, date, description)
            VALUES (?, ?, ?, ?, ?, NOW(), ?)");
        $stmt->execute([$adminUserId, $adminUsername, $adminRoleId, $action, $user_id, $description]);

        // Return success response
        echo json_encode(['success' => true, 'message' => 'Account deleted successfully, and backup saved.']);
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle "block" or "unblock" actions
if ($action === 'block' || $action === 'unblock') {
    $newStatus = $action; 
    
    // Update mu_status to the action value
    $stmt = $conn->prepare("UPDATE users SET mu_status = ? WHERE user_id = ?");
    $result = $stmt->execute([$newStatus, $user_id]);
    
    if ($result) {
        // Log the action into the manageuser table
        $description = generateDescription($action, $user_id, $adminUsername);
        $stmt = $conn->prepare("INSERT INTO manageuser (user_id, username, role_id, type_of_action, to_whom, date, description)
            VALUES (?, ?, ?, ?, ?, NOW(), ?)");
        $stmt->execute([$adminUserId, $adminUsername, $adminRoleId, $action, $user_id, $description]);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update user status.']);
    }
    exit;
}

// Invalid action
error_log("Invalid action received: $action for user_id: $user_id");
echo json_encode(['success' => false, 'message' => 'Invalid action.']);
exit;
