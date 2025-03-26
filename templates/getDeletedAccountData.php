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
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css" />
    <link rel="stylesheet" href="../assets/css/settings.css" />
    <link rel="stylesheet" href="../assets/css/settings-profile.css" />
    <link rel="stylesheet" href="../assets/css/settings-password.css" />
    <link rel="stylesheet" href="../assets/css/sa.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
</head>

<body>
    <div class="bgMain">
        <?php include '../partials/bgMain.php'; ?>
        <div class="containerPart">
            <!-- Header -->
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
        </div>
    </div>
</body>

</html>

