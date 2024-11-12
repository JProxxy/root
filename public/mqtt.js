
  // mqtt.php

  // Define MQTT broker URL
  const endpoint = 'wss://a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com/mqtt';

  // Central MQTT client
  let mqttClient = null;

  // Function to establish MQTT connection
  function connectToMQTT(user_id) {
    // Generate unique MQTT client ID based on user_id
    const clientId = 'webClient_' + user_id;

    // Define MQTT connection options
    const options = {
      clientId: clientId,  // Use the logged-in user's ID as part of the client ID
      clean: true,
      reconnectPeriod: 1000,
      username: '',  // Optional, as you're using certificates for authentication
      password: '',  // Optional
      ca: '../assets/certificates/AmazonRootCA1.pem',
      cert: '../assets/certificates/DeviceCertificate.pem.crt',
      key: '../assets/certificates/Private.pem.key',
    };

    // Create MQTT client
    mqttClient = mqtt.connect(endpoint, options);

    mqttClient.on('connect', function () {
      console.log('Connected to AWS IoT with clientId: ' + clientId);
    });

    mqttClient.on('message', function (topic, message) {
      console.log('Received message:', message.toString());
    });
  }

  // Fetch logged-in user info from the server (PHP backend)
  fetch('../app/config/get-user-info.php')
    .then(response => response.json())
    .then(data => {
      if (data.user_id) {
        console.log('User info:', data);
        connectToMQTT(data.user_id);
      } else {
        console.error('Error:', data.error);
      }
    })
    .catch(error => console.error('Fetch error:', error));


  // Function to subscribe to a topic
  function subscribeToTopic(topic) {
    if (!mqttClient) {
      console.log('MQTT client is not connected. Attempting to connect...');
      return;
    }
    mqttClient.subscribe(topic, function (err) {
      if (err) {
        console.log('Error subscribing to ' + topic, err);
      } else {
        console.log('Subscribed to topic: ' + topic);
      }
    });
  }

  // Function to publish a message to a topic
  function publishMessage(topic, message) {
    if (!mqttClient) {
      console.log('MQTT client is not connected. Attempting to connect...');
      return;
    }
    const payload = {
      message: message,
      timestamp: new Date().toISOString()
    };

    mqttClient.publish(topic, JSON.stringify(payload), function (err) {
      if (err) {
        console.log('Error publishing message:', err);
      } else {
        console.log('Message published to ' + topic);
      }
    });
  }

  // Export the functions to be used in other files
  export { connectToMQTT, subscribeToTopic, publishMessage };
