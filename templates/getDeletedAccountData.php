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
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #eef2f3;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .bgMain {
            width: 100%;
        }
        .containerPart {
            padding: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 20px auto;
            max-width: 800px;
        }
        .links-container {
            max-height: 60vh; /* Container becomes scrollable if too many links */
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
</head>

<body>
<div class="bgMain">
    <?php include '../partials/bgMain.php'; ?>
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
</body>

</html>
