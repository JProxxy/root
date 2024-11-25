// Include MQTT.js from CDN (already done in the HTML file)
const endpoint = 'a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com';

// If you need authentication, use AWS Cognito or a different mechanism to get credentials.
// For this example, assume you are using AWS Cognito or IAM role-based credentials in EC2.

const mqttClient = mqtt.connect(`wss://${endpoint}/mqtt`, {
  clientId: 'mqtt-user',  // Set a unique clientId for the MQTT connection
  rejectUnauthorized: false,  // Optional, depending on your server setup
});

// MQTT event handlers
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

// Handle errors, reconnections, etc.
mqttClient.on('error', (err) => console.error('Error:', err));
mqttClient.on('close', () => console.log('Connection closed'));
mqttClient.on('reconnect', () => console.log('Reconnecting...'));
mqttClient.on('offline', () => console.log('Client is offline'));
mqttClient.on('message', (topic, message) => {
  console.log(`Received message from ${topic}: ${message.toString()}`);
});
