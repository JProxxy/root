// Include AWS SDK and MQTT.js libraries
const AWS = require('aws-sdk');

const endpoint = 'a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com';

// AWS SDK will automatically fetch credentials from the instance metadata service
AWS.config.update({ region: 'ap-southeast-1' });

// Use AWS credentials to generate SigV4-signed WebSocket URL
AWS.config.getCredentials((err) => {
  if (err) {
    console.error('Error fetching AWS credentials:', err);
    return;
  }

  // Automatically fetched IAM role credentials
  const { accessKeyId, secretAccessKey, sessionToken } = AWS.config.credentials;

  // Create MQTT client using SigV4-signed WebSocket connection
  const mqttClient = mqtt.connect(`wss://${endpoint}/mqtt`, {
    clientId: 'mqtt-user', // Unique clientId
    username: accessKeyId,
    password: `${secretAccessKey}:${sessionToken || ''}`, // Include sessionToken if available
    protocol: 'wss',
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

  mqttClient.on('message', (topic, message) => {
    console.log(`Received message from ${topic}: ${message.toString()}`);
  });

  mqttClient.on('error', (err) => console.error('Error:', err));
  mqttClient.on('close', () => console.log('Connection closed'));
  mqttClient.on('reconnect', () => console.log('Reconnecting...'));
  mqttClient.on('offline', () => console.log('Client is offline'));
  mqttClient.on('connect_error', (err) => console.error('Connection error:', err));
});
