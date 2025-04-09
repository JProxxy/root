<?php
include '../app/config/connection.php';  // Include the database connection
session_start();  // Start the session

// Check if user is logged in and has a valid session
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to recover files.";
    exit;
}

// Check if password is correct
if (isset($_POST['password'])) {
    $password = $_POST['password'];
    $user_id = $_SESSION['user_id']; // Get the user_id from the session

    echo "User ID: $user_id<br>";  // Debugging: Print user_id to ensure it's being retrieved

    // Fetch the hashed password from the database for this user
    $sql = "SELECT password FROM users WHERE user_id = :user_id LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debugging: Check if user was fetched successfully
    if ($user) {
        echo "User found: " . $user['password'] . "<br>";  // Print the hashed password (ensure it's safe to display)
    } else {
        echo "User not found.<br>";
    }

    if ($user && password_verify($password, $user['password'])) {
        // If password is correct, proceed with file recovery

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $file = trim($_POST['file']);
            $backupDir = __DIR__ . '/../storage/user/deleted_userAccounts/';
            echo "Backup Directory (realpath): " . realpath($backupDir) . "<br>";
            $filePath = $backupDir . $file;

            echo "Looking for file: $filePath<br>";  // Debugging: Print the full file path

            if (file_exists($filePath)) {
                echo "File exists: $filePath<br>";  // Debugging: Confirm file exists

                $pattern = '/(\d+)_([^_]+)_+([^_]+)_(\d{2}-\d{2}-\d{4}-\d{2}-\d{2}-[AP]M)\.csv/i';
                if (preg_match($pattern, $file, $matches)) {
                    echo "Pattern matched. Matches found:<br>";
                    var_dump($matches);  // Debugging: Show the match results

                    $email = ltrim($matches[2], '_') . '@rivaniot.online';
                    $date = $matches[3];

                    $table = (strpos($file, 'acRemote') !== false) ? 'acRemote' : 'users';

                    $fileData = file_get_contents($filePath);
                    $lines = explode("\n", $fileData);

                    $header = str_getcsv(array_shift($lines));
                    $columnCount = count($header);

                    echo "Header: " . implode(', ', $header) . "<br>";  // Debugging: Print the header

                    $conn->beginTransaction();

                    try {
                        foreach ($lines as $line) {
                            if (trim($line) === '') continue;

                            $data = str_getcsv($line);
                            if (count($data) === $columnCount) {
                                foreach ($data as &$value) {
                                    $value = ($value === '') ? null : $value;
                                }

                                $placeholders = implode(',', array_fill(0, $columnCount, '?'));
                                $sql = "INSERT INTO `$table` (" . implode(',', $header) . ") VALUES ($placeholders)";
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
                        echo "Error restoring the data: " . $e->getMessage();
                    }
                } else {
                    echo "Invalid filename format.<br>";
                }
            } else {
                // Provide more detailed debugging
                echo "File not found: $filePath<br>";  // Add more detailed output for debugging
                // Check if the file exists with file_exists() directly
                echo "Checking if file exists with file_exists(): " . (file_exists($filePath) ? 'Yes' : 'No') . "<br>";
            }
        } else {
            echo "Invalid request.<br>";
        }
    } else {
        // Password is incorrect
        echo "Incorrect password.<br>";
    }
} else {
    echo "Password not provided.<br>";
}
?>
