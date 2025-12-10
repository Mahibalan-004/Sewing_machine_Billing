<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

// If not logged in → redirect
// if(!isset($_SESSION['user_id'])){
//     redirect("../login/login.php");
// }

// Auto-generate jobcard number
$result = mysqli_query($conn, "SELECT id FROM jobcards ORDER BY id DESC LIMIT 1");
$nextID = (mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result)['id'] + 1 : 1;
$jobcard_no = "JC-" . date("Y") . "-" . str_pad($nextID, 3, "0", STR_PAD_LEFT);

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

    // Validations
    if($customer_phone == "" || $customer_name == ""){
        $error = "Phone number and customer name are required.";
    } else {

        // Handle image upload
        $machine_image = "";
        if(isset($_FILES['machine_image']['name']) && $_FILES['machine_image']['name'] != ""){
            $imageName = time() . "_" . basename($_FILES['machine_image']['name']);
            $targetPath = "../uploads/" . $imageName;
            if(move_uploaded_file($_FILES['machine_image']['tmp_name'], $targetPath)){
                $machine_image = $imageName;
            }
        }

        // Insert record
        $query = "
            INSERT INTO jobcards (
                jobcard_no, jobcard_date,
                customer_phone, customer_name, customer_city,
                machine_image, machine_name, serial_number,
                work_type, remarks, created_at
            )
            VALUES (
                '$jobcard_no', NOW(),
                '$customer_phone', '$customer_name', '$customer_city',
                '$machine_image', '$machine_name', '$serial_number',
                '$work_type', '$remarks', NOW()
            )
        ";

        if(mysqli_query($conn, $query)){
            $success = "Jobcard created successfully!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Jobcard</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<?php include("../includes/header.php"); ?>

<div class="container">
    <div class="card">
        <h2>Create Jobcard</h2>

        <!-- Success/Error Messages -->
        <?php if($success != ""): ?>
            <p style="color:green;"><?php echo $success; ?></p>
        <?php endif; ?>

        <?php if($error != ""): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>


        <form method="POST" enctype="multipart/form-data">

            <h3>Jobcard Info</h3>
            <label>Jobcard Number</label>
            <input type="text" value="<?php echo $jobcard_no; ?>" disabled>

            <label>Date</label>
            <input type="text" value="<?php echo date("Y-m-d"); ?>" disabled>

            <h3>Customer Information</h3>

            <label>Phone Number *</label>
            <input type="text" name="customer_phone" required>

            <label>Customer Name *</label>
            <input type="text" name="customer_name" required>

            <label>City</label>
            <input type="text" name="customer_city">

            <button type="button" class="btn" onclick="clearCustomer()">Clear Customer Info</button>

            <script>
                function clearCustomer(){
                    document.getElementsByName('customer_phone')[0].value = "";
                    document.getElementsByName('customer_name')[0].value = "";
                    document.getElementsByName('customer_city')[0].value = "";
                }
            </script>

            <h3>Machine & Work Details</h3>

            <label>Machine Image</label>
            <input type="file" name="machine_image">

            <label>Machine Name</label>
            <input type="text" name="machine_name">

            <label>Serial Number</label>
            <input type="text" name="serial_number">

            <label>Work Type</label>
            <select name="work_type">
                <option value="Service">Service</option>
                <option value="Total Checkup">Total Checkup</option>
                <option value="Free Service">Free Service</option>
            </select>

            <label>Remarks</label>
            <textarea name="remarks"></textarea>

            <br><br>
            <button type="submit" class="btn">Create Jobcard</button>
        </form>
    </div>
</div>

<?php include("../includes/footer.php"); ?>

</body>
</html>
