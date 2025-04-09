<?php
include '../app/config/connection.php';  // Include the database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_POST['file'];

    $backupDir = __DIR__ . '/../storage/user/deleted_userAccounts/';
    $recoveryDir = __DIR__ . '/../storage/user/recovered_userAccounts/';

    if (file_exists($backupDir . $file)) {
        if (!is_dir($recoveryDir)) {
            mkdir($recoveryDir, 0777, true);
        }

        $pattern = '/(\d+)_.*?_(.*?)_(\d{2}-\d{2}-\d{4}-\d{2}-\d{2}-[APM]{2})\.csv/';
        if (preg_match($pattern, $file, $matches)) {
            $email = ltrim($matches[2], '_') . '@rivaniot.online';
            $date = $matches[3];

            $table = (strpos($file, 'acRemote') !== false) ? 'acRemote' : 'users';

            $filePath = $backupDir . $file;
            $fileData = file_get_contents($filePath);
            $lines = explode("\n", $fileData);

            $header = str_getcsv(array_shift($lines));
            $columnCount = count($header);

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

                // Move file to recovery directory
                if (rename($filePath, $recoveryDir . $file)) {
                    // Then delete the file from recovery directory after confirming move success
                    unlink($recoveryDir . $file);
                    echo "File recovered successfully, data restored, and CSV deleted.";
                } else {
                    echo "Data restored but error moving the file to recovery directory.";
                }

            } catch (PDOException $e) {
                $conn->rollBack();
                echo "Error restoring the data: " . $e->getMessage();
            }
        } else {
            echo "Invalid filename format.";
        }
    } else {
        echo "File not found in the backup directory.";
    }
} else {
    echo "Invalid request.";
}
