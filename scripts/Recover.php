<?php
include '../app/config/connection.php';  // Include the database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_POST['file'];
    $backupDir = __DIR__ . '/../storage/user/deleted_userAccounts/';
    $filePath = $backupDir . $file;

    if (file_exists($filePath)) {
        $pattern = '/(\d+)_.*?_(.*?)_(\d{2}-\d{2}-\d{4}-\d{2}-\d{2}-[APM]{2})\.csv/';
        if (preg_match($pattern, $file, $matches)) {
            $email = ltrim($matches[2], '_') . '@rivaniot.online';
            $date = $matches[3];

            $table = (strpos($file, 'acRemote') !== false) ? 'acRemote' : 'users';

            $fileData = file_get_contents($filePath);
            $lines = explode("\n", $fileData);

            $header = str_getcsv(array_shift($lines));
            $columnCount = count($header);

            $conn->beginTransaction();

            try {
                foreach ($lines as $line) {
                    if (trim($line) === '')
                        continue;

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
            echo "Invalid filename format.";
        }
    } else {
        echo "❌ File not found.\n";
        echo "🔍 Expected path: $filePath\n";
        echo "📍 Current working directory: " . getcwd() . "\n";
        echo "📂 Directory exists? " . (is_dir($backupDir) ? 'YES' : 'NO') . "\n";
        echo "📄 Full file path realpath(): " . realpath($filePath) . "\n";
        echo "📄 File exists via realpath()? " . (file_exists(realpath($filePath)) ? 'YES' : 'NO') . "\n";

        // Optional: List directory contents for more clues
        if (is_dir($backupDir)) {
            echo "📁 Files in directory:\n";
            $files = scandir($backupDir);
            foreach ($files as $f) {
                echo "- $f\n";
            }
        } else {
            echo "⚠️ Backup directory is missing or not accessible.\n";
        }
    }
} else {
    echo "Invalid request.";
}
