<?php
header('Content-Type: application/json');
session_start();
include '../app/config/connection.php'; // sets up PDO as $conn

// -------------------------
// Static notifications (always present)
// $static_notifications = [
//     ["type" => "error",   "title" => "Error",   "message" => "Light sensor malfunction detected on the 5th floor."],
//     ["type" => "info",    "title" => "Info",    "message" => "Room 1 temperature is now within the optimal range (24°C)."],
//     ["type" => "warning", "title" => "Warning", "message" => "Room 2 temperature is rising above the threshold (32°C)."],
//     ["type" => "success", "title" => "Success", "message" => "Water pump has successfully refilled the tank to 100% capacity."]
// ];

// // Initialize session notifications
// if (!isset($_SESSION['notifications'])) {
//     $_SESSION['notifications'] = $static_notifications;
// }
// foreach ($static_notifications as $static) {
//     $exists = array_filter($_SESSION['notifications'], fn($n) => $n['message'] === $static['message']);
//     if (!$exists) {
//         $_SESSION['notifications'][] = $static;
//     }
// }

// -------------------------
// Dynamic notifications container
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
foreach ($waterTankRecords as $r) {
    $ts = date('l g:ia', strtotime($r['timestamp']));
    $msg = sprintf(
        "Level: %s<br>Water Tank is at %.0f%%<br>Distance: %.2f cm<br>Recorded at: %s",
        $r['WaterLevel'], $r['WaterPercentage'], $r['MeasuredDistance'], $ts
    );
    $dynamic_notifications[] = ['type'=>'info','title'=>'Water Tank Data','message'=>$msg];
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
$roomMap = ['ffRoom'=>'First Floor','sfRoom'=>'Second Floor','tfRoom'=>'Third Floor','fofRoom'=>'Fourth Floor','fifRoom'=>'Fifth Floor'];
foreach ($roomRecords as $r) {
    $name = $r['deviceName'];
    foreach ($roomMap as $abbr=>$full) {
        if (strpos($name, $abbr) === 0) { $name = $full; break; }
    }
    $ts = date('l g:ia', strtotime($r['timestamp']));
    $msg = sprintf(
        "Temperature: %.2f°C<br>Humidity: %.2f%%<br>Time: %s",
        $r['temperature'], $r['humidity'], $ts
    );
    $dynamic_notifications[] = ['type'=>'info','title'=>"Room Data for {$name}",'message'=>$msg];
}

// --- Customize AC threshold notifications based on room data ---
try {
    $stmt = $conn->prepare("SELECT * FROM customizeAC ORDER BY `customizeTime` DESC LIMIT 1");
    $stmt->execute();
    $acSetting = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Customize AC DB Error: " . $e->getMessage());
    $acSetting = [];
}
if ($acSetting) {
    $minTemp = (float)$acSetting['minTemp'];
    $maxTemp = (float)$acSetting['maxTemp'];
    foreach ($roomRecords as $r) {
        // Map room name
        $name = $r['deviceName'];
        foreach ($roomMap as $abbr=>$full) {
            if (strpos($name, $abbr) === 0) { $name = $full; break; }
        }
        $temp = (float)$r['temperature'];
        if ($temp < $minTemp) {
            $type = 'warning';
            $title = 'Warning';
            $message = sprintf(
                "Room %s temperature is below the minimum threshold: %.2f°C.",
                $name, $temp
            );
        } elseif ($temp > $maxTemp) {
            $type = 'warning';
            $title = 'Warning';
            $message = sprintf(
                "Room %s temperature is above the maximum threshold: %.2f°C.",
                $name, $temp
            );
        } else {
            $type = 'info';
            $title = 'Info';
            $message = sprintf(
                "Room %s temperature is optimal at %.2f°C.",
                $name, $temp
            );
        }
        $dynamic_notifications[] = ['type'=>$type,'title'=>$title,'message'=>$message];
    }
}

// -------------------------
// Merge dynamic into session and keep latest 10
foreach ($dynamic_notifications as $n) {
    $exists = array_filter($_SESSION['notifications'], fn($x) => $x['message'] === $n['message']);
    if (!$exists) array_unshift($_SESSION['notifications'], $n);
}
$_SESSION['notifications'] = array_slice($_SESSION['notifications'], 0, 10);

echo json_encode($_SESSION['notifications']);
?>
