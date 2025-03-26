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
    <title>Deleted Accounts Backups</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome for icons (optional) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <!-- Custom CSS -->
    <style>
        body {
            background: #f7f7f7;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        .card-header {
            background-color: #007bff;
            color: #fff;
            font-size: 1.25rem;
        }
        .list-group-item a {
            color: #007bff;
            text-decoration: none;
        }
        .list-group-item a:hover {
            text-decoration: underline;
        }
        .no-files {
            font-size: 1.1rem;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                <i class="fa-solid fa-database me-2"></i>Deleted Accounts Backups
            </div>
            <div class="card-body">
                <?php if (empty($files)): ?>
                    <p class="text-center no-files">No backup files found.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($files as $file): ?>
                            <li class="list-group-item">
                                <a href="?file=<?php echo urlencode($file); ?>">
                                    <i class="fa-solid fa-download me-2"></i><?php echo htmlspecialchars($file); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
