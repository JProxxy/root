// Specify your IoT endpoint (use the endpoint you found earlier)
const endpoint = 'wss://a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com/mqtt';  // AWS IoT MQTT WebSocket endpoint

// Set up connection options
const options = {
  clientId: 'myClientId',  // Make sure to provide a unique clientId
  clean: true,
  reconnectPeriod: 1000,
  cert: '../assets/certificate/Device Certificate.crt',  // Path to your Thing's certificate
  key: '../assets/certificate/Private Key.key',          // Path to your Thing's private key
  ca: '../assets/certificate/AmazonRootCA1.pem',         // Path to the Amazon Root CA certificate
};

// Connect to the MQTT broker
const client = mqtt.connect(endpoint, options);

// Subscribe to a topic (matching the ESP32 subscription topic)
client.on('connect', function () {
  console.log('Connected to AWS IoT');
  // Subscribe to the topic your ESP32 is subscribing to (this should match the topic in ESP32 code)
  client.subscribe('esp32/sub', function (err) {
    if (err) {
      console.error('Subscription error: ', err);
    } else {
      console.log('Subscribed to esp32/sub');
    }
  });
});

// Handle incoming messages
client.on('message', function (topic, message) {
  console.log('Message received on topic:', topic);
  console.log(message.toString());

  // You can add logic here to handle messages, e.g., update UI or control devices
});

// Publish a message (e.g., to control the device)
function publishMessage() {
  const message = JSON.stringify({
    accessGateState: 1  // example message content to change the gate state to 1 (open)
  });

  client.publish('esp32/pub', message, function(err) {
    if (err) {
      console.error('Publish failed: ', err);
    } else {
      console.log('Message published to esp32/pub');
    }
  });
}

// Example of publishing a message every 5 seconds
setInterval(publishMessage, 5000);
