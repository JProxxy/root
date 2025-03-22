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
        $stmt = $conn->prepare("SELECT power, temp, timer, mode, fan, swing FROM acRemote WHERE user_id = :user_id LIMIT 1");
        $stmt->execute([':user_id' => $user_id]);
        $acData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($acData) {
            echo json_encode($acData);
        } else {
            // INSERT DEFAULT VALUES for this user.
            $stmt = $conn->prepare("INSERT INTO acRemote (user_id, power, temp, timer, mode, fan, swing, timestamp) 
                                    VALUES (:user_id, 'Off', 26, '0', 'Cool', 'High', 'On', NOW())");
            $stmt->execute([':user_id' => $user_id]);

            // FETCH AGAIN
            $stmt = $conn->prepare("SELECT power, temp, timer, mode, fan, swing FROM acRemote WHERE user_id = :user_id LIMIT 1");
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

        // Use the session user_id.
        $user_id = $_SESSION['user_id'];

        // Check if a record for this user exists.
        $stmt = $conn->prepare("SELECT COUNT(*) FROM acRemote WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $exists = $stmt->fetchColumn();

        if (!$exists) {
            // Insert default values for a new user.
            $stmt = $conn->prepare("INSERT INTO acRemote (user_id, power, temp, timer, mode, fan, swing, timestamp) 
                                    VALUES (:user_id, 'Off', 26, '0', 'Cool', 'High', 'On', NOW())");
            $stmt->execute([':user_id' => $user_id]);
        }

        // List of fields to update. The "timer" field is included.
        $fields = ['power', 'temp', 'timer', 'mode', 'fan', 'swing'];
        $updateValues = [];

        // Use provided values, or fetch the current value if not set.
        foreach ($fields as $field) {
            if (isset($input[$field])) {
                $updateValues[$field] = $input[$field];
            } else {
                $stmt = $conn->prepare("SELECT $field FROM acRemote WHERE user_id = :user_id");
                $stmt->execute([':user_id' => $user_id]);
                $updateValues[$field] = $stmt->fetchColumn();
            }
        }

        // Validate temperature range if provided.
        if (isset($updateValues['temp'])) {
            $temp = (int)$updateValues['temp'];
            if ($temp < 16 || $temp > 32) {
                throw new Exception("Temperature must be between 16 and 32°C.");
            }
        }

        // Build and execute the update query.
        $sql = "UPDATE acRemote SET power = :power, temp = :temp, timer = :timer, mode = :mode, fan = :fan, swing = :swing WHERE user_id = :user_id";
        error_log("Update values: " . print_r($updateValues, true));

        $stmt = $conn->prepare($sql);
        $updateValues['user_id'] = $user_id;
        $stmt->execute($updateValues);

        // Fetch the updated AC settings.
        $stmt = $conn->prepare("SELECT power, temp, timer, mode, fan, swing FROM acRemote WHERE user_id = :user_id LIMIT 1");
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
