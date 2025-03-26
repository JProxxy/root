<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../app/config/connection.php';

// Ensure a valid user is logged in.
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not authenticated."]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Ensure PDO throws exceptions.
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        // FETCH AC DATA for the authenticated user.
        $stmt = $conn->prepare("SELECT power, temp, timer, mode, fan, swing, sleep FROM acRemote WHERE user_id = :user_id LIMIT 1");
        $stmt->execute([':user_id' => $user_id]);
        $acData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($acData) {
            echo json_encode($acData);
        } else {
            // INSERT DEFAULT VALUES for this user.
            $stmt = $conn->prepare("INSERT INTO acRemote (user_id, power, temp, timer, mode, fan, swing, sleep, timestamp) 
                                    VALUES (:user_id, 'Off', 26, '0', 'Cool', 'High', 'On', 'Off', NOW())");
            $stmt->execute([':user_id' => $user_id]);

            // FETCH AGAIN
            $stmt = $conn->prepare("SELECT power, temp, timer, mode, fan, swing, sleep FROM acRemote WHERE user_id = :user_id LIMIT 1");
            $stmt->execute([':user_id' => $user_id]);
            $acData = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode($acData);
        }
        exit;

    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            throw new Exception("Invalid JSON input.");
        }

        // ============== POWER OFF RESET LOGIC ============== //
        if (isset($input['power']) && $input['power'] === 'Off') {
            // Force default values regardless of input.
            $forcedDefaults = [
                'fan'   => 'High',
                'mode'  => 'Cool',
                'swing' => 'Off',
                'sleep' => 'Off',
                'timer' => '0',
                'temp'  => 16
            ];
            // Merge forced defaults with existing input.
            $input = array_merge($input, $forcedDefaults);

            // Reset local session variables to the forced defaults.
            $_SESSION['fan']   = $forcedDefaults['fan'];
            $_SESSION['mode']  = $forcedDefaults['mode'];
            $_SESSION['swing'] = $forcedDefaults['swing'];
            $_SESSION['sleep'] = $forcedDefaults['sleep'];
            $_SESSION['timer'] = $forcedDefaults['timer'];
            $_SESSION['temp']  = $forcedDefaults['temp'];
        }
        // ==================================================== //

        // Ensure the record for the current user exists.
        $stmt = $conn->prepare("SELECT COUNT(*) FROM acRemote WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $exists = $stmt->fetchColumn();

        if (!$exists) {
            $stmt = $conn->prepare("INSERT INTO acRemote (user_id, power, temp, timer, mode, fan, swing, sleep, timestamp) 
                                    VALUES (:user_id, 'Off', 16, '0', 'Cool', 'High', 'On', 'Off', NOW())");
            $stmt->execute([':user_id' => $user_id]);
        }

        // List of fields that share the same AC.
        $fields = ['power', 'temp', 'timer', 'mode', 'fan', 'swing', 'sleep'];

        // Prepare arrays to build global update (for all rows) and local update (for timestamp only).
        $globalUpdateParts = [];
        $globalParams = [];
        $localTimestampUpdateParts = [];

        // Mapping field to its timestamp column.
        $timestampMapping = [
            'power' => 'powertime',
            'temp'  => 'temptime',
            'timer' => 'timertime',
            'mode'  => 'modetime',
            'fan'   => 'fantime',
            'swing' => 'swingtime',
            'sleep' => 'sleeptime'
        ];

        // Loop through each field to update if provided.
        foreach ($fields as $field) {
            if (isset($input[$field])) {
                $globalUpdateParts[] = "$field = :$field";
                $globalParams[$field] = $input[$field];
                // Add the corresponding timestamp update for the current user.
                $localTimestampUpdateParts[] = $timestampMapping[$field] . " = NOW()";
            }
        }

        // Additional Business Logic:
        // If mode is being changed to "Dry" or "Fan", force sleep to "Off".
        if (isset($input['mode']) && ($input['mode'] === "Dry" || $input['mode'] === "Fan")) {
            $globalUpdateParts[] = "sleep = 'Off'";
            $localTimestampUpdateParts[] = "sleeptime = NOW()";
        }

        // If mode is being changed to "Cool", force fan to "High".
        if (isset($input['mode']) && $input['mode'] === "Cool") {
            $globalUpdateParts[] = "fan = 'High'";
            $localTimestampUpdateParts[] = "fantime = NOW()";
        }

        // Validate temperature range if provided.
        if (isset($globalParams['temp'])) {
            $temp = (int)$globalParams['temp'];
            if ($temp < 16 || $temp > 32) {
                throw new Exception("Temperature must be between 16 and 32°C.");
            }
        }

        if (empty($globalUpdateParts)) {
            throw new Exception("No valid fields provided for update.");
        }

        // ===================== GLOBAL UPDATE =====================
        // Update all records in the acRemote table with the new values.
        $sqlGlobal = "UPDATE acRemote SET " . implode(", ", $globalUpdateParts);
        $stmt = $conn->prepare($sqlGlobal);
        $stmt->execute($globalParams);
        // ==========================================================

        // ===================== LOCAL TIMESTAMP UPDATE =====================
        // Update the timestamp fields only for the current user.
        if (!empty($localTimestampUpdateParts)) {
            $sqlLocal = "UPDATE acRemote SET " . implode(", ", $localTimestampUpdateParts) . " WHERE user_id = :user_id";
            $localParams = [':user_id' => $user_id];
            $stmt = $conn->prepare($sqlLocal);
            $stmt->execute($localParams);
        }
        // =================================================================

        // Fetch the updated AC settings for the current user.
        $stmt = $conn->prepare("SELECT power, temp, timer, mode, fan, swing, sleep FROM acRemote WHERE user_id = :user_id LIMIT 1");
        $stmt->execute([':user_id' => $user_id]);
        $acData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return the updated data along with a success message.
        echo json_encode(array_merge(["success" => true, "message" => "AC settings updated"], $acData));
        exit;
    }
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}
?>
