<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

// Login check
if(!isset($_SESSION['user_id'])){
    redirect("../login/login.php");
}

// Auto-generate outing number
$qr = mysqli_query($conn, "SELECT id FROM outing ORDER BY id DESC LIMIT 1");
$nextID = (mysqli_num_rows($qr) > 0) ? mysqli_fetch_assoc($qr)['id'] + 1 : 1;
$outing_no = "OUT-" . date("Y") . "-" . str_pad($nextID, 3, "0", STR_PAD_LEFT);

$success = "";
$error = "";

// Form Submit
if($_SERVER['REQUEST_METHOD'] == "POST"){

    // Customer Info
    $customer_name = clean($_POST['customer_name']);
    $phone = clean($_POST['phone']);
    $addr1 = clean($_POST['addr1']);
    $addr2 = clean($_POST['addr2']);
    $city  = clean($_POST['city']);

    // Item Info
    $item_name = clean($_POST['item_name']);
    $serial_no = clean($_POST['serial_no']);
    $purpose   = clean($_POST['purpose']);
    $expected_return = clean($_POST['expected_return']);
    $remarks   = clean($_POST['remarks']);

    if($item_name == "" || $purpose == ""){
        $error = "Item name and purpose are required.";
    } else {

        // Image Upload
        $item_image = "";
        if(isset($_FILES['item_image']['name']) && $_FILES['item_image']['name'] != ""){
            $imgName = time() . "_" . basename($_FILES['item_image']['name']);
            $targetPath = "../uploads/" . $imgName;
            if(move_uploaded_file($_FILES['item_image']['tmp_name'], $targetPath)){
                $item_image = $imgName;
            }
        }

        // Insert into DB
        $q = "
            INSERT INTO outing (
                outing_no, outing_date,
                customer_name, phone, addr1, addr2, city,
                item_image, item_name, serial_no, purpose,
                expected_return, remarks, created_at
            ) VALUES (
                '$outing_no', NOW(),
                '$customer_name', '$phone', '$addr1', '$addr2', '$city',
                '$item_image', '$item_name', '$serial_no', '$purpose',
                '$expected_return', '$remarks', NOW()
            )
        ";

        if(mysqli_query($conn, $q)){
            $success = "Outing record created successfully!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Outing</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<?php include("../includes/header.php"); ?>

<div class="container">
    <div class="card">
        <h2>Create Outing</h2>

        <?php if($success!=""){ echo "<p style='color:green;'>$success</p>"; } ?>
        <?php if($error!=""){ echo "<p style='color:red;'>$error</p>"; } ?>

        <form method="POST" enctype="multipart/form-data">

            <h3>Outing Info</h3>

            <label>Outing Number</label>
            <input type="text" value="<?php echo $outing_no; ?>" disabled>

            <label>Outing Date</label>
            <input type="text" value="<?php echo date('Y-m-d'); ?>" disabled>

            <h3>Customer Information (Optional)</h3>

            <label>Customer Name</label>
            <input type="text" name="customer_name">

            <label>Phone Number</label>
            <input type="text" name="phone">

            <label>Address Line 1</label>
            <input type="text" name="addr1">

            <label>Address Line 2</label>
            <input type="text" name="addr2">

            <label>City</label>
            <input type="text" name="city">


            <h3>Item Details</h3>

            <label>Item Image</label>
            <input type="file" name="item_image">

            <label>Item / Machine Name *</label>
            <input type="text" name="item_name" required>

            <label>Serial Number</label>
            <input type="text" name="serial_no">

            <label>Purpose *</label>
            <select name="purpose" required>
                <option value="">Select Purpose</option>
                <option>Service</option>
                <option>Delivery</option>
                <option>Demo</option>
                <option>Pickup</option>
                <option>Transport</option>
                <option>Return</option>
            </select>

            <label>Expected Return Date</label>
            <input type="date" name="expected_return">

            <label>Remarks</label>
            <textarea name="remarks"></textarea>

            <br><br>
            <button type="submit" class="btn">Create Outing</button>
            <button type="reset" class="btn" style="background:#e74c3c;">Reset</button>
        </form>

    </div>
</div>

<?php include("../includes/footer.php"); ?>

</body>
</html>
