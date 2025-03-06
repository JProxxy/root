<?php
$host = '52.221.180.50';
$dbname = 'rivan_iot';
$username = 'root';
$password = 'Pa$$word1';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database is working!";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
