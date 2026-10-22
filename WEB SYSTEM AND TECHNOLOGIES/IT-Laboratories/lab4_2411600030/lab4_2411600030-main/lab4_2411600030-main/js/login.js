// ================================
// LOGIN SCRIPT
// ================================

document.addEventListener("DOMContentLoaded", function () {

    const loginButton = document.getElementById("loginButton");

    loginButton.addEventListener("click", function () {

        const username = document.getElementById("username").value.trim();
        const password = document.getElementById("password").value.trim();

        const loginAlert = document.getElementById("loginAlert");


        // Check if fields are empty
        if (username === "" || password === "") {

            loginAlert.innerHTML = `
                <div class="alert alert-warning">
                    Please enter your username and password.
                </div>
            `;

            return;
        }


        // Simple login credentials
        if (username === "marvin" && password === "12345") {

            // Save login state
            localStorage.setItem("isLoggedIn", "true");

            localStorage.setItem("username", "Marvin M. Meneses");

            // Redirect to dashboard
            window.location.href = "dashboard.html";

        } else {

            loginAlert.innerHTML = `
                <div class="alert alert-danger">
                    Invalid username or password.
                </div>
            `;

        }

    });

});