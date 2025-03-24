<?php
session_start();

// Require authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: ../templates/login.php");
    exit;
}

// Define the backup directory (adjust the path as needed)
$backupDir = __DIR__ . '/../storage/user/deleted_userAccounts/';

// Check if the backup directory exists
if (!is_dir($backupDir)) {
    die("Backup directory not found.");
}

// If a file is requested via GET, serve it for download.
if (isset($_GET['file'])) {
    // Sanitize input to prevent directory traversal
    $requestedFile = basename($_GET['file']);
    $filePath = $backupDir . $requestedFile;
    
    if (!file_exists($filePath)) {
        die("File not found.");
    }
    
    // Set headers to force download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $requestedFile . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    flush();
    readfile($filePath);
    exit;
}

// Otherwise, list the files for download.
$files = array_diff(scandir($backupDir), array('.', '..'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Deleted Accounts Backups</title>
</head>
<body>
    <h1>Deleted Accounts Backups</h1>
    <?php if (empty($files)): ?>
        <p>No backup files found.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($files as $file): ?>
                <li>
                    <a href="?file=<?php echo urlencode($file); ?>">
                        <?php echo htmlspecialchars($file); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
