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

echo "User ID: [$user_id]<br>";  // Debug: Print user_id

// Fetch the hashed password from the database for this user
$sql = "SELECT password FROM users WHERE user_id = :user_id LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Debug: Check if user was fetched successfully
if ($user) {
    echo "User found: [" . $user['password'] . "]<br>";  // Debug: print hashed password
} else {
    echo "<script>console.error('User with ID $user_id not found in the database.');</script>";
    echo "User not found.<br>";
    exit;
}

if (!password_verify($password, $user['password'])) {
    echo "<script>console.error('Incorrect password attempt for user $user_id.');</script>";
    echo "Incorrect password.<br>";
    exit;
}

// At this point, password is correct, so we proceed with file recovery.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = trim($_POST['file']);
    echo "Received filename: [$file]<br>";  // Debug the received filename

    $backupDir = __DIR__ . '/../storage/user/deleted_userAccounts/';
    echo "Backup Directory (raw): [$backupDir]<br>";
    echo "Backup Directory (realpath): [" . realpath($backupDir) . "]<br>";

    $filePath = $backupDir . $file;
    echo "Looking for file: [$filePath]<br>";

    if (file_exists($filePath)) {
        echo "File exists: [$filePath]<br>";  // Debug: Confirm file exists

        // Parse the filename
        // Expected format: 32_jhopscotch_users_04-21-2025-07-27-AM.csv
        // This pattern:
        // Group 1: user_id (digits)
        // Group 2: email portion (rest of characters until the next underscore)
        // Group 3: table name (any characters except underscore)
        // Group 4: timestamp in the format XX-XX-XXXX-XX-XX-[AP]M
        $pattern = '/^(\d+)_([^_]+)_([^_]+)_(\d{2}-\d{2}-\d{4}-\d{2}-\d{2}-(AM|PM))\.csv$/i';

        if (preg_match($pattern, $file, $matches)) {
            echo "Pattern matched. Matches found:<br>";
            var_dump($matches);  // Debug: Display the matches

            // Construct account: table_emailPart@rivaniot.online, e.g. "jhopscotch_users@rivaniot.online"
            $tableName = 'users';  // Use the 'users' table for recovery
            $emailPart = $matches[2];      // e.g., "jhopscotch"
            $account = strtolower($emailPart) . '@rivaniot.online';
            $timestamp = $matches[4];      // e.g., "04-21-2025-07-27-AM"
            $formattedDate = DateTime::createFromFormat('m-d-Y-h-i-A', $timestamp)
                             ->format('Y-m-d H:i:s'); // Convert to Y-m-d H:i:s format

            // Construct the SQL to insert the data into the 'users' table
            echo "Parsed Account: [$account]<br>";
            echo "Parsed Date: [$formattedDate]<br>";

            $fileData = file_get_contents($filePath);
            $lines = explode("\n", $fileData);
            // Get CSV header
            $header = str_getcsv(array_shift($lines));
            $columnCount = count($header);
            echo "CSV Header: " . implode(', ', $header) . "<br>";

            $conn->beginTransaction();
            try {
                foreach ($lines as $line) {
                    if (trim($line) === '')
                        continue;
                    $data = str_getcsv($line);
                    if (count($data) === $columnCount) {
                        // Set the created_at field
                        foreach ($data as $index => &$value) {
                            if ($header[$index] == 'created_at') {
                                $value = $formattedDate;
                            }
                        }

                        // Insert data into the 'users' table
                        $placeholders = implode(',', array_fill(0, $columnCount, '?'));
                        $sql = "INSERT INTO `$tableName` (" . implode(',', $header) . ") VALUES ($placeholders)";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute($data);
                    }
                }
                $conn->commit();

                if (unlink($filePath)) {
                    echo "File recovered successfully, data restored, and CSV deleted.";
                } else {
                    echo "Data restored, but failed to delete the CSV file.";
                }
            } catch (PDOException $e) {
                $conn->rollBack();
                echo "<script>console.error('Error restoring data: " . $e->getMessage() . "');</script>";
                echo "Error restoring the data: " . $e->getMessage();
            }
        } else {
            echo "<script>console.error('Invalid filename format for file: $file');</script>";
            echo "Invalid filename format.<br>";
        }
    } else {
        echo "<script>console.error('File not found: $filePath');</script>";
        echo "File not found: [$filePath]<br>";
        echo "Checking file existence with file_exists(): " . (file_exists($filePath) ? 'Yes' : 'No') . "<br>";
    }
} else {
    echo "<script>console.error('Invalid request method.');</script>";
    echo "Invalid request.<br>";
}
?>
