<?php
session_start();
header('Content-Type: application/json');
$user_id = $_SESSION['user_id'] ?? 'default_user_id';
echo json_encode(['user_id' => $user_id]);
?>
