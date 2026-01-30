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
</head>
<body>

<div class="topbar">
    <div class="brand">Billing System</div>

    <ul class="menu-bar">

        <li class="menu-item">
            <a href="../login/dashboard.php">Dashboard</a>
        </li>

        <li class="menu-item has-dropdown">
            <a href="#" class="menu-link">Job Card</a>
            <div class="dropdown">
                <a href="../jobcard/create.php">Create Job</a>
                <a href="../jobcard/list.php">Job Card Bills</a>
                <a href="../jobcard/labor.php">Labor Summary</a>
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
            <a href="#" class="menu-link">Outing</a>
            <div class="dropdown">
                <a href="../outing/create.php">Create Outing</a>
                <a href="../outing/list.php">Outing List</a>
            </div>
        </li>

        <li class="menu-item has-dropdown">
            <a href="#" class="menu-link">Employees</a>
            <div class="dropdown">
                <a href="../master/new_employee.php">Add Employee</a>
                <a href="../master/list_employee.php">Employee List</a>
            </div>
        </li>
        <li class="logout-btn"><a href="../login/logout.php">Logout</a></li>
    </ul>
    
</div>

<script>
// Toggle dropdown safely
document.querySelectorAll(".menu-link").forEach(link => {
    link.addEventListener("click", function (e) {
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
document.addEventListener("click", function (e) {
    if (!e.target.closest(".menu-item")) {
        document.querySelectorAll(".dropdown").forEach(d => {
            d.style.display = "none";
        });
    }
});
</script>

</body>
</html>
