// script.js

document.addEventListener('DOMContentLoaded', function () {
  const button = document.getElementById('controlButton');
  
  button.addEventListener('click', function () {
      fetch('http://18.139.255.32:1880/updateDeviceStatus', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json'
          },
          body: JSON.stringify({
              device: 'air-conditioner',
              status: 'on'
          })
      })
      .then(response => response.json())
      .then(data => {
          console.log('Success:', data);
      })
      .catch((error) => {
          console.error('Error:', error);
      });
  });
});
