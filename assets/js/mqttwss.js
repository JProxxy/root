// Include the AWS SDK for JavaScript (v3) in your HTML file or bundle it with Webpack
import { SigV4Utils } from "@aws-sdk/signature-v4"; // Import AWS SDK signature utility

const endpoint = 'a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com';
const region = 'ap-southeast-1';
const clientId = 'mqtt-user';

// Assume you are getting temporary credentials from Cognito or an IAM role
const accessKeyId = 'YOUR_ACCESS_KEY'; // Use Cognito or IAM credentials
const secretAccessKey = 'YOUR_SECRET_KEY'; // Use Cognito or IAM credentials
const sessionToken = 'YOUR_SESSION_TOKEN'; // Use Cognito or IAM credentials

// Create the MQTT WebSocket URL with the appropriate signature
const websocketUrl = `wss://${endpoint}/mqtt`;

// Sign the request using SigV4
const signer = new SigV4Utils.SigningConfig({
    region: region,
    service: 'iot',
    accessKeyId: accessKeyId,
    secretAccessKey: secretAccessKey,
    sessionToken: sessionToken, // Use sessionToken if using temporary credentials
});

const signedUrl = signer.sign(websocketUrl);

// Connect using the signed URL
const mqttClient = mqtt.connect(signedUrl, {
  clientId: clientId,
  rejectUnauthorized: false, // Optional, depending on your server setup
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
