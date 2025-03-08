<?php
header('Content-Type: application/json');
session_start();

// Static notifications (always present)
$static_notifications = [
    ["type" => "error", "title" => "Error", "message" => "Light sensor malfunction detected on the 5th floor."],
    ["type" => "info", "title" => "Info", "message" => "Room 1 temperature is now within the optimal range (24°C)."],
    ["type" => "warning", "title" => "Warning", "message" => "Room 2 temperature is rising above the threshold (32°C)."],
    ["type" => "success", "title" => "Success", "message" => "Water pump has successfully refilled the tank to 100% capacity."]
];

// Ensure static notifications are always included
if (!isset($_SESSION['notifications'])) {
    $_SESSION['notifications'] = $static_notifications;
}

// Prevent duplicate static notifications
foreach ($static_notifications as $static) {
    $exists = array_filter($_SESSION['notifications'], fn($notif) => $notif['message'] === $static['message']);
    if (!$exists) {
        $_SESSION['notifications'][] = $static;
    }
}

// **Dynamic notifications based on different scenarios**
$dynamic_scenarios = [
    ["type" => "error", "title" => "Error", "message" => "Gate access camera on the ground floor is offline."],
    ["type" => "warning", "title" => "Warning", "message" => "Water level is dropping below 20% in the storage tank."],
    ["type" => "success", "title" => "Success", "message" => "Backup generator successfully activated due to a power outage."],
    ["type" => "info", "title" => "Info", "message" => "Security system checked – all sensors are functioning properly."]
];

// **Add a new dynamic notification**
$random_scenario = $dynamic_scenarios[array_rand($dynamic_scenarios)];
array_unshift($_SESSION['notifications'], $random_scenario);

// **Limit stored notifications to the latest 10**
$_SESSION['notifications'] = array_slice($_SESSION['notifications'], 0, 10);

// Return JSON response
echo json_encode($_SESSION['notifications']);
?>
