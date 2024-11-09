<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/mainTheme.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <script>
        function handleButtonClick(item) {
            alert("You clicked on " + item);
            // functionality here based on the item
            // For example, redirecting to a different page
            // window.location.href = 'path/to/page';
        }
    </script>
</head>

<body>
    <div class="mainContainer">
        <div class="sidePanel">
            <div class="topBar">
                <div class="topBarItem" onclick="window.location.href='dashboard.php'">
                    <img src="../assets/images/rivanLogo.png" alt="Logo" class="iconLogo" />
                </div>

                <div class="middleItems">
                    <div class="middleItem" onclick="window.location.href='officeSpace.php'">
                        <img src="../assets/images/officeSpace.png" alt="Office Space" class="icon" />
                        <span>Office<br>Space</span>
                    </div>

                    <div class="middleItem" onclick="handleButtonClick('Analytics')">
                        <img src="../assets/images/analytics.png" alt="Analytics" class="icon" />
                        <span>Analytics</span>
                    </div>
                    <div class="middleItem" onclick="handleButtonClick('Members')">
                        <img src="../assets/images/members.png" alt="Members" class="icon" />
                        <span>Members</span>
                    </div>
                </div>
                <div class="lowerBarItem">
                    <img src="../assets/images/logout.png" alt="Log Out" class="iconLogout" />
                    <span>Log Out</span>
                </div>
            </div>
        </div>

        <div class="profile">
            <img src="../assets/images/defaultProfile.png" alt="Profile Icon" class="profile-image" />
        </div>

        <div class="mainContent">

        </div>
    </div>
</body>

</html>