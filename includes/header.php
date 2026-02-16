<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../style.css">
    <style>
        .menu-container {
            border-bottom: 2px solid #eee;
            background: #fff;
            position: fixed;
            width: 100%;
            z-index: 1000;
            margin-top: 50px;
            text-align: center;
        }
        .menu-bar{
            display: flex;
            padding: 0;
            margin: 0;
            text-align: center;
            /* justify-content: center; */
            margin-left: 350px;
        }
    </style>
</head>

<body>

    <div class="topbar">
        <div class="brand">Sewing Machines Billing System</div>
        <p style="align-self: center; margin-left: 890px; padding: 4px;">
            <button onclick="window.location.href='../login/logout.php'" style="background-color: #f44336; color: white; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer;">Logout</button>
        </p>
    </div>
    <br>
    <div class="menu-container">
        <ul class="menu-bar">

            <li class="menu-item">
                <a href="../login/dashboard.php">Dashboard</a>
            </li>

            <li class="menu-item has-dropdown">
                <a href="#" class="menu-link">Job Card</a>
                <div class="dropdown">
                    <a href="../jobcard/create.php">Create Job</a>
                    <a href="../jobcard/list.php">Job Card Bills</a>
                </div>
            </li>

            <li class="menu-item has-dropdown">
                <a href="#" class="menu-link">Sales</a>
                <div class="dropdown">
                    <a href="../sales/create.php">Create Sales</a>
                    <a href="../sales/list.php">Sales List</a>
                </div>
            </li>

            <li class="menu-item has-dropdown">
                <a href="#" class="menu-link">Stock</a>
                <div class="dropdown">
                    <a href="../stock/new.php">Add Stock</a>
                    <a href="../stock/list.php">Stock List</a>
                </div>
            </li>

            <li class="menu-item has-dropdown">
                <a href="#" class="menu-link">Purchase</a>
                <div class="dropdown">
                    <a href="../purchase/create.php">Create purchase</a>
                    <a href="../purchase/list.php">Purchase List</a>
                </div>
            </li>


        </ul>
    </div>

    <script>
        // Toggle dropdown safely
        document.querySelectorAll(".menu-link").forEach(link => {
            link.addEventListener("click", function(e) {
                e.preventDefault();

                let dropdown = this.nextElementSibling;

                // Close other dropdowns
                document.querySelectorAll(".dropdown").forEach(d => {
                    if (d !== dropdown) d.style.display = "none";
                });

                // Toggle current dropdown
                dropdown.style.display =
                    dropdown.style.display === "block" ? "none" : "block";
            });
        });

        // Close dropdown when clicking outside menu
        document.addEventListener("click", function(e) {
            if (!e.target.closest(".menu-item")) {
                document.querySelectorAll(".dropdown").forEach(d => {
                    d.style.display = "none";
                });
            }
        });
    </script>

</body>

</html>