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

        <!-- Wrap input inside a form -->
        <form id="forgotPassForm">
            <div class="inputsign-container">
                <i class="fas fa-envelope"></i> <!-- Email Icon -->
                <input type="email" id="EmailCheck" name="Email" placeholder="Email" required>
            </div>

            <button type="submit" class="forgotPassButton">Send Code</button>
        </form>
    </div>

    <script>
        document.getElementById("forgotPassForm").addEventListener("submit", function (event) {
            event.preventDefault(); // Prevent default form submission

            let form = document.getElementById("forgotPassForm");
            let emailInput = document.getElementById("EmailCheck");
            let email = emailInput.value.trim();

            if (email === "") {
                alert("Please enter your email.");
                return;
            }

            console.log("Button clicked, sending request..."); // Debugging log

            // Create FormData object
            let formData = new FormData(form);

            fetch("../scripts/check-email.php", {
                method: "POST",
                body: formData,
            })
            .then(response => response.text()) // Read response as text
            .then(text => {
                let jsonStart = text.indexOf("{");
                let jsonEnd = text.lastIndexOf("}");
                if (jsonStart !== -1 && jsonEnd !== -1) {
                    let jsonString = text.substring(jsonStart, jsonEnd + 1);
                    return JSON.parse(jsonString);
                } else {
                    throw new Error("Invalid JSON response from server.");
                }
            })
            .then(data => {
                if (data.success) {
                    alert("A verification code has been sent to your email.");
                    console.log("OTP sent successfully.");
                    // Redirect user to verification page if needed
                    // window.location.href = "verify-code.php";
                } else {
                    alert("Failed to send OTP. Please check your email and try again.");
                    console.error("OTP sending failed.");
                }
            })
            .catch(error => console.error("Error:", error));
        });
    </script>
</body>

</html>
