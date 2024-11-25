const WebSocket = require('ws');

// Create a WebSocket server on port 8080
const wss = new WebSocket.Server({ port: 8080 });

wss.on('connection', ws => {
  console.log('A new client connected');
  
  // Send a message to the client when they connect
  ws.send('Hello Client!');

  // Listen for messages from the client
  ws.on('message', message => {
    console.log('Received from client: ', message);
    // Send a response to the client
    ws.send(`You said: ${message}`);
  });
  
  // When the connection is closed
  ws.on('close', () => {
    console.log('Client disconnected');
  });
});

console.log('WebSocket server running on ws://<EC2_PUBLIC_IP>:8080');
