<?php
session_start();
header('Content-Type: application/json');

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
$password = isset($data['password']) ? $data['password'] : null;

// Include database connection
require_once '../app/config/connection.php';

// Start transaction
$conn->begin_transaction();

try {
    // Fetch user data
    $stmt = $conn->prepare("SELECT password, email FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        throw new Exception('User not found.');
    }

    $stmt->bind_result($hashedPassword, $userEmail);
    $stmt->fetch();
    $stmt->close();

    // Validate password for non-OAuth users
    if (!is_null($hashedPassword)) {
        if (empty($password) || !password_verify($password, $hashedPassword)) {
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

    // Get all tables once
    $tablesResult = $conn->query("SHOW TABLES");
    if (!$tablesResult) throw new Exception('Failed to retrieve tables.');
    
    $tables = [];
    while ($tableRow = $tablesResult->fetch_array()) {
        $tables[] = $tableRow[0];
    }

    // Backup phase
    $backupSuccess = true;
    foreach ($tables as $tableName) {
        $columnsResult = $conn->query("DESCRIBE `$tableName`");
        $hasUserIdColumn = false;
        
        while ($column = $columnsResult->fetch_assoc()) {
            if (strtolower($column['Field']) === 'user_id') {
                $hasUserIdColumn = true;
                break;
            }
        }
        $columnsResult->free();

        if ($hasUserIdColumn) {
            // Backup data
            $stmtBackup = $conn->prepare("SELECT * FROM `$tableName` WHERE user_id = ?");
            $stmtBackup->bind_param("i", $userId);
            $stmtBackup->execute();
            $result = $stmtBackup->get_result();
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $stmtBackup->close();

            if (!empty($rows)) {
                $filename = sprintf('%s%d_%s_%s_%d.csv',
                    $backupDir,
                    $userId,
                    $sanitizedEmail,
                    $tableName,
                    time()
                );

                $file = fopen($filename, 'w');
                if ($file === false || !fputcsv($file, array_keys($rows[0]))) {
                    $backupSuccess = false;
                    break;
                }

                foreach ($rows as $row) {
                    if (!fputcsv($file, $row)) {
                        $backupSuccess = false;
                        break 2; // Break both loops
                    }
                }
                fclose($file);
            }
        }
    }

    if (!$backupSuccess) {
        throw new Exception('Backup failed. Account deletion aborted.');
    }

    // Deletion phase
    foreach ($tables as $tableName) {
        $columnsResult = $conn->query("DESCRIBE `$tableName`");
        $hasUserIdColumn = false;
        
        while ($column = $columnsResult->fetch_assoc()) {
            if (strtolower($column['Field']) === 'user_id') {
                $hasUserIdColumn = true;
                break;
            }
        }
        $columnsResult->free();

        if ($hasUserIdColumn) {
            $stmtDelete = $conn->prepare("DELETE FROM `$tableName` WHERE user_id = ?");
            $stmtDelete->bind_param("i", $userId);
            if (!$stmtDelete->execute()) {
                throw new Exception('Error during data deletion.');
            }
            $stmtDelete->close();
        }
    }

    // Delete user account
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        throw new Exception('Error deleting account.');
    }
    $stmt->close();

    // Commit transaction
    $conn->commit();

    // Clear session
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Account deleted successfully and backup saved.']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
?>