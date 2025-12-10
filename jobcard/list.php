<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

// Login check
// if(!isset($_SESSION['user_id'])){
//     redirect("../login/login.php");
// }

// Fetch all jobcards
$query = "SELECT * FROM jobcards ORDER BY id DESC";
$result = mysqli_query($conn, $query);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Jobcard List</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<?php include("../includes/header.php"); ?>

<div class="container">
    <div class="card">
        <h2>Jobcard List</h2>

        <a href="create.php" class="btn" style="margin-bottom:15px;">+ Create New Jobcard</a>

        <table>
            <tr>
                <th>ID</th>
                <th>Jobcard No</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>City</th>
                <th>Machine</th>
                <th>Work Type</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>

            <?php while($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['jobcard_no']; ?></td>
                    <td><?php echo $row['jobcard_date']; ?></td>

                    <td><?php echo $row['customer_name']; ?></td>
                    <td><?php echo $row['customer_phone']; ?></td>
                    <td><?php echo $row['customer_city']; ?></td>

                    <td><?php echo $row['machine_name']; ?></td>
                    <td><?php echo $row['work_type']; ?></td>

                    <td>
                        <?php if($row['machine_image'] != "") { ?>
                            <img src="../uploads/<?php echo $row['machine_image']; ?>" width="50" height="50">
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
