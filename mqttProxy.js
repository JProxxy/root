import mqtt from "mqtt";
import { STSClient, AssumeRoleCommand } from "@aws-sdk/client-sts"; // v3 SDK imports
import https from "https"; // Use https module for secure server
import fs from "fs"; // File system module to read certificates
import { WebSocketServer } from "ws"; // Use WebSocketServer export from 'ws'

// Your AWS IoT endpoint
const endpoint = "a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com";

// Read SSL certificates (replace these paths with your actual cert files)
const privateKey = fs.readFileSync("/path/to/private-key.pem", "utf8");
const certificate = fs.readFileSync("/path/to/certificate.pem", "utf8");
const ca = fs.readFileSync("/path/to/ca.pem", "utf8");

// Create the AWS IoT credentials provider using v3 SDK
const stsClient = new STSClient({ region: "ap-southeast-1" });

// Use the AWS credentials to assume a role or use IAM
const getAWSCredentials = async () => {
  const params = {
    RoleArn: "arn:aws:iam::207567780437:role/EC2IoTAccessRole", // Your role ARN
    RoleSessionName: "IoTSession",
  };
  try {
    const data = await stsClient.send(new AssumeRoleCommand(params));
    if (data.Credentials) {
      return data.Credentials;
    } else {
      console.error("No credentials returned from sts:assumeRole");
    }
  } catch (error) {
    console.error("Error assuming role:", error);
  }
};

// MQTT WebSocket Client setup
const startMQTTConnection = async () => {
  const credentials = await getAWSCredentials();

  if (!credentials) {
    console.error("Failed to retrieve AWS credentials.");
    return;
  }

  // Make sure credentials are available and valid
  const { AccessKeyId, SecretAccessKey, SessionToken } = credentials;

  if (!AccessKeyId || !SecretAccessKey || !SessionToken) {
    console.error("Missing AWS credentials (AccessKeyId, SecretAccessKey, or SessionToken).");
    return;
  }

  const mqttClient = mqtt.connect(`wss://${endpoint}/mqtt`, {
    clientId: "mqtt-user",
    username: AccessKeyId, // Using AccessKeyId as the username
    password: SecretAccessKey, // Using SecretAccessKey as the password
    rejectUnauthorized: false, // Optional, depending on your setup
    connectTimeout: 3000, // Optional, adjust timeout for your needs
    protocol: "wss", // Ensuring we're using WebSocket protocol
    headers: {
      "x-amz-security-token": SessionToken, // Using SessionToken for the WebSocket connection
    },
  });

  mqttClient.on("connect", () => {
    console.log("Connected to AWS IoT Core");
    mqttClient.subscribe("esp32/sub", (err) => {
      if (err) {
        console.error("Subscription failed:", err);
      } else {
        console.log("Subscribed to topic: esp32/sub");
      }
    });
  });

  mqttClient.on("message", (topic, message) => {
    console.log(`Received message from ${topic}: ${message.toString()}`);
    // Proxy the message to connected WebSocket clients
    wss.clients.forEach((client) => {
      if (client.readyState === WebSocket.OPEN) {
        client.send(message.toString());
      }
    });
  });

  mqttClient.on("error", (err) => {
    console.error("MQTT Client Error:", err);
  });

  mqttClient.on("close", () => {
    console.log("MQTT Client connection closed");
  });

  return mqttClient;
};

// WebSocket server setup
const server = https.createServer(
  {
    key: privateKey,
    cert: certificate,
    ca: ca, // If using CA cert
  },
  (req, res) => {
    res.writeHead(200);
    res.end("Secure WebSocket server is running.");
  }
);

const wss = new WebSocketServer({ server }); // WebSocketServer with HTTPS server

wss.on("connection", (ws) => {
  console.log("New WebSocket client connected");
  ws.on("message", (message) => {
    console.log(`Received message from WebSocket client: ${message}`);
    // You can add logic to forward messages to AWS IoT Core here if needed
  });

  ws.on("close", () => {
    console.log("WebSocket client disconnected");
  });
});

// Start both MQTT and WebSocket servers
server.listen(8080, () => {
  console.log("WebSocket server listening on port 8080 (HTTPS)");
});

// Start MQTT connection
startMQTTConnection().catch(console.error);
