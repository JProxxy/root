// mqtt.js
// Define AWS IoT WebSocket URL
const endpoint = "wss://a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com/mqtt"; // Add /mqtt for WebSocket connection

// Central MQTT client
let mqttClient = null;
let reconnectAttempts = 0; // Track the number of reconnection attempts
const maxReconnectAttempts = 5; // Limit the number of reconnection attempts

// Function to establish MQTT connection
function connectToMQTT(user_id) {
  // Generate unique MQTT client ID based on user_id
  const clientId = "webClient_" + user_id;

  // Define MQTT connection options
  const options = {
    clientId: clientId, // Use the logged-in user's ID as part of the client ID
    clean: true, // Start with a clean session
    reconnectPeriod: 1000, // Set reconnect period (milliseconds)
    connectTimeout: 30 * 1000, // Set connection timeout
    ca: "../assets/certificates/firstFloor-garage-lights/AmazonRootCA1.pem", // Correct relative path to the root CA certificate
    cert: "../assets/certificates/firstFloor-garage-lights/DeviceCertificate.pem.crt", // Correct relative path to the device certificate
    key: "../assets/certificates/firstFloor-garage-lights/Private.pem.key", // Correct relative path to the private key
  };

  // Check if certificate paths exist (for debugging purposes)
  console.log("Certificate Paths:", options.ca, options.cert, options.key);

  // Create MQTT client using the WebSocket URL (wss://)
  mqttClient = mqtt.connect(endpoint, options);

  // Event listener for successful connection
  mqttClient
    .on("connect", function () {
      console.log("Connected to AWS IoT with clientId: " + clientId);
      reconnectAttempts = 0; // Reset reconnect attempts on successful connection
    })
    .on("error", function (err) {
      console.error("MQTT connection error:", err.message);
      handleConnectionError(err);
    })
    .on("close", function () {
      console.log("MQTT connection closed.");
    });

  // Event listener for connection errors
  mqttClient.on("error", function (err) {
    console.error("MQTT connection error:", err);
    handleConnectionError(err);
  });

  // Event listener for connection close
  mqttClient.on("close", function () {
    console.log("MQTT connection closed");
    if (!mqttClient.connected) {
      console.log("Client disconnected: Possibly due to incorrect credentials, network issue, or server-side error.");
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
    console.log("Received message from topic '" + topic + "':", message.toString());
  });
}

// Handle MQTT connection errors
function handleConnectionError(err) {
  if (err.message.includes("ENOTFOUND") || err.message.includes("timeout")) {
    console.error("Network issue: Could not reach the endpoint. Check network connectivity.");
  } else if (err.message.includes("certificate")) {
    console.error("Certificate issue: Ensure the certificates are correctly configured.");
  } else if (err.message.includes("403")) {
    console.error("Authorization issue: Check the AWS IoT policy for permissions.");
  } else {
    console.log("Attempting to reconnect...");
  }
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
      console.log("Message published to " + topic + ": ", JSON.stringify(payload));
    }
  });
}

// Function to toggle light states and publish
export function toggleLight(lightCategory, state) {
    const topic = `esp32/pub/${lightCategory}`;
    mqttClient.publish(topic, state ? 'ON' : 'OFF');
    console.log(`Published to ${topic}: ${state ? 'ON' : 'OFF'}`);
}

// Export the functions to be used in other files
export { connectToMQTT, subscribeToTopic, publishMessage };
