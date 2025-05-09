<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Retrieve JSON payload
$data = json_decode(file_get_contents('php://input'), true);

// Validate confirmation field
$confirm = isset($data['confirm']) ? trim($data['confirm']) : '';
if (strtolower($confirm) !== 'delete') {
    echo json_encode(['success' => false, 'message' => "Please type 'delete' to confirm account deletion."]);
    exit;
}

$userId = $_SESSION['user_id'];
// Capture the user-provided password into a separate variable before including the connection
$userInputPassword = isset($data['password']) ? trim($data['password']) : null;

// Include database connection (this file uses $password for the DB, which won't affect $userInputPassword)
require_once '../app/config/connection.php';

// Temporary debug info array (remove in production)
$debugInfo = [
    'userInputPassword' => $userInputPassword,
    'sessionUserId' => $userId
];

try {
    // Begin transaction
    $conn->beginTransaction();

    // Fetch user data
    $stmt = $conn->prepare("SELECT password, email FROM users WHERE user_id = ?");
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        throw new Exception('User not found.');
    }

    $hashedPassword = $userData['password'];
    $userEmail = $userData['email'];
    $debugInfo['storedHash'] = $hashedPassword;

    // Validate password for non-OAuth users using the separate variable
    if (!is_null($hashedPassword)) {
        if (empty($userInputPassword) || !password_verify($userInputPassword, $hashedPassword)) {
            throw new Exception('Incorrect password.');
        }
    }

    // Backup configuration
    $backupDir = '../storage/user/deleted_userAccounts/';
    if (!is_dir($backupDir) && !mkdir($backupDir, 0755, true)) {
        throw new Exception('Failed to create backup directory.');
    }

    // Sanitize email for filename
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
            $backupStmt->bindValue(1, $userId, PDO::PARAM_INT);
            $backupStmt->execute();
            $rows = $backupStmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                // Extract username from email (everything before '@')
                $shortEmail = explode('@', $userEmail)[0];

                // Get the current time in a human-readable format
                $timeNow = date('m-d-Y-h-i-A');  // e.g., 04-09-2025-05-00-PM

                // Construct the filename in the desired format
                $filename = sprintf(
                    '%s%d_%s_%s_%s.csv',
                    $backupDir,
                    $userId,
                    $shortEmail,
                    $tableName,
                    $timeNow
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
            $deleteStmt->bindValue(1, $userId, PDO::PARAM_INT);
            if (!$deleteStmt->execute()) {
                throw new Exception('Error during data deletion.');
            }
        }
    }

    // Finally, delete the account from the users table
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    if (!$stmt->execute()) {
        throw new Exception('Error deleting account.');
    }

    // Commit transaction
    $conn->commit();

    // Destroy the session
    session_destroy();
    echo json_encode([
        'success' => true,
        'message' => 'Account deleted successfully and backup saved.',
        'debug' => $debugInfo  // Debug info for development; remove in production.
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => $debugInfo
    ]);
    exit;
}
?>