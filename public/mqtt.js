// mqtt.js
const AWS = require('aws-sdk');
const mqtt = require('mqtt');

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
-----END CERTIFICATE-----`;

const privateKey = `-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAsRO2Y8EXncsFznZ55F7Rqi0HAEt/GnMvqsnlcXs8PuqndXpi
bCR3LWEhZXyfqp/vSTuU8X0Wr040BhE/heIX0rfArXbW5q3eREX/NO8K3GPf5f90
EeV08qPOMcYBhGbYa+/HLt3UDccy6pbECbkWTImbVymzjRCCwQQiwM6HxrYsEGSy
xpHciEVLTkrfDp1uYrOiOYUY6ZWXhpuH6gKbScnHgbbNMZ8UfNoe/+4Q5Gn8n+dG
ReFpMc/j1Kesv+WBTl+edsdL/BfdBAgozWgHY12UgqxuocBcAQ+6Mf/GKZ4Jlkkq
Z6Mpz3Z/MgJoBa2J18kpmVHNstcpjDrfTlIesQIDAQABAoIBAEs4I28GdAC8YDAO
1cJzoL6YN/QhHdHfgi0bbFKjVbkoNpBJt4tWhiWJsAULRkvVenDyVVermjpHjwPQ
ydoWaFV33z67D4k9QpRvgSYIlUs5FGy0kFgx/60YrUP2Tj0g7ix2g1KszTqXk2Xp
P7tThwtugU6TQK0t51vC8t8cBy6QX5F+I4ZXlOgpKOrDsU9gGFo8k1yCH/cIXYOw
eza4pVMjA9BZ5rYbzNRUBy+gkg1MchB0jVDRwv/VyGVjG5tWn3tTQwyZMOqFuEvT
l8fSOYniYo0wEqFfMNkN6EADkYOPZa7LtwV9m1xnzoDWTX1M74+aX2TZHzfJzdBf
kUmdpDkJghV3UmFpt0FSChh9jU9gd2g27vvCndgCggEAr3lfdGChXBISmyfKHh3s
9EOI/EXlM4S7p9TALpQqVWS5XmvBIfu5TaA== 
-----END RSA PRIVATE KEY-----`;

// Initialize the MQTT client
mqttClient = mqtt.connect(endpoint, {
  clientId: 'your-client-id',
  username: 'garage-lights', // Optional, if required
  password: rootCA, // Optional, if required
  ca: rootCA,
  cert: deviceCert,
  key: privateKey
});

// Event listeners for connecting, errors, and messages
mqttClient.on('connect', () => {
  console.log('Connected to AWS IoT');
  mqttClient.subscribe('your/topic', (err) => {
    if (err) {
      console.error('Error subscribing to topic:', err);
    } else {
      console.log('Subscribed to topic: your/topic');
    }
  });
});

mqttClient.on('message', (topic, message) => {
  console.log(`Received message on topic ${topic}:`, message.toString());
});

mqttClient.on('error', (error) => {
  console.error('Error with MQTT connection:', error);
});

// Reconnect logic with retry
mqttClient.on('close', () => {
  if (reconnectAttempts < maxReconnectAttempts) {
    console.log('Connection lost. Attempting to reconnect...');
    reconnectAttempts++;
    setTimeout(() => mqttClient.reconnect(), 5000); // Reconnect after 5 seconds
  } else {
    console.log('Maximum reconnection attempts reached. Connection failed.');
  }
});
