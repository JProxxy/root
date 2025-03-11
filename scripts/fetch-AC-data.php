<?php
header('Content-Type: application/json');

$ac_log = [
    "power" => "On",
    "temp" => "31",
    "timer" => "8 hrs",
    "mode" => "Fan",
    "fan" => "High",
    "swing" => "Off"
];

echo json_encode($ac_log);
?>
