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
    <script>
        const client = mqtt.connect('wss://a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com:443/mqtt', {
            clientId: 'TestClient',
            clean: true,
            connectTimeout: 4000,
            rejectUnauthorized: false,
            protocol: 'wss', // Secure WebSocket protocol
        });

        client.on('connect', () => {
            console.log('Connected to AWS IoT');
            client.subscribe('esp32/sub', (err) => {
                if (!err) {
                    console.log('Subscribed to esp32/sub');
                } else {
                    console.log('Subscription failed:', err);
                }
            });
        });

        client.on('message', (topic, message) => {
            console.log('Received message:', message.toString());
            const li = document.createElement('li');
            li.textContent = message.toString();
            document.getElementById('messages').appendChild(li);
        });

        client.on('error', (err) => {
            console.log('Connection failed with error:', err);
        });

        function publishMessage() {
            client.publish('esp32/pub', 'Hello from the Web');
            console.log('Message sent to esp32/pub');
        }
    </script>
</body>
</html>
