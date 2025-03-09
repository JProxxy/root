<?php
header('Content-Type: application/json');

// List of random names
$names = ['John', 'Jane', 'Alex', 'Chris', 'Maria', 'Luis', 'Eva', 'David', 'Sophia', 'Mark'];

$notifications = [];

for ($i = 50; $i >= 1; $i--) {
    $devices = [
        "Air Conditioner",
        "Light " . rand(1, 6), // Random Light 1-6
        "Camera"
    ];

    $device = $devices[array_rand($devices)]; // Randomly select a device
    $action = rand(0, 1) ? "was Opened" : "was Closed"; // Randomly choose Opened/Closed

    // Randomly pick a name from the list
    $randomName = $names[array_rand($names)];

    $notifications[] = [
        "time" => date("h:i A", strtotime("-$i minutes")), // Generates a dynamic timestamp
        "message" => "$randomName - $device $action" // Include the random name in the message
    ];
}

echo json_encode($notifications);
?>
