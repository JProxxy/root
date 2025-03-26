<?php
header('Content-Type: application/json');
session_start();
include '../app/config/connection.php'; // This file should set up your PDO connection as $conn

// -------------------------
// Static notifications (always present)
$static_notifications = [
    ["type" => "error",   "title" => "Error",   "message" => "Light sensor malfunction detected on the 5th floor."],
    ["type" => "info",    "title" => "Info",    "message" => "Room 1 temperature is now within the optimal range (24°C)."],
    ["type" => "warning", "title" => "Warning", "message" => "Room 2 temperature is rising above the threshold (32°C)."],
    ["type" => "success", "title" => "Success", "message" => "Water pump has successfully refilled the tank to 100% capacity."]
];

// Initialize session notifications if not already set
if (!isset($_SESSION['notifications'])) {
    $_SESSION['notifications'] = $static_notifications;
}

// Prevent duplicate static notifications
foreach ($static_notifications as $static) {
    $exists = array_filter($_SESSION['notifications'], function($notif) use ($static) {
        return $notif['message'] === $static['message'];
    });
    if (!$exists) {
        $_SESSION['notifications'][] = $static;
    }
}

// -------------------------
// Dynamic notifications (all set to "info")
// We'll build dynamic notifications from 4 tables: water_tank, room_data, acRemote, customizeAC

$dynamic_notifications = [];

// --- Water Tank ---
try {
    $stmt = $conn->prepare("SELECT * FROM water_tank ORDER BY `timestamp` DESC LIMIT 5");
    $stmt->execute();
    $waterTankRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Water Tank DB Error: " . $e->getMessage());
    $waterTankRecords = [];
}

foreach ($waterTankRecords as $record) {
    $message = sprintf(
        "Water Tank - Device %s reports %d%% water, Water Level: %s, Measured Distance: %s, Recorded at: %s",
        $record['DeviceName'],
        $record['WaterPercentage'],
        $record['WaterLevel'],
        $record['MeasuredDistance'],
        $record['timestamp']
    );
    $dynamic_notifications[] = [
        "type"  => "info",
        "title" => "Water Tank Data",
        "message" => $message
    ];
}

// --- Room Data ---
try {
    $stmt = $conn->prepare("SELECT * FROM room_data ORDER BY `timestamp` DESC LIMIT 5");
    $stmt->execute();
    $roomRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Room Data DB Error: " . $e->getMessage());
    $roomRecords = [];
}

foreach ($roomRecords as $record) {
    $message = sprintf(
        "Room Data - Device %s reports temperature %.2f°C and humidity %.2f%% at %s",
        $record['deviceName'],
        $record['temperature'],
        $record['humidity'],
        $record['timestamp']
    );
    $dynamic_notifications[] = [
        "type"  => "info",
        "title" => "Room Data",
        "message" => $message
    ];
}

// --- AC Remote ---
try {
    // Order by id descending since there is no timestamp column provided
    $stmt = $conn->prepare("SELECT * FROM acRemote ORDER BY `id` DESC LIMIT 5");
    $stmt->execute();
    $acRemoteRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("AC Remote DB Error: " . $e->getMessage());
    $acRemoteRecords = [];
}

foreach ($acRemoteRecords as $record) {
    $message = sprintf(
        "AC Remote - User %s: Power: %s, Temp: %d°C, Timer: %s, Mode: %s, Fan: %s, Swing: %s, Switch: %s, Sleep: %s",
        $record['user_id'],
        $record['power'],
        $record['temp'],
        $record['timer'],
        $record['mode'],
        $record['fan'],
        $record['swing'],
        $record['switchState'],
        $record['sleep']
    );
    $dynamic_notifications[] = [
        "type"  => "info",
        "title" => "AC Remote",
        "message" => $message
    ];
}

// --- Customize AC ---
try {
    $stmt = $conn->prepare("SELECT * FROM customizeAC ORDER BY `customizeTime` DESC LIMIT 5");
    $stmt->execute();
    $customizeACRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Customize AC DB Error: " . $e->getMessage());
    $customizeACRecords = [];
}

foreach ($customizeACRecords as $record) {
    $message = sprintf(
        "Customize AC - User %s set min temp %.2f°C at %s and max temp %.2f°C at %s",
        $record['user_id'],
        $record['minTemp'],
        $record['minTempTime'],
        $record['maxTemp'],
        $record['maxTempTime']
    );
    $dynamic_notifications[] = [
        "type"  => "info",
        "title" => "Customize AC",
        "message" => $message
    ];
}

// -------------------------
// Merge dynamic notifications into session notifications
foreach ($dynamic_notifications as $notif) {
    $exists = array_filter($_SESSION['notifications'], function($n) use ($notif) {
        return $n['message'] === $notif['message'];
    });
    if (!$exists) {
        array_unshift($_SESSION['notifications'], $notif);
    }
}

// Limit stored notifications to the latest 10
$_SESSION['notifications'] = array_slice($_SESSION['notifications'], 0, 10);

// Return the notifications as JSON
echo json_encode($_SESSION['notifications']);
?>
