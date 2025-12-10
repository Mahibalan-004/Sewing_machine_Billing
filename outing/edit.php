<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

// Check login
if(!isset($_SESSION['user_id'])){
    redirect("../login/login.php");
}

// Check ID
if(!isset($_GET['id'])){
    redirect("list.php");
}

$id = intval($_GET['id']);

// Fetch outing record
$query = "SELECT * FROM outing WHERE id=$id LIMIT 1";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0){
    redirect("list.php");
}

$data = mysqli_fetch_assoc($result);

$success = "";
$error = "";

// Form submit
if($_SERVER['REQUEST_METHOD'] == "POST"){

    $customer_name = clean($_POST['customer_name']);
    $phone = clean($_POST['phone']);
    $addr1 = clean($_POST['addr1']);
    $addr2 = clean($_POST['addr2']);
    $city  = clean($_POST['city']);

    $item_name = clean($_POST['item_name']);
    $serial_no = clean($_POST['serial_no']);
    $purpose   = clean($_POST['purpose']);
    $expected_return = clean($_POST['expected_return']);
    $remarks   = clean($_POST['remarks']);

    if($item_name == "" || $purpose == ""){
        $error = "Item name and purpose are required.";
    } else {

        // Handle image update
        $item_image = $data['item_image'];

        if(isset($_FILES['item_image']['name']) && $_FILES['item_image']['name'] != ""){
            $imgName = time(). "_" . basename($_FILES['item_image']['name']);
            $target = "../uploads/" . $imgName;
            if(move_uploaded_file($_FILES['item_image']['tmp_name'], $target)){
                $item_image = $imgName;
            }
        }

        // Update query
        $u = "
            UPDATE outing SET
                customer_name='$customer_name',
                phone='$phone',
                addr1='$addr1',
                addr2='$addr2',
                city='$city',
                item_image='$item_image',
                item_name='$item_name',
                serial_no='$serial_no',
                purpose='$purpose',
                expected_return='$expected_return',
                remarks='$remarks'
            WHERE id=$id
        ";

        if(mysqli_query($conn, $u)){
            $success = "Outing updated successfully!";

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
    <title>Edit Outing</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<?php include("../includes/header.php"); ?>
<div class="container">
    <div class="card">
        <h2>Edit Outing</h2>

        <?php if($success!=""){ echo "<p style='color:green;'>$success</p>"; } ?>
        <?php if($error!=""){ echo "<p style='color:red;'>$error</p>"; } ?>

        <form method="POST" enctype="multipart/form-data">

            <h3>Outing Info</h3>

            <label>Outing Number</label>
            <input type="text" value="<?php echo $data['outing_no']; ?>" disabled>

            <label>Date</label>
            <input type="text" value="<?php echo $data['outing_date']; ?>" disabled>


            <h3>Customer Information</h3>

            <label>Customer Name</label>
            <input type="text" name="customer_name" value="<?php echo $data['customer_name']; ?>">

            <label>Phone Number</label>
            <input type="text" name="phone" value="<?php echo $data['phone']; ?>">

            <label>Address Line 1</label>
            <input type="text" name="addr1" value="<?php echo $data['addr1']; ?>">

            <label>Address Line 2</label>
            <input type="text" name="addr2" value="<?php echo $data['addr2']; ?>">

            <label>City</label>
            <input type="text" name="city" value="<?php echo $data['city']; ?>">


            <h3>Item Details</h3>

            <label>Current Image</label><br>
            <?php if($data['item_image']!=""){ ?>
                <img src="../uploads/<?php echo $data['item_image']; ?>" width="100" height="100">
            <?php } else { echo "No Image"; } ?>
            <br><br>

            <label>Change Image</label>
            <input type="file" name="item_image">

            <label>Item / Machine Name *</label>
            <input type="text" name="item_name" value="<?php echo $data['item_name']; ?>" required>

            <label>Serial Number</label>
            <input type="text" name="serial_no" value="<?php echo $data['serial_no']; ?>">

            <label>Purpose *</label>
            <select name="purpose" required>
                <option value="">Select Purpose</option>
                <option <?php if($data['purpose']=="Service") echo "selected"; ?>>Service</option>
                <option <?php if($data['purpose']=="Delivery") echo "selected"; ?>>Delivery</option>
                <option <?php if($data['purpose']=="Demo") echo "selected"; ?>>Demo</option>
                <option <?php if($data['purpose']=="Pickup") echo "selected"; ?>>Pickup</option>
                <option <?php if($data['purpose']=="Transport") echo "selected"; ?>>Transport</option>
                <option <?php if($data['purpose']=="Return") echo "selected"; ?>>Return</option>
            </select>

            <label>Expected Return Date</label>
            <input type="date" name="expected_return" value="<?php echo $data['expected_return']; ?>">

            <label>Remarks</label>
            <textarea name="remarks"><?php echo $data['remarks']; ?></textarea>

            <br><br>
            <button type="submit" class="btn">Update Outing</button>
            <a href="list.php" class="btn" style="background:#7f8c8d;">Back</a>

        </form>

    </div>
</div>

<?php include("../includes/footer.php"); ?>
</body>
</html>
