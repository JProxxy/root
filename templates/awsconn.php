<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MQTT WebSocket Test</title>
    <script src="https://cdn.jsdelivr.net/npm/mqtt/dist/mqtt.min.js"></script>
</head>
<body>
    <h1>MQTT.js WebSocket Test</h1>
    <button onclick="publishMessage()">Publish Test Message</button>
    <ul id="messages"></ul>
    <h3>Status: <span id="status">Disconnected</span></h3>

    <script>
        const endpoint = 'a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com'; // AWS IoT endpoint
        const clientId = 'TestClient'; // Unique client ID for the WebSocket connection
        const topicPublish = 'esp32/pub';  // Topic for publishing messages
        const topicSubscribe = 'esp32/sub'; // Topic for subscribing to messages

        // Create MQTT client using WebSocket protocol over TLS
        const client = mqtt.connect('wss://' + endpoint + ':443/mqtt', {
            clientId: clientId,
            clean: true,
            connectTimeout: 4000,
            rejectUnauthorized: false,
            protocol: 'wss', // Secure WebSocket protocol
            username: '', // leave empty if not using username/password authentication
            password: ''  // leave empty if not using username/password authentication
        });

        // Connection successful callback
        client.on('connect', function () {
            console.log('Connected to AWS IoT');
            document.getElementById("status").textContent = 'Connected';

            // Subscribe to the topic
            client.subscribe(topicSubscribe, function (err) {
                if (err) {
                    console.log('Subscription failed:', err);
                } else {
                    console.log('Subscribed to ' + topicSubscribe);
                }
            });
        });

        // Connection error callback
        client.on('error', function (err) {
            console.log('Connection failed with error:', err);
            document.getElementById("status").textContent = 'Connection failed';
        });

        // Connection closed callback
        client.on('close', function () {
            console.log('Connection closed');
            document.getElementById("status").textContent = 'Disconnected';
        });

        // Message received callback
        client.on('message', function (topic, message) {
            console.log('Received message on topic ' + topic + ': ' + message.toString());
            const li = document.createElement('li');
            li.textContent = message.toString();
            document.getElementById('messages').appendChild(li);
        });

        // Publish a test message to the topic
        function publishMessage() {
            const message = 'Hello from the Web!';
            client.publish(topicPublish, message, function (err) {
                if (err) {
                    console.log('Publish failed:', err);
                } else {
                    console.log('Message sent to ' + topicPublish + ': ' + message);
                }
            });
        }

        // Debugging messages
        client.on('connect', function () {
            console.log('Successfully connected to AWS IoT');
        });

        client.on('reconnect', function () {
            console.log('Reconnecting to AWS IoT');
        });

        client.on('offline', function () {
            console.log('Client is offline');
            document.getElementById("status").textContent = 'Offline';
        });

        client.on('error', function (error) {
            console.log('Connection error:', error);
            document.getElementById("status").textContent = 'Connection error';
        });

    </script>
</body>
</html>
