// mqtt.js
// Define AWS IoT WebSocket URL
const endpoint = "wss://a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com/mqtt"; // Add /mqtt for WebSocket connection

// Central MQTT client
let mqttClient = null;
let reconnectAttempts = 0; // Track the number of reconnection attempts
const maxReconnectAttempts = 5; // Limit the number of reconnection attempts

// Certificate contents as string literals (paste your actual certificate contents here)
const rootCA = `-----BEGIN CERTIFICATE-----
MIIDQTCCAimgAwIBAgITBmyfz5m/jAo54vB4ikPmljZbyjANBgkqhkiG9w0BAQsF
ADA5MQswCQYDVQQGEwJVUzEPMA0GA1UEChMGQW1hem9uMRkwFwYDVQQDExBBbWF6
b24gUm9vdCBDQSAxMB4XDTE1MDUyNjAwMDAwMFoXDTM4MDExNzAwMDAwMFowOTEL
MAkGA1UEBhMCVVMxDzANBgNVBAoTBkFtYXpvbjEZMBcGA1UEAxMQQW1hem9uIFJv
b3QgQ0EgMTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBALJ4gHHKeNXj
ca9HgFB0fW7Y14h29Jlo91ghYPl0hAEvrAIthtOgQ3pOsqTQNroBvo3bSMgHFzZM
9O6II8c+6zf1tRn4SWiw3te5djgdYZ6k/oI2peVKVuRF4fn9tBb6dNqcmzU5L/qw
IFAGbHrQgLKm+a/sRxmPUDgH3KKHOVj4utWp+UhnMJbulHheb4mjUcAwhmahRWa6
VOujw5H5SNz/0egwLX0tdHA114gk957EWW67c4cX8jJGKLhD+rcdqsq08p8kDi1L
93FcXmn/6pUCyziKrlA4b9v7LWIbxcceVOF34GfID5yHI9Y/QCB/IIDEgEw+OyQm
jgSubJrIqg0CAwEAAaNCMEAwDwYDVR0TAQH/BAUwAwEB/zAOBgNVHQ8BAf8EBAMC
AYYwHQYDVR0OBBYEFIQYzIU07LwMlJQuCFmcx7IQTgoIMA0GCSqGSIb3DQEBCwUA
A4IBAQCY8jdaQZChGsV2USggNiMOruYou6r4lK5IpDB/G/wkjUu0yKGX9rbxenDI
U5PMCCjjmCXPI6T53iHTfIUJrU6adTrCC2qJeHZERxhlbI1Bjjt/msv0tadQ1wUs
N+gDS63pYaACbvXy8MWy7Vu33PqUXHeeE6V/Uq2V8viTO96LXFvKWlJbYK8U90vv
o/ufQJVtMVT8QtPHRh8jrdkPSHCa2XV4cdFyQzR1bldZwgJcJmApzyMZFo6IQ6XU
5MsI+yMRQ+hDKXJioaldXgjUkK642M4UwtBV8ob2xJNDd2ZhwLnoQdeXeGADbkpy
rqXRfboQnoZsG4q5WTP468SQvvG5
-----END CERTIFICATE-----`;

const deviceCert = `-----BEGIN CERTIFICATE-----
MIIDWjCCAkKgAwIBAgIVAL9kt1NGZT8bx7ObKAd6jD2GCCf7MA0GCSqGSIb3DQEB
CwUAME0xSzBJBgNVBAsMQkFtYXpvbiBXZWIgU2VydmljZXMgTz1BbWF6b24uY29t
IEluYy4gTD1TZWF0dGxlIFNUPVdhc2hpbmd0b24gQz1VUzAeFw0yNDExMTAwOTU2
MTJaFw00OTEyMzEyMzU5NTlaMB4xHDAaBgNVBAMME0FXUyBJb1QgQ2VydGlmaWNh
dGUwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQCxE7ZjwRedywXOdnnk
XtGqLQcAS38acy+qyeVxezw+6qd1emJsJHctYSFlfJ+qn+9JO5TxfRavTjQGET+F
4hfSt8Ctdtbmrd5ERf807wrcY9/l/3QR5XTyo84xxgGEZthr78cu3dQNxzLqlsQJ
uRZMiZtXKbONEILBBCLAzofGtiwQZLLGkdyIRUtOSt8OnW5is6I5hRjplZeGm4fq
AptJyceBts0xnxR82h7/7hDkafyf50ZF4Wkxz+PUp6y/5YFOX552x0v8F90ECCjN
aAdjXZSCrG6hwFwBD7ox/8YpngmWSSpnoynPdn8yAmgFrYnXySmZUc2y1ymMOt9O
Uh6xAgMBAAGjYDBeMB8GA1UdIwQYMBaAFILice8ptfghcmSWsyorfLX2chTmMB0G
A1UdDgQWBBQQEMQLF044wZEKE+LdAuTOgZNbRjAMBgNVHRMBAf8EAjAAMA4GA1Ud
DwEB/wQEAwIHgDANBgkqhkiG9w0BAQsFAAOCAQEAQPoCyEQpkZB867rmMrXVeR2k
IyNAgl85xiO5lc8GXb0y2FbydqW5zsrFEaAqp1RLl1hW2ayD5jMCB2VR0590fEdk
q8vcN810uhPEJvlR0KiKcDkP0yZfszwXmW79m/lQbq7+mvUiWtOguGRy5jmcwN5S
jyywBF6Mcz0ct4kqIHGhBPaYAaDDqDchSk3UPLOBOIuFV+wYJWbrO4ZwtqgJ3L62
luVhgDt/sdyLPjpdha3LZv7w0nwrabEX7SzjutnK8wNWjYlMvoJhVE5WY3hWpKr1
oMrn+kPwvkwCbtO7jq30u4h+qx94rsImAvUyae4eca2ehdOS5bB1MUowlN804g==
-----END CERTIFICATE-----
`;

