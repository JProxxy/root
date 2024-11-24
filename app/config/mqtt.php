<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MQTT Client</title>
</head>

<body>

    <script src="https://cdn.jsdelivr.net/npm/mqtt@4.2.0/dist/mqtt.min.js"></script>
    <script>
        // AWS IoT WebSocket URL
        const endpoint = "wss://a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com/mqtt";

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
1MjFaFw00OTEyMzEyMzU5NTlaMB4xHDAaBgNVBAMME0FXUyBJb1QgQ2VydGlmaWNh
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
oMrn+kPwvkwCbtO7jq30u4h+qx94hz/xmGsLZOTbhgBz3iSzqRg7+fKwygPyUw3g
6KddH5uVVK0J6+ONoAHZrp+7Q2OBTQ==
-----END CERTIFICATE-----`;

        const deviceKey = `-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAsRO2Y8EXncsFznZ55F7Rqi0HAEt/GnMvqsnlcXs8PuqndXpi
bCR3LWEhZXyfqp/vSTuU8X0Wr040BhE/heIX0rfArXbW5q3eREX/NO8K3GPf5f90
EeV08qPOMcYBhGbYa+/HLt3UDccy6pbECbkWTImbVymzjRCCwQQiwM6HxrYsEGSy
xpHciEVLTkrfDp1uYrOiOYUY6ZWXhpuH6gKbScnHgbbNMZ8UfNoe/+4Q5Gn8n+dG
ReFpMc/j1Kesv+WBTl+eds7pqkFHdLscmIpwTgncklTxOmdj6vjmOYz1+WcsZFi2
mjKfp3pF1IKHvkhlIrfWm9wPTFzF5jPpoFJ+V7fvJheZTbb8QKKn9+Iq2i5g06xZ
CwIuF6QJ6z2mjr+uz6l0zNklb8HiJlMlXU25LwHhER7z6Oxmssv9jth5Asj7l23
yXZB01jZT6xPf4VITJ0dfgBmuFjKXdd0ZT0q8+e89En+rll+lt8fu0QHiRnmW/Z
rPbPdeF98ePxxeXhbEX0NRba0SPLvndOXqIgFe+nS2zNlKXrtk7ZZBlt9cI3LFZ0
+wuwdszMnX1Rpr7p4Hh7Z4ckBhg9vDff6hDlr5PYtVZq9VVyI/9ak5htbbfpftU8
OEh6w4g8iS6akPyokug4IEYXOhTmbahW06sm7A9MiNkH9g5zxge6rr6P3xw3LCxD
tdrcMJ7q0Nm2GRVuzM1t7Yal9vqwnzKgbyFyDFoOHiw2XXdklOnEshDN1NEdEdjf
cgNkvG6D1oCEJrsF3qS7lZowCg==
-----END RSA PRIVATE KEY-----`;


        const clientId = "client-" + Math.random().toString(16).substr(2, 8);

        // AWS IoT configuration
        const options = {
            clientId: clientId,
            protocol: "wss", // WebSocket Secure
            connectTimeout: 4000,
            keepalive: 60,
            clean: true,
            username: undefined,
            password: undefined
        };

        // Initialize the MQTT client
        const mqttClient = mqtt.connect(endpoint, options);

        // Debugging: Log connection attempt
        console.log("Attempting to connect to AWS IoT...");

        // Event listeners
        mqttClient.on("connect", () => {
            console.log("Connected to AWS IoT");  // Connection successful
            mqttClient.subscribe("home/+/lights", () => {
                console.log("Subscribed to topic: home/+/lights");
            });
            mqttClient.subscribe("home/+/aircon", () => {
                console.log("Subscribed to topic: home/+/aircon");
            });
        });

        mqttClient.on("message", (topic, message) => {
            console.log(`Received message from topic ${topic}: ${message.toString()}`);
        });

        mqttClient.on("error", (err) => {
            console.error("Connection error:", err);
        });

        mqttClient.on("close", () => {
            console.log("MQTT connection closed.");
        });

        mqttClient.on("reconnect", () => {
            console.log("Reconnecting to AWS IoT...");
        });

        mqttClient.on("offline", () => {
            console.log("Client is offline.");
        });

        mqttClient.on("packetsend", (packet) => {
            console.log("Packet sent:", packet);
        });

        mqttClient.on("packetreceive", (packet) => {
            console.log("Packet received:", packet);
        });

        // Publish a message to a topic
        function publishToTopic(message) {
            const topic = 'home/lights'; // Change topic as needed
            console.log('Publishing message:', message);
            mqttClient.publish(topic, message, (err) => {
                if (err) {
                    console.error('Publish failed:', err);
                } else {
                    console.log('Message published to topic:', topic);
                }
            });
        }

        // Event listeners for button clicks
        document.getElementById('lightOnButton').addEventListener('click', function () {
            publishToTopic('on');
        });

        document.getElementById('lightOffButton').addEventListener('click', function () {
            publishToTopic('off');
        });
    </script>
</body>

</html>