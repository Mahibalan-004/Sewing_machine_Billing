<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

// Ensure user is logged in
if(!isset($_SESSION['user_id'])){
    redirect("../login/login.php");
}

// Fetch all outing records
$query = "SELECT * FROM outing ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Outing List</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<?php include("../includes/header.php"); ?>

<div class="container">
    <div class="card">
        <h2>Outing Records</h2>

        <a href="create.php" class="btn" style="margin-bottom:15px;">+ Create New Outing</a>

        <table>
            <tr>
                <th>ID</th>
                <th>Outing No</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>City</th>
                <th>Item</th>
                <th>Purpose</th>
                <th>Return Date</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>

            <?php while($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>

                    <td><?php echo $row['outing_no']; ?></td>

                    <td><?php echo $row['outing_date']; ?></td>

                    <td><?php echo $row['customer_name']; ?></td>

                    <td><?php echo $row['phone']; ?></td>

                    <td><?php echo $row['city']; ?></td>

                    <td><?php echo $row['item_name']; ?></td>

                    <td><?php echo $row['purpose']; ?></td>

                    <td>
                        <?php 
                            echo ($row['expected_return'] != "") 
                            ? $row['expected_return'] 
                            : "—";
                        ?>
                    </td>

                    <td>
                        <?php if($row['item_image'] != "") { ?>
                            <img src="../uploads/<?php echo $row['item_image']; ?>" width="50" height="50">
                        <?php } else { echo "No Image"; } ?>
                    </td>

                    <td>
                        <a class="btn" href="edit.php?id=<?php echo $row['id']; ?>">Edit</a>
                    </td>
                </tr>
            <?php } ?>

        </table>

    </div>
</div>

<?php include("../includes/footer.php"); ?>

</body>
</html>
