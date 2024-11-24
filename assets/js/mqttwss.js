const AWS = require('aws-sdk');
const mqtt = require('mqtt');
const url = require('url');

// AWS IoT Core endpoint
const endpoint = 'a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com';

// Configure AWS SDK with IAM credentials
AWS.config.update({
  accessKeyId: 'AKIATAVAA7JKQAPDC76H',  // Your Access Key
  secretAccessKey: '+ZJt5LUXkMuqpvgUQe/VTS9fyLvkZe1iVh0n0BcW',  // Your Secret Key
  region: 'ap-southeast-1'  // Your AWS region
});

// Use AWS SigV4 to sign the MQTT WebSocket connection URL
const signer = new AWS.Signers.V4(
  { service: 'iot', region: 'ap-southeast-1', endpoint: endpoint }, 'iot'
);
const signedUrl = signer.getSignedUrl({
  httpMethod: 'GET',
  requestPath: `/mqtt`,
  headers: {
    Host: endpoint
  }
});

// Parse the signed URL and set up the MQTT connection
const mqttOptions = url.parse(signedUrl);

// Set unique clientId
mqttOptions.clientId = 'mqtt-user'; 

// Now we connect using the signed URL as the WebSocket URL
const mqttClient = mqtt.connect(`wss://${endpoint}/mqtt`, mqttOptions);

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
