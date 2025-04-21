<?php
// Start session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DB connection
require_once '../app/config/connection.php'; // make sure $conn is defined here


//

?>
