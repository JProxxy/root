<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MQTT.js MQTT Test with Certificates</title>
    <script src="https://cdn.jsdelivr.net/npm/mqtt/dist/mqtt.min.js"></script>
</head>

<body>
    <h1>MQTT.js MQTT Test with Certificates</h1>
    <button onclick="publishMessage()">Publish Test Message</button>
    <ul id="messages"></ul>

    <script>
        // Replace with your actual IoT endpoint
        const endpoint = 'a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com'; // Ensure this is correct

        // Replace with the appropriate Base64-encoded certificate and private key
        const certificate = `-----BEGIN CERTIFICATE-----
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

        const clientId = 'TestClient';  // Client ID for the MQTT connection

        // Creating the MQTT client with certificates over port 8883 (non-WebSocket MQTT)
        const client = mqtt.connect('mqtts://' + endpoint + ':8883', {
            clientId: clientId,
            clean: true,
            connectTimeout: 4000,
            rejectUnauthorized: true,  // Ensure valid certificate is required
            protocol: 'mqtts',  // Use 'mqtts' for MQTT over TLS
            cert: certificate,  // Add certificate
            key: privateKey,    // Add private key
        });


        client.on('connect', () => {
            console.log('Connected to AWS IoT');
            client.subscribe('esp32/sub', (err) => {
                if (!err) {
                    console.log('Subscribed to esp32/sub');
                } else {
                    console.log('Subscription failed:', err);
                }
            });
        });

        client.on('error', (err) => {
            console.log('Connection failed with error:', err);
        });

        client.on('close', () => {
            console.log('Connection closed');
        });

        client.on('reconnect', () => {
            console.log('Attempting to reconnect...');
        });

        client.on('offline', () => {
            console.log('Client is offline');
        });

        client.on('message', (topic, message) => {
            console.log('Received message:', message.toString());
            const li = document.createElement('li');
            li.textContent = message.toString();
            document.getElementById('messages').appendChild(li);
        });

        function publishMessage() {
            client.publish('esp32/pub', 'Hello from the Web');
            console.log('Message sent to esp32/pub');
        }
    </script>
</body>

</html>