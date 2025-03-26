<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Logs Dashboard</title>
  <link rel="stylesheet" href="../assets/css/dashboard.css" />
  <link rel="stylesheet" href="../assets/css/settings.css" />
  <link rel="stylesheet" href="../assets/css/settings-profile.css" />
  <link rel="stylesheet" href="../assets/css/settings-password.css" />
  <link rel="stylesheet" href="../assets/css/SA-manageUsers.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
</head>
<body>
  <div class="bgMain">
    <?php include '../partials/bgMain.php'; ?>
    <div class="containerPart">
      <!-- Header -->
      <div class="headbackCont">
        <div class="imgBack">
          <a href="../templates/dashboard.php">
            <img src="../assets/images/back.png" alt="back" class="backIcon" />
          </a>
        </div>
        <div class="headerGroup">
          <div class="headerText">Logs</div>
        </div>
      </div>
      <!-- Content Wrapper (Side Panel + Logs Table) -->
      <div class="contentWrapper">
        <!-- Sidebar (kept the same as example; modify as needed) -->
        <div class="sidepanel">
          <div class="d-flex flex-column flex-shrink-0 p-3 bg-body-tertiary" style="width: 100%;">
            <ul class="nav nav-pills flex-column mb-auto">
              <br>
              <li class="nav-item">
                <a href="../templates/SA.php" class="nav-link link-body-emphasis">
                  <img src="../assets/images/icon-roles.png" alt="Manage Roles" width="16" height="16" class="me-2" />
                  Manage Roles
                </a>
              </li>
              <li>
                <a href="../templates/SA-manageUsers.php" class="nav-link link-body-emphasis">
                  <img src="../assets/images/icon-users.png" alt="Manage Users" width="16" height="16" class="me-2" />
                  Manage Users
                </a>
              </li>
              <li>
                <a href="../templates/globalLogs.php" class="nav-link active">
                  <img src="../assets/images/icon-logs.png" alt="Logs" width="16" height="16" class="me-2" />
                  Logs
                </a>
              </li>
              <li>
                <a href="../classes/getDeletedAccountData.php" class="nav-link link-body-emphasis">
                  <img src="../assets/images/icon-racoontrash.png" alt="Deleted Logs" width="16" height="16" class="me-2" />
                  Deleted User Logs
                </a>
              </li>
              <br><br><br><br><br><br><br><br><br><br>
            </ul>
          </div>
        </div>
        <!-- Main Logs Section -->
        <div class="profile-main">
          <div class="flex-containerOneSA">
            <div class="headerSACont">
              <div class="searchContainer">
                <input type="text" id="searchInputX" placeholder=" " class="searchInput" />
                <button onclick="performSearch()" class="searchButton">
                  <svg class="searchIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                  </svg>
                </button>
              </div>
            </div>
            <div class="whiteLine"></div>
            <!-- Logs Container with Dynamic Table -->
            <div class="rolesCont">
              <div id="table-container" class="table-container">
                <!-- The dynamic logs table will be inserted here -->
              </div>
            </div>
          </div>
          <!-- Dynamic Logs Table Script -->
          <script>
            document.addEventListener("DOMContentLoaded", async function () {
              try {
                // Fetch the JSON data from SA-globalLogs.php
                const response = await fetch('../scripts/SA-globalLogs.php');
                console.log("HTTP Status:", response.status);
                const text = await response.text();
                console.log("Raw Response Text:", text);
                const result = JSON.parse(text);
                console.log("Parsed Response:", result);

                // Use the "data" field for table construction (assuming logs are in result.data)
                const data = result.data;

                // Create table element and assign the CSS class
                const table = document.createElement('table');
                table.classList.add("custom-table");

                // Define headers for the logs table
                const headers = [
                  { title: "No.", class: "col-no" },
                  { title: "User ID", class: "col-user_id" },
                  { title: "Role ID", class: "col-role_id" },
                  { title: "Changed", class: "col-changed" },
                  { title: "Description", class: "col-description" }
                ];

                // Build table header
                const thead = document.createElement('thead');
                const headerRow = document.createElement('tr');
                headers.forEach(headerInfo => {
                  const th = document.createElement('th');
                  th.className = headerInfo.class;
                  th.textContent = headerInfo.title;
                  headerRow.appendChild(th);
                });
                thead.appendChild(headerRow);
                table.appendChild(thead);

                // Build table body
                const tbody = document.createElement('tbody');
                data.forEach((row, index) => {
                  const tr = document.createElement('tr');

                  headers.forEach(headerInfo => {
                    const td = document.createElement('td');
                    td.className = headerInfo.class;

                    // For "No." column, simply use the row index + 1
                    if (headerInfo.title === "No.") {
                      td.textContent = index + 1;
                    } 
                    // For other columns, extract the corresponding key from the row
                    else {
                      // Map header title to expected key name (assuming keys are lowercase)
                      // You might need to adjust the key names if your JSON uses different naming
                      let key = headerInfo.title.toLowerCase().replace(" ", "_"); 
                      td.textContent = row[key] || "";
                    }
                    tr.appendChild(td);
                  });

                  tbody.appendChild(tr);
                });
                table.appendChild(tbody);

                // Insert the table into the container (clearing any existing content)
                const container = document.getElementById('table-container');
                container.innerHTML = '';
                container.appendChild(table);
              } catch (error) {
                console.error("Error fetching dynamic table data:", error);
              }
            });
          </script>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

<script>
  // Attach an event listener to the search input field for filtering logs
  document.getElementById("searchInputX").addEventListener("input", performSearch);

  function performSearch() {
    const filter = document.getElementById("searchInputX").value.toLowerCase();
    const table = document.querySelector("table.custom-table");
    if (!table) return;
    const rows = table.querySelectorAll("tbody tr");
    rows.forEach(row => {
      const rowText = row.textContent.toLowerCase();
      row.style.display = rowText.indexOf(filter) > -1 ? "" : "none";
    });
  }
</script>
