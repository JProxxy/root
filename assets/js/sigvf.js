const AWS = require('aws-sdk');

// Use the Access Key and Secret Key from the IAM user
AWS.config.update({
  accessKeyId: 'AKIATAVAA7JKQAPDC76H',     // Your Access Key
  secretAccessKey: '+ZJt5LUXkMuqpvgUQe/VTS9fyLvkZe1iVh0n0BcW', // Your Secret Access Key
  region: 'ap-southeast-1'                    // Your AWS region
});
