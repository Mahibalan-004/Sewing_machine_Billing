<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

// Login check
if(!isset($_SESSION['user_id'])){
    redirect("../login/login.php");
}

// Get jobcard ID
if(!isset($_GET['id'])){
    redirect("list.php");
}

$id = intval($_GET['id']);

// Fetch existing jobcard
$query = "SELECT * FROM jobcards WHERE id=$id LIMIT 1";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0){
    redirect("list.php");
}

$data = mysqli_fetch_assoc($result);

$success = "";
$error = "";

// When form submitted
if($_SERVER['REQUEST_METHOD'] == "POST"){

    $customer_phone = clean($_POST['customer_phone']);
    $customer_name  = clean($_POST['customer_name']);
    $customer_city  = clean($_POST['customer_city']);
    $machine_name   = clean($_POST['machine_name']);
    $serial_number  = clean($_POST['serial_number']);
    $work_type      = clean($_POST['work_type']);
    $remarks        = clean($_POST['remarks']);

    if($customer_phone == "" || $customer_name == ""){
        $error = "Phone number and customer name are required.";
    } else {

        // Handle image update
        $machine_image = $data['machine_image']; // keep old image

        if(isset($_FILES['machine_image']['name']) && $_FILES['machine_image']['name'] != ""){
            $imageName = time() . "_" . basename($_FILES['machine_image']['name']);
            $targetPath = "../uploads/" . $imageName;

            if(move_uploaded_file($_FILES['machine_image']['tmp_name'], $targetPath)){
                $machine_image = $imageName;
            }
        }

        // Update DB
        $updateQuery = "
            UPDATE jobcards SET
                customer_phone = '$customer_phone',
                customer_name  = '$customer_name',
                customer_city  = '$customer_city',
                machine_image  = '$machine_image',
                machine_name   = '$machine_name',
                serial_number  = '$serial_number',
                work_type      = '$work_type',
                remarks        = '$remarks'
            WHERE id = $id
        ";

        if(mysqli_query($conn, $updateQuery)){
            $success = "Jobcard updated successfully!";
            // Refresh data
            $result = mysqli_query($conn, $query);
            $data = mysqli_fetch_assoc($result);
        } else {
            $error = "Error updating record!";
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Jobcard</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<?php include("../includes/header.php"); ?>

<div class="container">
    <div class="card">
        <h2>Edit Jobcard</h2>

        <?php if($success != ""): ?>
            <p style="color:green;"><?php echo $success; ?></p>
        <?php endif; ?>

        <?php if($error != ""): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <h3>Jobcard Info</h3>

            <label>Jobcard Number</label>
            <input type="text" value="<?php echo $data['jobcard_no']; ?>" disabled>

            <label>Date</label>
            <input type="text" value="<?php echo $data['jobcard_date']; ?>" disabled>


            <h3>Customer Information</h3>

            <label>Phone Number *</label>
            <input type="text" name="customer_phone" value="<?php echo $data['customer_phone']; ?>" required>

            <label>Customer Name *</label>
            <input type="text" name="customer_name" value="<?php echo $data['customer_name']; ?>" required>

            <label>City</label>
            <input type="text" name="customer_city" value="<?php echo $data['customer_city']; ?>">


            <h3>Machine & Work Details</h3>

            <label>Current Machine Image</label><br>
            <?php if($data['machine_image'] != "") { ?>
                <img src="../uploads/<?php echo $data['machine_image']; ?>" width="100" height="100">
            <?php } else { echo "No Image"; } ?>
            <br><br>

            <label>Change Image (optional)</label>
            <input type="file" name="machine_image">

            <label>Machine Name</label>
            <input type="text" name="machine_name" value="<?php echo $data['machine_name']; ?>">

            <label>Serial Number</label>
            <input type="text" name="serial_number" value="<?php echo $data['serial_number']; ?>">

            <label>Work Type</label>
            <select name="work_type">
                <option value="Service" <?php if($data['work_type']=="Service") echo "selected"; ?>>Service</option>
                <option value="Total Checkup" <?php if($data['work_type']=="Total Checkup") echo "selected"; ?>>Total Checkup</option>
                <option value="Free Service" <?php if($data['work_type']=="Free Service") echo "selected"; ?>>Free Service</option>
            </select>

            <label>Remarks</label>
            <textarea name="remarks"><?php echo $data['remarks']; ?></textarea>

            <br><br>
            <button type="submit" class="btn">Update Jobcard</button>
        </form>

    </div>
</div>

<?php include("../includes/footer.php"); ?>

</body>
</html>
