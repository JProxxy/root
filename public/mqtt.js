// mqtt.php

// Define MQTT broker URL
const endpoint = "mqtts://a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com/mqtt";

// Central MQTT client
let mqttClient = null;
let reconnectAttempts = 0; // Track the number of reconnection attempts
const maxReconnectAttempts = 5; // Limit the number of reconnection attempts

// Function to establish MQTT connection
function connectToMQTT(user_id) {
  // Generate unique MQTT client ID based on user_id
  const clientId = "webClient_" + user_id;

  // Check if certificate paths exist
  console.log(
    "Certificate Paths:",
    "../assets/certificates/firstFloor-garage-lights/AmazonRootCA1.pem",
    "../assets/certificates/firstFloor-garage-lights/DeviceCertificate.pem.crt",
    "../assets/certificates/firstFloor-garage-lights/Private.pem.key"
  );

  // Define MQTT connection options
  const options = {
    clientId: clientId, // Use the logged-in user's ID as part of the client ID
    clean: true,
    reconnectPeriod: 1000, // Set reconnect period
    username: "", // Optional, as you're using certificates for authentication
    password: "", // Optional
    ca: "../assets/certificates/firstFloor-garage-lights/AmazonRootCA1.pem",
    cert: "../assets/certificates/firstFloor-garage-lights/DeviceCertificate.pem.crt",
    key: "../assets/certificates/firstFloor-garage-lights/Private.pem.key",
  };

  // Create MQTT client
  mqttClient = mqtt.connect(endpoint, options);

  // Event listener for successful connection
  mqttClient.on("connect", function () {
    console.log("Connected to AWS IoT with clientId: " + clientId);
    reconnectAttempts = 0; // Reset reconnect attempts on successful connection
  });

  // Event listener for connection errors
  mqttClient.on("error", function (err) {
    console.error("MQTT connection error:", err);
    if (err.message) {
      console.log("Error message:", err.message);
    }
    if (err.stack) {
      console.log("Error stack:", err.stack);
    }

    // Detailed error logging for specific types of errors:
    if (err.message.includes('certificate')) {
      console.error("Certificate error: Please check the certificate and key paths.");
    } else if (err.message.includes('ENOTFOUND') || err.message.includes('timeout')) {
      console.error("Network error: Could not reach the AWS IoT endpoint. Check your network connection.");
    } else if (err.message.includes('403')) {
      console.error("Authorization error: The device is not authorized. Check the policies attached to the certificate.");
    } else {
      console.log("Attempting to reconnect...");
    }
  });

  // Event listener for connection close
  mqttClient.on("close", function () {
    console.log("MQTT connection closed");
    if (mqttClient.connected === false) {
      console.log(
        "Client disconnected: Possibly due to incorrect credentials, network issue, or server-side error."
      );
    }
  });

  // Event listener for disconnection
  mqttClient.on("offline", function () {
    console.log("MQTT client is offline");
  });

  // Event listener for reconnect attempts
  mqttClient.on("reconnect", function () {
    reconnectAttempts++;
    console.log("Reconnection attempt #" + reconnectAttempts);
    if (reconnectAttempts > maxReconnectAttempts) {
      console.error("Max reconnect attempts reached. Giving up.");
      mqttClient.end(); // End the client if max reconnect attempts are reached
    }
  });

  // Event listener for incoming messages
  mqttClient.on("message", function (topic, message) {
    console.log(
      "Received message from topic '" + topic + "':",
      message.toString()
    );
  });
}

// Fetch logged-in user info from the server (PHP backend)
fetch("../app/config/get-user-info.php")
  .then((response) => {
    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }
    return response.text(); // Use text() temporarily for debugging
  })
  .then((text) => {
    try {
      const data = JSON.parse(text);
      if (data.user_id) {
        console.log("User info:", data);
        connectToMQTT(data.user_id);
      } else {
        console.error("Error:", data.error);
      }
    } catch (error) {
      console.error("JSON parsing error:", error, "Response text:", text);
    }
  })
  .catch((error) => console.error("Fetch error:", error));

// Function to subscribe to a topic
function subscribeToTopic(topic) {
  if (!mqttClient) {
    console.log("MQTT client is not connected. Attempting to connect...");
    return;
  }
  mqttClient.subscribe(topic, function (err) {
    if (err) {
      console.log("Error subscribing to " + topic, err);
    } else {
      console.log("Successfully subscribed to topic: " + topic);
    }
  });
}

// Function to publish a message to a topic
function publishMessage(topic, message) {
  if (!mqttClient) {
    console.log("MQTT client is not connected. Attempting to connect...");
    return;
  }
  const payload = {
    message: message,
    timestamp: new Date().toISOString(),
  };

  mqttClient.publish(topic, JSON.stringify(payload), function (err) {
    if (err) {
      console.log("Error publishing message to " + topic + ":", err);
    } else {
      console.log(
        "Message published to " + topic + ": ",
        JSON.stringify(payload)
      );
    }
  });
}

// Export the functions to be used in other files
export { connectToMQTT, subscribeToTopic, publishMessage };
