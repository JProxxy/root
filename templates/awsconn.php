<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AWS IoT MQTT Web Client</title>
    <script src="../scripts/mqtt.min.js"></script> <!-- Include mqtt.min.js -->
</head>

<body>
    <h1>AWS IoT MQTT Web Client</h1>

    <!-- MQTT Client UI -->
    <div>
        <h3>Status: <span id="status">Disconnected</span></h3>
        <input type="text" id="message" placeholder="Enter your message">
        <button onclick="publishMessage()">Publish</button>
        <h3>Messages:</h3>
        <ul id="messages"></ul>
    </div>

    <script>
        // AWS IoT MQTT Client details
        const endpoint = 'a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com'; // Replace with your endpoint
        const clientId = '1'; // Unique client ID
        const topicPublish = 'esp32/pub';  // Topic for publishing messages (e.g., to send commands)
        const topicSubscribe = 'esp32/sub'; // Topic for subscribing to messages (e.g., to receive data)

        // Certificates (base64 encoded or served over HTTPS)
        const cert = `-----BEGIN CERTIFICATE-----
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
-----END CERTIFICATE-----`;

        const key = `-----BEGIN RSA PRIVATE KEY-----
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
TVieZcnJlSYzqgfTcplYve8CgYBGhuW9Zx2B5W5nd29/cdpgS5DbUoBxetnqNK0u
26aR4l07g4m+sgbBjrjNmF10udtEFXwZ1ybmhNYDpb0bGEQIHxFqu75rNJZXOpu5
O3tI8c3w8eGJeHs5q6GXYztYXzHWeHwwq2JdIrHZM7QyMoV1vSKGVZlpgZ5k46DZ
eYBpzSOY3gxcFnlYxgTxyQ==
-----END RSA PRIVATE KEY-----`;

        const ca = `-----BEGIN CERTIFICATE-----
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

        // MQTT connection setup
        const client = mqtt.connect({
            host: 'a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com',
            port: 443,  // WebSocket over TLS port
            protocol: 'wss',  // WebSocket protocol (wss://)
            clientId: 'mqtt-client-' + Math.random().toString(36).substr(2, 9),
            cert: cert,  // Your certificate (if needed)
            key: key,    // Your private key (if needed)
            ca: ca,      // Your CA certificate (if needed)
            debug: console.log,
        });

        // Event listeners for client connection and status
        client.on('connect', function () {
            console.log('Connected to AWS IoT');
            document.getElementById('status').textContent = "Connected";
            client.subscribe(topicSubscribe, function (err) {
                if (err) {
                    console.error("Subscription failed:", err);
                } else {
                    console.log(`Subscribed to ${topicSubscribe}`);
                }
            });
        });

        client.on('error', function (err) {
            console.log('MQTT error:', err);
            document.getElementById('status').textContent = "Error";
        });

        client.on('close', function () {
            console.log('MQTT connection closed');
            document.getElementById('status').textContent = "Disconnected";
        });

        client.on('reconnect', function () {
            console.log('Reconnecting...');
        });

        client.on('offline', function () {
            console.log('Client is offline');
            document.getElementById('status').textContent = "Offline";
        });

        client.on('message', function (topic, message) {
            console.log(`Message received on ${topic}: ${message.toString()}`);
            const messagesList = document.getElementById('messages');
            const newMessage = document.createElement('li');
            newMessage.textContent = message.toString();
            messagesList.appendChild(newMessage);
        });

        // Function to publish a message
        function publishMessage() {
            const message = document.getElementById('message').value;
            if (message) {
                console.log('Publishing message:', message);
                client.publish(topicPublish, JSON.stringify({ message: message }), function (err) {
                    if (err) {
                        console.error('Publish error:', err);
                    } else {
                        console.log('Message Published');
                    }
                });
            } else {
                console.log('No message entered');
            }
        }
    </script>
</body>

</html>