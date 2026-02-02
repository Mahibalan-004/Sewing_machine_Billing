<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");


if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Auto Jobcard Number
$r = mysqli_query($conn, "SELECT id FROM jobcards ORDER BY id DESC LIMIT 1");
$nextID = (mysqli_num_rows($r) > 0) ? mysqli_fetch_assoc($r)['id'] + 1 : 1;
$jobcard_no = "JC-" . date("Y") . "-" . str_pad($nextID, 3, "0", STR_PAD_LEFT);

$success = $error = "";

/* FETCH CUSTOMERS */
$customers = mysqli_query($conn, "SELECT * FROM customers ORDER BY phone");

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $customer_phone = clean($_POST['customer_phone']);
    $customer_name  = clean($_POST['customer_name']);
    $customer_city  = clean($_POST['customer_city']);

    $machine_name  = clean($_POST['machine_name']);
    $serial_number = clean($_POST['serial_number']);
    $work_type     = clean($_POST['work_type']);
    $remarks       = clean($_POST['remarks']);

    if ($customer_phone == "" || $customer_name == "") {
        $error = "Customer phone & name required!";
    } else {

        /* SAVE CUSTOMER IF NOT EXISTS */
        $chk = mysqli_query($conn, "SELECT id FROM customers WHERE phone='$customer_phone'");
        if (mysqli_num_rows($chk) == 0) {
            mysqli_query($conn, "
                INSERT INTO customers(phone, name, city, created_at)
                VALUES('$customer_phone','$customer_name','$customer_city',NOW())
            ");
        }

        /* IMAGE UPLOAD */
        $machine_image = "";
        if (!empty($_FILES['machine_image']['name'])) {
            $img = time() . "_" . basename($_FILES['machine_image']['name']);
            move_uploaded_file($_FILES['machine_image']['tmp_name'], "../uploads/$img");
            $machine_image = $img;
        }

        /* INSERT JOBCARD */
        mysqli_query($conn, "
            INSERT INTO jobcards(
                jobcard_no, jobcard_date,
                customer_phone, customer_name, customer_city,
                machine_image, machine_name, serial_number,
                work_type, remarks, created_at
            ) VALUES (
                '$jobcard_no', NOW(),
                '$customer_phone','$customer_name','$customer_city',
                '$machine_image','$machine_name','$serial_number',
                '$work_type','$remarks',NOW()
            )
        ");

            $_SESSION['success'] = "Jobcard created successfully!";
            header("Location: create.php");
            exit();
        
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Create Jobcard</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, .6)
        }

        .modal-content {
            background: #fff;
            width: 400px;
            margin: 100px auto;
            padding: 20px
        }
        .section-title{
            background: #f5f7fa;
            padding: 10px 15px;
            border-left: 16px solid #3498db;
            border-radius: 6px;
        }
        .section-title:hover{
            color:#3498db;
            transition:0.3s ease;
        }
        .readonly {
            background: #eee
        }
    </style>
</head>

<body>
    <?php include("../includes/header.php"); ?>

    <div class="container">
        <div class="card">

            <h2 align="center">Create Jobcard</h2>
            <?= $success ? "<p style='color:green'>$success</p>" : "" ?>
            <?= $error ? "<p style='color:red'>$error</p>" : "" ?>

            <form method="POST" enctype="multipart/form-data">

                <h3  class="section-title">Jobcard Info</h3>
                <div class="grid">
                    <input value="<?= $jobcard_no ?>" readonly class="readonly">
                    <input value="<?= date('Y-m-d') ?>" readonly class="readonly">
                </div>
<br>
                <h3 class="section-title">Customer</h3>
                <div class="grid">

                    <select name="customer_phone" onchange="fillCustomer(this.value)">
                        <option value="">Select Phone</option>
                        <?php while ($c = mysqli_fetch_assoc($customers)) { ?>
                            <option value="<?= $c['phone'] ?>"
                                data-name="<?= $c['name'] ?>"
                                data-city="<?= $c['city'] ?>">
                                <?= $c['phone'] ?>
                            </option>
                        <?php } ?>
                    </select>

                    <input id="cname" name="customer_name" placeholder="Customer Name">
                    <input id="ccity" name="customer_city" placeholder="City">

                </div>

                <button type="button" class="btn" onclick="openCustomer()">+ Add New Customer</button>

            <br><br>
                <h3 class="section-title">Machine Details</h3>
                <div class="grid">
                    <input type="file" name="machine_image">
                    <input name="machine_name" placeholder="Machine Name">
                    <input name="serial_number" placeholder="Serial Number">
                </div>

                <label>Work Type</label>
                <select name="work_type">
                    <option>Service</option>
                    <option>Total Checkup</option>
                    <option>Free Service</option>
                </select>

                <label>Remarks</label>
                <textarea name="remarks"></textarea>

                <br><br>
                <button class="btn">Create Jobcard</button>

            </form>
        </div>
    </div>

    <!-- CUSTOMER MODAL -->
    <div class="modal" id="custModal">
        <div class="modal-content">
            <h3>New Customer</h3>

            <label>Phone</label>
            <input id="mphone">

            <label>Name</label>
            <input id="mname">

            <label>City</label>
            <input id="mcity">

            <br><br>
            <button class="btn" onclick="saveCustomer()">Save</button>
            <button class="btn" style="background:#7f8c8d" onclick="closeCustomer()">Cancel</button>
        </div>
    </div>

    <?php include("../includes/footer.php"); ?>

    <script>
        function fillCustomer(phone) {
            let opt = document.querySelector("option[value='" + phone + "']");
            if (!opt) return;
            cname.value = opt.dataset.name;
            ccity.value = opt.dataset.city;
        }

        function openCustomer() {
            custModal.style.display = 'block';
        }

        function closeCustomer() {
            custModal.style.display = 'none';
        }

        function saveCustomer() {
            cname.value = mname.value;
            ccity.value = mcity.value;

            let o = document.createElement("option");
            o.value = mphone.value;
            o.text = mphone.value;
            o.dataset.name = mname.value;
            o.dataset.city = mcity.value;
            o.selected = true;

            document.querySelector("[name='customer_phone']").appendChild(o);
            closeCustomer();
        }
    </script>

</body>

</html>