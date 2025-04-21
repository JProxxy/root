<?php
header('Content-Type: application/json');
date_default_timezone_set('UTC');
include __DIR__ . '/../app/config/connection.php';

try {
    $queries = [];

    // device_logs
    $queries[] = "
      SELECT 
        LOWER(CONCAT(u.last_name, ', ', SUBSTRING(u.first_name, 1, 1))) AS username,
        u.email AS email,
        COALESCE(u.profile_picture, '') AS profile_picture,
        NULL AS method,
        NULL AS result,
        d.last_updated AS timestamp
      FROM users u
      JOIN device_logs d ON u.user_id = d.user_id
    ";

    // customizeAC
    $queries[] = "
      SELECT 
        LOWER(CONCAT(u.last_name, ', ', SUBSTRING(u.first_name, 1, 1))) AS username,
        u.email AS email,
        COALESCE(u.profile_picture, '') AS profile_picture,
        NULL AS method,
        NULL AS result,
        ac.customizeTime AS timestamp
      FROM users u
      JOIN customizeAC ac ON u.user_id = ac.user_id
    ";

    // customizeWater
    $queries[] = "
      SELECT 
        LOWER(CONCAT(u.last_name, ', ', SUBSTRING(u.first_name, 1, 1))) AS username,
        u.email AS email,
        COALESCE(u.profile_picture, '') AS profile_picture,
        NULL AS method,
        NULL AS result,
        cw.customizeTime AS timestamp
      FROM users u
      JOIN customizeWater cw ON u.user_id = cw.user_id
    ";

    // gateAccess_logs (include unmatched scans as Anonymous)
    $queries[] = "
      SELECT 
        LOWER(CONCAT(u.last_name, ', ', SUBSTRING(u.first_name, 1, 1)))        AS username,
        u.email                                                              AS email,
        COALESCE(u.profile_picture, '')                                       AS profile_picture,
        ga.method                                                             AS method,
        ga.result                                                             AS result,
        ga.timestamp                                                          AS timestamp
      FROM gateAccess_logs ga
      LEFT JOIN users u ON u.user_id = ga.user_id
    ";

    // Combine, order, limit
    $sql = "
      SELECT * FROM (
        " . implode(" UNION ALL ", $queries) . "
      ) AS all_logs
      ORDER BY all_logs.timestamp DESC
      LIMIT 4
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $logs = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // 1) Determine username fallback
        $username = trim($row['username']);
        if ($username === '') {
            $username = strstr($row['email'], '@', true) ?: 'Anonymous';
        }

        // 2) Convert to Asia/Manila timezone and format
        $dt = new DateTime($row['timestamp'], new DateTimeZone('UTC'));
        $dt->setTimezone(new DateTimeZone('Asia/Manila'));
        $formattedTime = $dt->format('h:i A');

        // 3) Validate or fallback profile picture
        $pic = trim($row['profile_picture']);
        if ($pic !== '') {
            if (!preg_match('~^(?:f|ht)tps?://~i', $pic)) {
                $pic = 'https://your-domain.com/' . ltrim($pic, '/');
            }
            if (!filter_var($pic, FILTER_VALIDATE_URL)) {
                $pic = "https://ui-avatars.com/api/?name=" . urlencode($username) . "&size=40";
            }
        } else {
            $pic = "https://ui-avatars.com/api/?name=" . urlencode($username) . "&size=40";
        }

        // 4) Build action string & anonymous flag
        $method = strtolower($row['method'] ?? '');
        $result = strtolower($row['result'] ?? '');
        if ($method === 'rfid' && $result === 'denied') {
            $action = 'Invalid RFID tag scanned';
        } elseif ($method !== '') {
            $action = 'Gate Access Method: ' . ucfirst($method) . ', Result: ' . ($result === 'open' ? 'granted' : 'denied');
        } else {
            $action = '';
        }
        $isAnonymous = ($username === 'Anonymous');

        // 5) Append to logs
        $logs[] = [
            'username'        => $username,
            'profile_picture' => $pic,
            'timestamp'       => $formattedTime,
            'action'          => $action,
            'is_anonymous'    => $isAnonymous
        ];
    }

    echo json_encode($logs);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error'   => true,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