const privateKey = `-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAsRO2Y8EXncsFznZ55F7Rqi0HAEt/GnMvqsnlcXs8PuqndXpi
bCR3LWEhZXyfqp/vSTuU8X0Wr040BhE/heIX0rfArXbW5q3eREX/NO8K3GPf5f90
EeV08qPOMcYBhGbYa+/HLt3UDccy6pbECbkWTImbVymzjRCCwQQiwM6HxrYsEGSy
xpHciEVLTkrfDp1uYrOiOYUY6ZWXhpuH6gKbScnHgbbNMZ8UfNoe/+4Q5Gn8n+dG
ReFpMc/j1Kesv+WBTl+edsdL/BfdBAgozWgHY12UgqxuocBcAQ+6Mf/GKZ4Jlkkq
Z6Mpz3Z/MgJoBa2J18kpmVHNstcpjDrfTlIesQIDAQABAoIBAEs4I28GdAC8YDAO
1cJzoL6YN/QhHdHfgi0bbFKjVbkoNpBJt4tWhiWJsAULRkvVenDyVVermjpHjwPQ
ydoWa6ZAFiHZbHo6+0KnNTyIGmX6Kv7pX6XGgcIcYRd1k+lpQp+/EC5RXqWnq3JJ
Lucub1F91rXU6gePLuvM1PJwCO8YeCsucHx5imLAsSWECaqFIahrvWeTrxiR9noZ
RL9J9pygcOnk8Ukn8eziRmiakm+xpP3++AsTRMvMt6zDqeFQ2ElxU07brkXWGr5H
fjBCTYaAqCk3x9GWL4fSN7DKNAj4ttPfbzhVrvKpl7IOh69XaumwxERlFDeIEFkh
3qSPPBECgYEA4rZbvzJh26MxIhlgoRqBCw9WbeD8Eqv7/eDCTPCtxwrhWisbXCvu
+avLPh5TETCc92W4b8jlOQliASygo5J4R8XADO8va8w7bI5zwRgEGQhc4Lb7E2hZ
el/7OgthI/sozL+qlVxvSW8D8v+dDYmwf8BBEqLjPEhvo7Ugkeaezd0CgYEAx/Pb
B/K838v0jmBRMh0PtJeqlDe3AYxoKdrRfS2X51cAEHoKKA2czVp1e1MggIqTXbZA
jGgw0QSr5/6hOVXvt0FOBeQJvsrhGKCYXTpKsNZ7Ord0Gm1+4+2XRLWmrBVrl9ox
tovChvtakjIZfbSF2efA5r0cmdCfHw7H6SGAWOUCgYEAlYEQS6ub4M1jT0tQ76yc
wIBizJ77XAzSZn9bYoWs8393UJDwv/2w4DCsHNC+kq1uNa98yyq+DrjjSkb1wCiM
7Te5CE6LBRlvbo0aRJHj0eYz6XSBajD8ILk+4O40JxgvtaYHheuo79In1o0+MnlE
TVqpDvMfytcx19FQIybkPnkCgYAQ75+6wZ2r2wugz5mxA/MjmcESAtIWaH0eJnGe
B7GZH65atuuLTvPcFPBkfLsBCDvJMTmwatbPrXSeFtwzDgta20Yvi4wjw+1i959Q
LjLLXa9cXtOPtXyM87/fSv+ODdZqK0oQqy/T3RBj16h/FD5OIaoeISB+CsSfjdHy
9ip33QKBgC1gy/0rhOo4rEhiVzrNhxGUrt1yiXlaDUNco4NuoQ5XOF1AXKtNIlaa
joo4LymDXxzBVZ4WpY9EM5d7FVIhxcDxSJp7aY/R7URMJb7vAPhU5fQAuMTY5tNZ
mHz4YebGQdNG2NBvPDeK9gJxveHPAtzrT5fiR8R9IMl3ZYSzOaDv
-----END RSA PRIVATE KEY-----
`;

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
    ca: rootCA, // Root CA certificate content
    cert: deviceCert, // Device certificate content
    key: privateKey, // Private key content
  };

  // Check if certificate contents are provided (for debugging purposes)
  console.log("Certificate Contents:", options.ca, options.cert, options.key);

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
13