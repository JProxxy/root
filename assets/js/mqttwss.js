// Include the MQTT.js library directly for the browser
// Initialize the MQTT client in your JavaScript file
const endpoint = 'a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com';

// Create MQTT client using WebSocket and AWS IoT credentials
const mqttClient = mqtt.connect(`wss://${endpoint}/mqtt`, {
  clientId: 'mqtt-user',  // Set a unique clientId for the MQTT connection
  rejectUnauthorized: true,  // Ensure the connection is secure
});

// MQTT connection handling
mqttClient.on('connect', () => {
  console.log('Connected to AWS IoT Core');
  mqttClient.subscribe('esp32/sub', (err) => {
    if (err) {
      console.error('Subscription failed:', err);
    } else {
      console.log('Subscribed to topic: esp32/sub');
    }
  });
});

// Handle incoming messages
mqttClient.on('message', (topic, message) => {
  console.log(`Received message from ${topic}: ${message.toString()}`);
});

// Error handling
mqttClient.on('error', (err) => {
  console.error('Error:', err);
});

mqttClient.on('close', () => {
  console.log('Connection closed');
});
