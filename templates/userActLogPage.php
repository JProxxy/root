<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Activity Logs</title>

  <link rel="stylesheet" href="../assets/css/dashboard.css" />
  <link rel="stylesheet" href="../assets/css/userActLogPage.css" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />

  <style>
    .profile-pic {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 10px;
      object-fit: cover;
      vertical-align: middle;
    }
  </style>
</head>

<body>
  <?php include '../partials/bgMain.php'; ?>
  <div class="dashboardDevider">
    <div class="userActLogCont">
      <nav>
        <ul>
          <li>
            <img src="../assets/images/back.png" alt="Logo" width="50" height="50">
          </li>
          <li>User Activity Log</li>
          <li>
            <div class="searchContainer">
              <input type="text" id="searchInputX" placeholder=" " class="searchInput">
              <button onclick="filterTable()" class="searchButton">
                <svg class="searchIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="11" cy="11" r="8"></circle>
                  <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
              </button>
            </div>
          </li>
          <li>
            <div class="filterCont" style="cursor:pointer;">
              <img src="../assets/images/icon-filter.png" alt="Logo" width="20" height="25" style="margin-right: 10px;">
              <span> Filter </span>
            </div>
            <div class="dateCont" style="cursor:pointer;">
              <img src="../assets/images/icon-date.png" alt="Logo" width="25" height="25" style="margin-right: 10px;">
              <span> Date </span>
            </div>
            <div class="sortCont" style="cursor:pointer;">
              <img src="../assets/images/icon-sort.png" alt="Logo" width="25" height="25" style="margin-right: 10px;">
              <span> Sort </span>
            </div>
          </li>
        </ul>
      </nav>

      <div class="actLogTable">
        <table>
          <thead>
            <tr>
              <th>Role</th>
              <th>Username</th>
              <th>Floor</th>
              <th>Action Taken</th>
              <th>Timestamp</th>
            </tr>
          </thead>
          <tbody id="tableBody"></tbody>

          <!-- Data will be inserted here -->
          </tbody>
        </table>
      </div>

      <script>
        // Fetch and populate the table data
        document.addEventListener("DOMContentLoaded", function () {
          function fetchUserActivityLogs() {
            fetch("../scripts/getUserActLogPage.php")
              .then(response => response.json())
              .then(data => populateTable(data))
              .catch(error => console.error("Error fetching user activity logs:", error));
          }

          function populateTable(data) {
            const tableBody = document.getElementById("tableBody");
            tableBody.innerHTML = ""; // Clear previous data

            data.forEach(user => {
              const row = document.createElement("tr");
              row.innerHTML = `
      <td class="role">${user.role_id}</td>
      <td class="username">
        <img src="${user.profile_picture}" alt="Profile Picture" class="profile-pic">
        <div>
          <div>${user.name}</div>
          <div style="font-size: 0.9em; color: gray;">${user.email}</div>
        </div>
      </td>
      <td class="floor">${user.floor || '-'}</td>
      <td class="action">${user.action}</td>
      <td class="log-time">${user.timestamp}</td>
    `;
              tableBody.appendChild(row);
            });
          }


          // Basic search filter for the search input field
          window.filterTable = function () {
            const searchInput = document.getElementById("searchInputX").value.toLowerCase();
            const rows = document.querySelectorAll("#tableBody tr");

            rows.forEach(row => {
              const id = row.querySelector(".log-id").textContent.toLowerCase();
              const username = row.querySelector(".username").textContent.toLowerCase();
              const action = row.querySelector(".action").textContent.toLowerCase();
              const time = row.querySelector(".log-time").textContent.toLowerCase();
              if (id.includes(searchInput) || username.includes(searchInput) || action.includes(searchInput) || time.includes(searchInput)) {
                row.style.display = "";
              } else {
                row.style.display = "none";
              }
            });
          };

          // Fetch data when the page loads
          fetchUserActivityLogs();
        });
      </script>

      <style>
        .status-authorized {
          background-color: #ceffc2;
          color: #3b5d33;
          padding: 5px 10px;
          border-radius: 15px;
          font-weight: bold;
        }

        .status-unauthorized {
          background-color: #dd0000;
          color: #ffcece;
          padding: 5px 10px;
          border-radius: 15px;
          font-weight: bold;
        }
      </style>

      <!-- New script to add Filter, Date, and Sort functionality -->
      <script>
        document.addEventListener("DOMContentLoaded", function () {
          const filterCont = document.querySelector(".filterCont");
          const dateCont = document.querySelector(".dateCont");
          const sortCont = document.querySelector(".sortCont");

          // Filter by status (authorized / unauthorized)
          filterCont.addEventListener("click", function () {
            const status = prompt("Enter status to filter (authorized/unauthorized) or leave empty to reset:");
            filterByStatus(status);
          });

          // Filter by date (expects a string like YYYY-MM-DD)
          dateCont.addEventListener("click", function () {
            const dateInput = prompt("Enter date to filter (YYYY-MM-DD) or leave empty to reset:");
            filterByDate(dateInput);
          });

          // Sort by timestamp (ascending order)
          sortCont.addEventListener("click", function () {
            sortTableByTimestamp();
          });

          function filterByStatus(status) {
            const rows = document.querySelectorAll("#tableBody tr");
            rows.forEach(row => {
              const statusCell = row.querySelector("td:nth-child(5)");
              // Remove extra spaces and convert to lowercase for comparison
              let statusText = statusCell.textContent.toLowerCase().trim();
              if (!status) {
                row.style.display = "";
              } else if (statusText.includes(status.toLowerCase())) {
                row.style.display = "";
              } else {
                row.style.display = "none";
              }
            });
          }

          function filterByDate(dateInput) {
            const rows = document.querySelectorAll("#tableBody tr");
            rows.forEach(row => {
              const timeCell = row.querySelector("td:nth-child(4)");
              let timeText = timeCell.textContent;
              if (!dateInput) {
                row.style.display = "";
              } else if (timeText.includes(dateInput)) {
                row.style.display = "";
              } else {
                row.style.display = "none";
              }
            });
          }

          function sortTableByTimestamp() {
            const table = document.querySelector(".actLogTable table");
            const tbody = table.querySelector("tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));

            rows.sort((a, b) => {
              const dateA = new Date(a.querySelector("td:nth-child(4)").textContent);
              const dateB = new Date(b.querySelector("td:nth-child(4)").textContent);
              return dateA - dateB;
            });

            // Re-attach sorted rows
            tbody.innerHTML = "";
            rows.forEach(row => tbody.appendChild(row));
          }
        });
      </script>
    </div>
  </div>
</body>

</html>