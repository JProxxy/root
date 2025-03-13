<?php
session_start();
header('Content-Type: application/json');

// If the AC log isn't already in the session, initialize it.
if (!isset($_SESSION['ac_log'])) {
    $_SESSION['ac_log'] = [
        "power" => "On",
        "temp" => "30",
        "timer" => "8 hrs",
        "mode" => "Cool",
        "fan" => "High",
        "swing" => "Off"
    ];
}

// Check if a POST request is made to update values.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['fan'])) { // Handle fan speed update
        $_SESSION['ac_log']['fan'] = $input['fan'];
    }
    if (isset($input['activeMode'])) { // Handle mode update
        $_SESSION['ac_log']['mode'] = $input['activeMode'];
    }
    if (isset($input['swing'])) { // Handle swing state update
        $_SESSION['ac_log']['swing'] = $input['swing'];
    }    
    if (isset($input['temp'])) {
        $_SESSION['ac_log']['temp'] = $input['temp'];
    }
    if (isset($input['sleep'])) { // Handle sleep mode update
        $_SESSION['ac_log']['sleep'] = $input['sleep'];
    }
    
}

// Return the AC log (which may now contain updated values).
echo json_encode($_SESSION['ac_log']);
?>