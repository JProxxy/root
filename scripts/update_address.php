<?php
// Start the session and include the database connection
session_start();
require_once '../app/config/connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Retrieve and sanitize POST data for the address
$country   = isset($_POST['country']) ? trim($_POST['country']) : '';
$city      = isset($_POST['city']) ? trim($_POST['city']) : '';
$street_address    = isset($_POST['street_address']) ? trim($_POST['street_address']) : '';
$postal_code = isset($_POST['postal_code']) ? trim($_POST['postal_code']) : '';
$barangay  = isset($_POST['barangay']) ? trim($_POST['barangay']) : '';

try {
    // Prepare the SQL update statement
    $query = "UPDATE users 
              SET country = :country, city = :city, street_address = :street_address, postal_code = :postal_code, barangay = :barangay 
              WHERE user_id = :user_id";
    $stmt = $conn->prepare($query);

    // Bind the values
    $stmt->bindValue(':country', $country);
    $stmt->bindValue(':city', $city);
    $stmt->bindValue(':street_address', $street_address);
    $stmt->bindValue(':postal_code', $postal_code);
    $stmt->bindValue(':barangay', $barangay);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    // Execute the query
    $stmt->execute();

    // Return a success JSON response
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // Return an error JSON response on failure
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
