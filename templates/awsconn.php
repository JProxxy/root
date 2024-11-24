<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AWS IoT MQTT Web Client</title>
    <script src="https://cdn.jsdelivr.net/npm/mqtt/dist/mqtt.min.js"></script> <!-- Include mqtt.min.js -->
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
        const endpoint = 'a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com'; // Provided endpoint
        const clientId = '1'; // Unique client ID
        const topicPublish = 'esp32/pub';  // Topic for publishing messages
        const topicSubscribe = 'esp32/sub'; // Topic for subscribing to messages

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
jGgw0QSr5/6hOVXvt0FOBeQJvsrhGKCY0yS9hxaZtbYkvMvBwtZ1l6X99sSOtGna
7WUzBmyc8omcgytHZuwWpGHxRT8IT5usxuKh1tQRaDch7zwAq9r/8bBUqk6O5rx4
IMwti2kCgYBa73hBYRLyY4QZm02noQbdwMkqTo7eVuRykETX2mQFE2+RxErY3PZo
XGbf6wTQ2tRZ5T2oT5dx4Xitj97huvQdeDwvs1TGw7Aew2SvJrFzZr7Hg79gHfjl
w90fcb4FCFFIQvljpQHj2Db0QmcNnMkElVWSkt0R2JbCDh+ZTJhK1UET70yx2is8
6klbswKvg0vFA45gSgEmKw==
-----END RSA PRIVATE KEY-----`;

        // Create MQTT client using AWS IoT endpoint, WebSocket URL with TLS/SSL (WSS)
        const client = mqtt.connect(`wss://${endpoint}:443/mqtt`, {
            clientId: clientId,
            username: '',  // Leave empty if not using username/password authentication
            password: '',  // Leave empty if not using username/password authentication
            cert: cert,  // Provide the certificate
            key: key,    // Provide the private key
            rejectUnauthorized: true, // Ensure authorization validation
            protocol: 'wss', // Ensures WebSocket over TLS
            clean: true,   // Clean session
        });

        // When the client connects
        client.on('connect', function () {
            console.log('Connected to AWS IoT');
            document.getElementById("status").textContent = 'Connected';
            client.subscribe(topicSubscribe, function (err) {
                if (err) {
                    console.log('Error subscribing to topic: ' + err);
                }
            });
        });

        // Handle incoming messages
        client.on('message', function (topic, message) {
            const messagesList = document.getElementById("messages");
            const listItem = document.createElement("li");
            listItem.textContent = `Received message: ${message.toString()}`;
            messagesList.appendChild(listItem);
        });

        // Log any errors that occur
        client.on('error', function (error) {
            console.log('Error:', error);
            document.getElementById("status").textContent = 'Connection failed';
        });

        // Publish message
        function publishMessage() {
            const message = document.getElementById('message').value;
            if (message) {
                client.publish(topicPublish, message, function (err) {
                    if (err) {
                        console.log('Error publishing message: ' + err);
                    } else {
                        console.log('Message published successfully');
                    }
                });
            }
        }
    </script>
</body>

</html>
