// Include the MQTT.js library directly for the browser
// Initialize the MQTT client in your JavaScript file
const endpoint = 'a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com';

// Create MQTT client using WebSocket and AWS IoT credentials
const mqttClient = mqtt.connect(`wss://${endpoint}/mqtt`, {
  clientId: 'mqtt-user',  // Set a unique clientId for the MQTT connection
  rejectUnauthorized: false,  // Ensure the connection is secure
});

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
  
  mqttClient.on('error', (err) => {
    console.error('Error:', err);
  });
  
  mqttClient.on('close', () => {
    console.log('Connection closed');
  });
  
  mqttClient.on('reconnect', () => {
    console.log('Reconnecting...');
  });
  
  mqttClient.on('offline', () => {
    console.log('Client is offline');
  });
  
  mqttClient.on('connect_error', (err) => {
    console.error('Connection error:', err);
  });
  