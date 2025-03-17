<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Activity Logs</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/userActLogPage.css">

</head>

<body>
    <?php include '../partials/bgMain.php'; ?>
    <div class="dashboardDevider">
        <nav>
            <ul>
                <li>this must be an image</li>
                <li>User Activity Log</li>
                <li>
                    <input type="text" id="searchInputX" placeholder=" " class="searchInput">
                    <button onclick="performSearch()" class="searchButton">
                        <svg class="searchIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </li>
                <li>
                    <div class="filterCont">

                    </div>
                    <div class="dateCont">

                    </div>
                    <div class="sortCont">
                        
                    </div>
                </li>
            </ul>
        </nav>
    </div>
</body>

</html>