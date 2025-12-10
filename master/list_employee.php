<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

// Only admin can view employee list
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin"){
    redirect("../login/login.php");
}

// Fetch all employees
$query = "SELECT * FROM employees ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Employee List</title>
    <link rel="stylesheet" href="../style.css">

    <style>
        .active { color: green; font-weight: bold; }
        .inactive { color: red; font-weight: bold; }
    </style>
</head>
<body>

<?php include("../includes/header.php"); ?>

<div class="container">

    <div class="card">
        <h2>Employee List</h2>

        <a href="new_employee.php" class="btn" style="margin-bottom:15px;">+ Add New Employee</a>

        <table>
            <tr>
                <th>ID</th>
                <th>Employee Code</th>
                <th>Name</th>
                <th>Mobile</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            <?php while($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>

                    <td><?php echo $row['emp_code']; ?></td>

                    <td><?php echo $row['name']; ?></td>

                    <td><?php echo $row['mobile']; ?></td>

                    <td><?php echo $row['email']; ?></td>

                    <td><?php echo ucfirst($row['role']); ?></td>

                    <td>
                        <?php 
                        if($row['status'] == "active")
                            echo "<span class='active'>Active</span>";
                        else
                            echo "<span class='inactive'>Inactive</span>";
                        ?>
                    </td>

                    <td>
                        <a class="btn" href="edit_employee.php?id=<?php echo $row['id']; ?>">Edit</a>
                    </td>
                </tr>
            <?php } ?>

        </table>

    </div>

</div>

<?php include("../includes/footer.php"); ?>

</body>
</html>
