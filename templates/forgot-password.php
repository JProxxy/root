<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password?</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/forgot-password.css">
</head>

<body>
    <img class="backgroundImageForgot" src="../assets/images/bg-forgotpass.png" alt="Forgot Password Background">

    <div class="leftCont">
        <div class="spillOne">
            HAVING TROUBLE REMEMBERING YOUR PASSWORD?
        </div>
        <div class="spillTwo">
            We will send a secure code to your email to enhance your privacy and security.
        </div>

        <div class="inputsign-container">
            <i class="fas fa-envelope"></i> <!-- Email Icon -->
            <input type="email" id="EmailCheck" name="Email" placeholder="Email" required>
        </div>

        <button type="submit" class="forgotPassButton">Send Code</button>
    </div>

    <script>
        window.onload = function () {
            document.querySelector(".forgotPassButton").addEventListener("click", function (event) {
                event.preventDefault(); // Prevent form submission

                let emailInput = document.getElementById("EmailCheck");
                let email = emailInput.value.trim();

                if (email === "") {
                    alert("Please enter your email.");
                    return;
                }

                console.log("Button clicked, sending request..."); // Debugging log

                // Send email to the backend for validation
                fetch("../scripts/check-email.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "email=" + encodeURIComponent(email)
                })
                    .then(response => response.text()) // Read as text instead of JSON
                    .then(data => {
                        console.log("Raw response from PHP:", data); // Debugging
                        try {
                            let jsonData = JSON.parse(data);
                            if (jsonData.success) {
                                window.location.href = "../templates/reset-password.php";
                            } else {
                                alert(jsonData.message || "No account with that email is found.");
                            }
                        } catch (error) {
                            console.error("JSON Parse Error:", error, "Received:", data);
                            alert("An error occurred. Check console logs.");
                        }
                    })
                    .catch(error => console.error("Fetch error:", error));
            });
        };
    </script>
</body>

</html>
