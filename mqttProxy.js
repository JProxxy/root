import mqtt from 'mqtt';
import AWS from 'aws-sdk';
import http from 'http';
import WebSocket from 'ws';

// Your AWS IoT endpoint
const endpoint = 'a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com';

// Create the AWS IoT credentials provider
AWS.config.update({ region: 'ap-southeast-1' });
const sts = new AWS.STS();

// Use the AWS credentials to assume a role or use IAM
const getAWSCredentials = async () => {
  const params = {
    RoleArn: 'arn:aws:iam::207567780437:role/EC2IoTAccessRole', // Your role ARN
    RoleSessionName: 'IoTSession',
  };
  try {
    const data = await sts.assumeRole(params).promise();
    return data.Credentials;
  } catch (error) {
    console.error('Error assuming role:', error);
  }
};

// MQTT WebSocket Client setup
const startMQTTConnection = async () => {
  const credentials = await getAWSCredentials();

  const mqttClient = mqtt.connect(`wss://${endpoint}/mqtt`, {
    clientId: 'mqtt-user',
    username: credentials.AccessKeyId, // Assuming IAM Role credentials
    password: credentials.SecretAccessKey,
    rejectUnauthorized: false,
  });

  mqttClient.on('connect', () => {
    console.log('Connected to AWS IoT Core');
    mqttClient.subscribe('esp32/sub', (err) => {
      if (err) {
        console.error('Subscription failed:', err);
      } else {
        console.log('Subscribed to topic: esp32/sub');
      }
    });
  });

  mqttClient.on('message', (topic, message) => {
    console.log(`Received message from ${topic}: ${message.toString()}`);
    // Proxy the message to connected WebSocket clients
    wss.clients.forEach(client => {
      if (client.readyState === WebSocket.OPEN) {
        client.send(message.toString());
      }
    });
  });

  mqttClient.on('error', (err) => {
    console.error('MQTT Client Error:', err);
  });

  mqttClient.on('close', () => {
    console.log('MQTT Client connection closed');
  });

  return mqttClient;
};

// WebSocket server setup
const server = http.createServer();
const wss = new WebSocket.Server({ server });

wss.on('connection', (ws) => {
  console.log('New WebSocket client connected');
  ws.on('message', (message) => {
    console.log(`Received message from WebSocket client: ${message}`);
    // You can add logic to forward messages to AWS IoT Core here if needed
  });

  ws.on('close', () => {
    console.log('WebSocket client disconnected');
  });
});

// Start both MQTT and WebSocket servers
server.listen(8080, () => {
  console.log('WebSocket server listening on port 8080');
});

// Start MQTT connection
startMQTTConnection().catch(console.error);
