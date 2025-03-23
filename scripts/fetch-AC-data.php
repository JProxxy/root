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
        // FETCH AC DATA for the authenticated user (including sleep state).
        $stmt = $conn->prepare("SELECT power, temp, timer, mode, fan, swing, sleep FROM acRemote WHERE user_id = :user_id LIMIT 1");
        $stmt->execute([':user_id' => $user_id]);
        $acData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($acData) {
            echo json_encode($acData);
        } else {
            // INSERT DEFAULT VALUES for this user, now with a default sleep value ("Off").
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

        // Fetch the current mode from the database
        $stmt = $conn->prepare("SELECT mode FROM acRemote WHERE user_id = :user_id LIMIT 1");
        $stmt->execute([':user_id' => $user_id]);
        $currentMode = $stmt->fetchColumn();

        // Prevent updating sleep if mode is Dry or Fan
        if (isset($input['sleep']) && ($currentMode === 'Dry' || $currentMode === 'Fan')) {
            echo json_encode(["error" => "Sleep mode cannot be enabled in Dry or Fan mode."]);
            exit;
        }

        // Check if a record for this user exists.
        $stmt = $conn->prepare("SELECT COUNT(*) FROM acRemote WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $exists = $stmt->fetchColumn();

        if (!$exists) {
            // Insert default values for a new user.
            $stmt = $conn->prepare("INSERT INTO acRemote (user_id, power, temp, timer, mode, fan, swing, sleep, timestamp) 
                                    VALUES (:user_id, 'Off', 26, '0', 'Cool', 'High', 'On', 'Off', NOW())");
            $stmt->execute([':user_id' => $user_id]);
        }

        // Build the dynamic update query.
        $fields = ['power', 'temp', 'timer', 'mode', 'fan', 'swing', 'sleep'];
        $updateParts = [];
        $params = [];

        foreach ($fields as $field) {
            if (isset($input[$field])) {
                $updateParts[] = "$field = :$field";
                $params[$field] = $input[$field];

                switch ($field) {
                    case 'power':
                        $updateParts[] = "powertime = NOW()";
                        break;
                    case 'temp':
                        $updateParts[] = "temptime = NOW()";
                        break;
                    case 'timer':
                        $updateParts[] = "timertime = NOW()";
                        break;
                    case 'mode':
                        $updateParts[] = "modetime = NOW()";
                        break;
                    case 'fan':
                        $updateParts[] = "fantime = NOW()";
                        break;
                    case 'swing':
                        $updateParts[] = "swingtime = NOW()";
                        break;
                    case 'sleep':
                        $updateParts[] = "sleeptime = NOW()"; // Update sleeptime only if sleep changes
                        break;
                }
            }
        }

        // Validate temperature range if provided.
        if (isset($params['temp'])) {
            $temp = (int)$params['temp'];
            if ($temp < 16 || $temp > 32) {
                throw new Exception("Temperature must be between 16 and 32°C.");
            }
        }

        $params['user_id'] = $user_id;

        if (!empty($updateParts)) {
            $sql = "UPDATE acRemote SET " . implode(", ", $updateParts) . " WHERE user_id = :user_id";
            error_log("Dynamic Update SQL: " . $sql);
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        } else {
            throw new Exception("No valid fields provided for update.");
        }

        // Fetch the updated AC settings.
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
