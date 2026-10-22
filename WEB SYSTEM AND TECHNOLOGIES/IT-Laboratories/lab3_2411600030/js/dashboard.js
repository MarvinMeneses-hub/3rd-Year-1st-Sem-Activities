// ================================
// DASHBOARD SCRIPT
// ================================

document.addEventListener("DOMContentLoaded", function () {


    // ================================
    // CHECK LOGIN
    // ================================

    const isLoggedIn = localStorage.getItem("isLoggedIn");

    if (isLoggedIn !== "true") {

        window.location.href = "index.html";

        return;

    }


    // ================================
    // DISPLAY USERNAME
    // ================================

    const username = localStorage.getItem("username");

    const welcomeName = document.getElementById("welcomeName");

    if (username) {

        welcomeName.textContent = username;

    }


    // ================================
    // STATISTICS
    // ================================

    const stats = [

        {
            title: "Course",
            value: "BSIT"
        },

        {
            title: "Skills",
            value: "5"
        },

        {
            title: "Projects",
            value: "3"
        },

        {
            title: "Year Level",
            value: "BSIT-2"
        }

    ];


    stats.forEach(function (stat, index) {

        const number = index + 1;

        document.getElementById(`stat${number}-title`)
            .textContent = stat.title;

        document.getElementById(`stat${number}-value`)
            .textContent = stat.value;

    });


    // ================================
    // RECENT ACTIVITY
    // ================================

    const activities = [

        {
            activity: "Created Personal Profile Website",
            category: "Web Development",
            status: "Completed"
        },

        {
            activity: "Worked on Student Information System",
            category: "Programming",
            status: "In Progress"
        },

        {
            activity: "Updated Personal Portfolio",
            category: "Portfolio",
            status: "Completed"
        },

        {
            activity: "Studied Bootstrap 5",
            category: "Web Development",
            status: "Completed"
        }

    ];


    const activityTable = document.getElementById("activityTable");


    activities.forEach(function (item) {

        const row = document.createElement("tr");

        row.innerHTML = `

            <td>${item.activity}</td>

            <td>${item.category}</td>

            <td>
                <span class="badge ${
                    item.status === "Completed"
                        ? "bg-success"
                        : "bg-warning text-dark"
                }">
                    ${item.status}
                </span>
            </td>

        `;

        activityTable.appendChild(row);

    });


    // ================================
    // LOGOUT
    // ================================

    const logoutButton = document.getElementById("logoutButton");

    logoutButton.addEventListener("click", function () {

        localStorage.removeItem("isLoggedIn");

        localStorage.removeItem("username");

        window.location.href = "index.html";

    });

});