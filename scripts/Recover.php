<?php
// Enable error reporting for all errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../app/config/connection.php';  // Include the database connection
session_start();  // Start the session

// Check if user is logged in and has a valid session
if (!isset($_SESSION['user_id'])) {
    echo "<script>console.error('User not logged in. Attempted file recovery.');</script>";
    echo "You must be logged in to recover files.";
    exit;
}

// Check if password is provided
if (!isset($_POST['password'])) {
    echo "<script>console.error('Password not provided.');</script>";
    echo "Password not provided.<br>";
    exit;
}

$password = $_POST['password'];
$user_id = $_SESSION['user_id']; // Get the user_id from the session

echo "User ID: [{$user_id}]<br>";  // Debug: Print user_id

// Fetch the hashed password from the database for this user
$sql = "SELECT password FROM users WHERE user_id = :user_id LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Debug: Check if user was fetched successfully
if ($user) {
    echo "User found: [{$user['password']}]<br>";  // Debug: print hashed password
} else {
    echo "<script>console.error('User with ID {$user_id} not found in the database.');</script>";
    echo "User not found.<br>";
    exit;
}

if (!password_verify($password, $user['password'])) {
    echo "<script>console.error('Incorrect password attempt for user {$user_id}.');</script>";
    echo "Incorrect password.<br>";
    exit;
}

// At this point, password is correct, so we proceed with file recovery.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = trim($_POST['file']);
    echo "Received filename: [{$file}]<br>";  // Debug the received filename

    $backupDir = __DIR__ . '/../storage/user/deleted_userAccounts/';
    echo "Backup Directory (raw): [{$backupDir}]<br>";
    echo "Backup Directory (realpath): [" . realpath($backupDir) . "]<br>";

    $filePath = $backupDir . $file;
    echo "Looking for file: [{$filePath}]<br>";

    if (!file_exists($filePath)) {
        echo "<script>console.error('File not found: {$filePath}');</script>";
        echo "File not found: [{$filePath}]<br>";
        echo "Checking file existence with file_exists(): " . (file_exists($filePath) ? 'Yes' : 'No') . "<br>";
        exit;
    }

    echo "File exists: [{$filePath}]<br>";  // Debug: Confirm file exists

    // Parse the filename with regex
    $pattern = '/^(\d+)_([^_]+)_([^_]+)_(\d{2}-\d{2}-\d{4}-\d{2}-\d{2}-(AM|PM))\.csv$/i';
    if (!preg_match($pattern, $file, $matches)) {
        echo "<script>console.error('Invalid filename format for file: {$file}');</script>";
        echo "Invalid filename format.<br>";
        exit;
    }

    echo "Pattern matched. Matches found:<br>";
    var_dump($matches);  // Debug: Display the matches

    // Extract parts
    $user_id   = $matches[1];
    $emailPart = $matches[2];
    $tableName = $matches[3];
    $timestamp = $matches[4];

    // Build email and formatted date
    $email         = strtolower($emailPart) . '@rivaniot.online';
    $formattedDate = DateTime::createFromFormat('m-d-Y-h-i-A', $timestamp)
                         ->format('Y-m-d H:i:s');

    echo "Parsed Account: [{$email}]<br>";
    echo "Parsed Date: [{$formattedDate}]<br>";
    echo "Destination Table: [{$tableName}]<br>";

    // Read CSV
    $fileData    = file_get_contents($filePath);
    $lines       = explode("\n", trim($fileData));
    $header      = str_getcsv(array_shift($lines));
    $columnCount = count($header);
    echo "CSV Header: " . implode(', ', $header) . "<br>";

    // Columns to treat as nullable datetimes
    $datetimeFields = [
        'minTempTime',
        'maxTempTime',
        'last_login',
        'updated_at',
        'lock_until',
        'reset_token_expiry',
        'otp_expiry',
    ];

    // Integer columns to default to 0 if empty
    $intFieldsZero = [
        'timer',
        'otp_code',
        'failed_attempts',
    ];

    // ENUM columns to treat as NULL if empty
    $enumFieldsNull = [
        'status',
        'gender',
        'isEmailVerified',
        'mu_status',
    ];

    // Plain‑text columns to treat as NULL if empty
    $stringFieldsNull = [
        'rfid',
    ];

    // Begin transaction
    $conn->beginTransaction();
    try {
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $data = str_getcsv($line);
            if (count($data) !== $columnCount) {
                continue;
            }

            // Normalize each column value
            foreach ($data as $idx => &$value) {
                $col = $header[$idx];

                // Override created_at and email for every row
                if ($col === 'created_at') {
                    $value = $formattedDate;
                }
                if ($col === 'email') {
                    $value = $email;
                }

                // Empty datetimes → NULL
                if (in_array($col, $datetimeFields, true) && $value === '') {
                    $value = null;
                }

                // Empty integers → 0
                if (in_array($col, $intFieldsZero, true) && $value === '') {
                    $value = 0;
                }

                // Empty ENUMs → NULL
                if (in_array($col, $enumFieldsNull, true) && $value === '') {
                    $value = null;
                }

                // Empty plain‑text fields → NULL
                if (in_array($col, $stringFieldsNull, true) && $value === '') {
                    $value = null;
                }
            }
            unset($value);

            // Build and execute INSERT
            $placeholders = implode(',', array_fill(0, $columnCount, '?'));
            $sql = "INSERT INTO `{$tableName}` (" . implode(',', $header) . ") VALUES ({$placeholders})";
            $stmt = $conn->prepare($sql);
            $stmt->execute($data);
        }

        $conn->commit();

        // Clean up
        if (unlink($filePath)) {
            echo "File recovered successfully, data restored, and CSV deleted.";
        } else {
            echo "Data restored, but failed to delete the CSV file.";
        }
    } catch (PDOException $e) {
        $conn->rollBack();
        echo "<script>console.error('Error restoring data: " . addslashes($e->getMessage()) . "');</script>";
        echo "Error restoring the data: " . $e->getMessage();
    }
} else {
    echo "<script>console.error('Invalid request method.');</script>";
    echo "Invalid request.<br>";
}
?>
