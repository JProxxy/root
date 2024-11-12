// mqtt.php

// Define MQTT broker URL
const endpoint =
  "wss://a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com/mqtt";

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
    clientId: clientId,
    clean: true,
    reconnectPeriod: 1000,
    username: "",
    password: "",
    ca: `-----BEGIN CERTIFICATE-----
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
    -----END CERTIFICATE-----`,
    cert: `-----BEGIN CERTIFICATE-----
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
-----END CERTIFICATE-----`,
    key: `-----BEGIN RSA PRIVATE KEY-----
    MIIEpAIBAAKCAQEAsfDZGMu3aZLptEKFBqIt15Bd6MCbacB7+s1oqqKh5DXtf6xg
    Fpp2hpEUK3BfIfFPZ/LsSmPulzmS4aPD/D4Ow7UDqwghx69QDdT6a6fWOWSeNds+
    unVv5MRalO82Lyv6GkMMB0RGdUFh9IzOh/TEHkrAhSDWoV1DODaZCtOcVY6AlVeu
    LoBmDiiFZeH3u9ykL+19SLTyOmL+I/WbZbFg8KvrTaEDYahBT2lAb7+bb1GWaDxh
    x8Z3+256MVeIzNTLAvM5PxeM5NGamNiD3m4VQ4slSwTHFasJSSLVoNgUfUjvz52V
    iG2HASlVluCvgECXcvdFCAJUxHBA+xj6qw3RhQIDAQABAoIBAQCX8Cfv3ENIioGz
    wfkEqQarwkHttEkAC7PRfAObcDL8QnABTJbfthTu4oJudQ1GVl6PTAYnnxzjU+3v
    bX6zq+m/gpkXOWJet3uL1AfgVPe/GgNdyU/OtuhMVr1h3sBNlyd/tTBYJBxlkuap
    gnH39TBhDUNGabvbHV+AaF5VuEsNYatf3A5QrOs88yYdUqYyPC1qlsnE/aXZsNvz
    dzEZwepqKkx6SVEWjE1DBmheJ8YFVXqx9Gr4DNX4s8/Z0vAFm1rjK0oC7lV3l87b
    Dzh//h/kluVr9hpTaur2/3K5RquYWOY4tiMcKalwoBUcqq7oVY0blfEDURErDK+W
    2jHGGI8BAoGBAOSelJ/4SDC3D88B4n+fxmFEhq5C0OrlioQGTNPPmXoTffVQqcJo
    LHCiM2cpMnce8hC2DsSdHYedFJOTaET8nRsna0g+MZxR3M7Yt+pWAwQiBp+Y/kaU
    RfggqWXKySy6cuZwNMg2K3MIGBUWXpqlw+bumdVxL80N7gOH2LKbt/RtAoGBAMdA
    eNgP9dbM1ppti+DBpLzCLGpmJR9qaFD13QUjmjTobhYG0heClxAab/M9LoSdipjy
    7F/hwinpRHO/e1oZSZS1elcLsWJBbeeD0sbq3zpmi9UiutN3Fj5GLjiSDbY/wnal
    SnH2mJGs0+NgREB2xl9D5xhMVjlpca+U8wVqFjJ5AoGATeq+PMch03iQqry5tUV/
    FyeDv2CGU4hn7Rc4l/fpFvINu84CDX/zpW+ilUY1LOQfHBLwdZIWvmGK4cEbiGeQ
    I/ELX1PTFLPRza2PAQ7PeFkgaMR75RIjxq1bDpZOejARePhFBPdxV4MqsgUtuQdF
    /S7UvyoUYI6e0BU8haMMzwECgYB3h3q3CuQMprc6zuqxuxjrT4S7k2lDrl7D6qpb
    Ud9JTAH2XsMb7XBX7bQo9BP65FNnq5sXbeQ/pjs1QLJr+22Ds1af71jLO7sFvrPs
    NqwRacEK7Bmtj+wdEZbawutM4HT1HfVJ5ofiJA68gVyQW3BnZ+GzVuAG8vWLQkyD
    e/+y6QKBgQCwlLvKG7cyj5pMd76Bz62xDAdLIlOTwfu2fMSV8BZK31L3hqPc6ZPX
    2VZXy/CxJxNk/QXwTYg1kd4G9aeW9IJYJnxwOa5P+IfAbrynBSAJRp0yXCB0NrPy
    56u9MOX69LgOfHCkk9MgVoC+XdKmTUM9WBJtL5xPVFXjVUwG/24ZuQ==
    -----END RSA PRIVATE KEY-----
    `,
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
    console.log("Attempting to reconnect...");
  });

  // Event listener for connection close
  mqttClient.on("close", function () {
    console.log("MQTT connection closed");
    // Add debug to check why the connection closed
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
