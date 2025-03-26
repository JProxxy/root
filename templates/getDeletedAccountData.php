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
    $requestedFile = basename($_GET['file']);
    $filePath = $backupDir . $requestedFile;

    if (!file_exists($filePath)) {
        die("File not found.");
    }

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

$files = array_diff(scandir($backupDir), array('.', '..'));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deleted Accounts Backups</title>

</head>

<body>
<div class="bgMain" style="position: relative; min-height: 100vh;">
    <?php include '../partials/bgMain.php'; ?>
    <div class="center-container" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <div class="containerPart">
            <h1>Deleted Accounts Backups</h1>
            <div class="links-container">
                <?php if (empty($files)): ?>
                    <p class="no-files">No backup files found.</p>
                <?php else: ?>
                    <?php foreach ($files as $file): ?>
                        <a href="?file=<?php echo urlencode($file); ?>">
                            <?php echo htmlspecialchars($file); ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Inline CSS for simple design -->
<style>
    .containerPart {
        padding: 20px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        max-width: 800px;
        margin: 0 auto;
    }
    .links-container {
        max-height: 60vh;
        overflow-y: auto;
        padding: 10px;
        background: #f9f9f9;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .links-container a {
        display: block;
        padding: 10px;
        margin: 5px 0;
        text-decoration: none;
        color: #007BFF;
        border: 1px solid transparent;
        border-radius: 4px;
        transition: background 0.2s, border-color 0.2s;
    }
    .links-container a:hover {
        background: #e9f5ff;
        border-color: #007BFF;
    }
    .no-files {
        text-align: center;
        color: #666;
        padding: 20px;
    }
</style>

</body>

</html>
