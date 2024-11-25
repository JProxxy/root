<?php
// Assuming you are using PDO for database connection
try {
    // Database connection (adjust with your credentials)
    $pdo = new PDO('mysql:host=localhost;dbname=your_db_name', 'username', 'password');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the device states from the database
    $stmt = $pdo->query("SELECT device_name, status FROM devices WHERE device_name LIKE 'FFLight%'");
    $deviceStates = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $deviceStates[$row['device_name']] = $row['status'] === 'ON' ? true : false;
    }

    // Return the device states as JSON
    echo json_encode($deviceStates);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
