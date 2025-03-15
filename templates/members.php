<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members</title>
    <link rel="stylesheet" href="../assets/css/members.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>

<body>
    <div class="bgMain">
        <?php include "../partials/bgMain.php"; ?>

        <div class="dashboardDevider">

            <div class="membersCont">
                <h1>Member Information</h1>
                <div class="membersList" id="membersList">
                    <!-- Members will be dynamically added here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        function renderMembers(members) {
            const membersList = document.getElementById("membersList");
            membersList.innerHTML = ""; // Clear previous content

            members.forEach(member => {
                const memberCard = document.createElement("div");
                memberCard.classList.add("memberCard");

                // Default placeholder image if profile picture is missing
                const profilePicture = member.profile_picture || 'https://placehold.co/100';

                // Parse social media JSON safely
                let socialMediaHTML = "";
                if (member.soc_med) {
                    try {
                        const socialLinks = JSON.parse(member.soc_med); // Parse JSON string into object

                        if (socialLinks.facebook) {
                            socialMediaHTML += `<a href="${socialLinks.facebook}" target="_blank" class="no-underline">
                <img src="../assets/images/colored_facebook.png" alt="Facebook" class="social-icons">
            </a> `;
                        }
                        if (socialLinks.linkedin) {
                            socialMediaHTML += `<a href="${socialLinks.linkedin}" target="_blank" class="no-underline">
                <img src="../assets/images/colored_linkedIn.png" alt="LinkedIn" class="social-icons">
            </a> `;
                        }
                        if (socialLinks.telegram) {
                            socialMediaHTML += `<a href="${socialLinks.telegram}" target="_blank" class="no-underline">
                <img src="../assets/images/colored_telegram.png" alt="Telegram" class="social-icons">
            </a> `;
                        }
                    } catch (error) {
                        console.error("Error parsing social media JSON:", error);
                    }
                }


                memberCard.innerHTML = `
    <div class="memberCard-content">
    <div class="leftContMem">
    <div class="profile-container">
        <img class="profile-pic" src="${profilePicture}" alt="${member.full_name || 'User'}">
        <div class="status-dot"></div> <!-- Green dot -->
    </div>
</div>
        <div class="rightContMem">
            <h2>${member.full_name || 'N/A'}</h2>
            <p>${member.username || 'Unknown'}&nbsp;<strong>|</strong>&nbsp;${member.bio || 'No bio available'}</p>
            <p><img class="iconPhone" src="../assets/images/phone.png" alt="Phone"> ${member.phone_number || 'No phone'}</p>
        </div>
    </div>
    <hr class="member-divider"> <!-- Line added here -->
    <div class="social-container">
        ${socialMediaHTML || 'No social media'}
    </div>
`;


                membersList.appendChild(memberCard);
            });
        }

        function fetchMembers() {
            fetch("../scripts/fetch_members.php")
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error("Error fetching members:", data.error);
                    } else {
                        renderMembers(data);
                    }
                })
                .catch(error => console.error("Error fetching members:", error));
        }

        // Load members on page load
        fetchMembers();

    </script>
</body>

</html>