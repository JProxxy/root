const AWS = require('aws-sdk');

// No need to manually configure credentials if the EC2 instance has an IAM role
AWS.config.update({ region: 'ap-southeast-1' });

const mqtt = require('mqtt');

// AWS IoT endpoint
const endpoint = 'a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com';

// Configure MQTT client using IAM role credentials
const mqttClient = mqtt.connect(`wss://${endpoint}/mqtt`, {
  clientId: 'mqtt-user',  // You can set a unique clientId here
  rejectUnauthorized: true  // Ensure the connection is secure
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
