<?php
session_start();
header('Content-Type: application/json');

// For debugging, enable error reporting (adjust these settings for production)
error_reporting(E_ALL);
ini_set('log_errors', 1);
// ini_set('error_log', '/path/to/php-error.log');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$input = file_get_contents("php://input");
error_log("Input payload: " . $input);
$data = json_decode($input, true);
if (!$data) {
    error_log("Invalid JSON payload.");
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload.']);
    exit;
}

if (!isset($data['user_id']) || !isset($data['action'])) {
    error_log("Missing required parameters. Data received: " . print_r($data, true));
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
    exit;
}

$user_id = $data['user_id'];
$action = $data['action'];
error_log("Received action: $action for user_id: $user_id");

include '../app/config/connection.php';

if (!isset($_SESSION['user_id'])) {
    error_log("Admin not authenticated for action.");
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}
$adminUserId = $_SESSION['user_id'];
error_log("Admin user ID: $adminUserId");

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

function generateDescription($action, $user_id, $adminUsername)
{
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

if ($action === 'delete') {
    if (empty($data['password'])) {
        error_log("Password missing for delete action for user_id: $user_id");
        echo json_encode(['success' => false, 'message' => 'Password is required for deletion.']);
        exit;
    }
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$adminUserId]);
    $hashedPassword = $stmt->fetchColumn();
    if (!$hashedPassword || !password_verify($data['password'], $hashedPassword)) {
        error_log("Invalid password for admin user ID: $adminUserId");
        echo json_encode(['success' => false, 'message' => 'Invalid password.']);
        exit;
    }

    $conn->beginTransaction();
    try {
        // Backup configuration
        $backupDir = '../storage/user/deleted_userAccounts/';
        if (!is_dir($backupDir) && !mkdir($backupDir, 0755, true)) {
            throw new Exception('Failed to create backup directory.');
        }

        // Load every table name
        $tablesStmt = $conn->query("SHOW TABLES");
        $tables = $tablesStmt->fetchAll(PDO::FETCH_NUM);

        $backupSuccess = true;
        foreach ($tables as $tableRow) {
            $tableName = $tableRow[0];

            // Only backup tables containing `user_id`
            $colsStmt = $conn->query("DESCRIBE `$tableName`");
            $cols = $colsStmt->fetchAll(PDO::FETCH_ASSOC);
            $hasUser = false;
            foreach ($cols as $col) {
                if (strtolower($col['Field']) === 'user_id') {
                    $hasUser = true;
                    break;
                }
            }

            if (!$hasUser) {
                continue;
            }

            // Fetch all rows for this user
            $bkStmt = $conn->prepare("SELECT * FROM `$tableName` WHERE user_id = ?");
            $bkStmt->execute([$user_id]);
            $rows = $bkStmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rows)) {
                continue;
            }

            // Build filename: {user_id}_{localPart}_{tableName}_{MM-DD-YYYY-hh-mm-AM/PM}.csv
            $stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $userEmail = $stmt->fetchColumn() ?: '';
            // Extract local part (before '@')
            $emailParts = explode('@', $userEmail);
            $localPart = $emailParts[0];
            $timestamp = date('m-d-Y-h-i-A', time());

            $filename = sprintf(
                '%d_%s_%s_%s.csv',
                $user_id,
                $localPart,
                $tableName,
                $timestamp
            );

            $filePath = $backupDir . $filename;
            $fp = fopen($filePath, 'w');
            if ($fp === false || fputcsv($fp, array_keys($rows[0])) === false) {
                $backupSuccess = false;
                break;
            }

            foreach ($rows as $row) {
                if (fputcsv($fp, $row) === false) {
                    $backupSuccess = false;
                    break 2;
                }
            }
            fclose($fp);
        }

        if (!$backupSuccess) {
            throw new Exception('Backup failed. Account deletion aborted.');
        }

        // Now delete across all tables
        foreach ($tables as $tableRow) {
            $tableName = $tableRow[0];

            $colsStmt = $conn->query("DESCRIBE `$tableName`");
            $cols = $colsStmt->fetchAll(PDO::FETCH_ASSOC);
            $hasUser = false;
            foreach ($cols as $col) {
                if (strtolower($col['Field']) === 'user_id') {
                    $hasUser = true;
                    break;
                }
            }
            if (!$hasUser) {
                continue;
            }

            $delStmt = $conn->prepare("DELETE FROM `$tableName` WHERE user_id = ?");
            if (!$delStmt->execute([$user_id])) {
                throw new Exception("Error deleting from $tableName.");
            }
        }

        // Finally remove the user record
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        if (!$stmt->execute([$user_id])) {
            throw new Exception('Error deleting account.');
        }

        $conn->commit();

        // Log into manageuser
        $desc = generateDescription($action, $user_id, $adminUsername);
        $log = $conn->prepare(
            "INSERT INTO manageuser
              (user_id, username, role_id, type_of_action, to_whom, date, description)
            VALUES (?, ?, ?, ?, ?, NOW(), ?)"
        );
        $log->execute([
            $adminUserId,
            $adminUsername,
            $adminRoleId,
            $action,
            $user_id,
            $desc
        ]);

        echo json_encode(['success' => true, 'message' => 'Account deleted successfully, and backup saved.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle block/unblock
if ($action === 'block' || $action === 'unblock') {
    $newStatus = $action;
    $upd = $conn->prepare("UPDATE users SET mu_status = ? WHERE user_id = ?");
    $res = $upd->execute([$newStatus, $user_id]);

    if ($res) {
        $desc = generateDescription($action, $user_id, $adminUsername);
        $log = $conn->prepare(
            "INSERT INTO manageuser
              (user_id, username, role_id, type_of_action, to_whom, date, description)
            VALUES (?, ?, ?, ?, ?, NOW(), ?)"
        );
        $log->execute([
            $adminUserId,
            $adminUsername,
            $adminRoleId,
            $action,
            $user_id,
            $desc
        ]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update user status.']);
    }
    exit;
}

error_log("Invalid action received: $action for user_id: $user_id");
echo json_encode(['success' => false, 'message' => 'Invalid action.']);
exit;
