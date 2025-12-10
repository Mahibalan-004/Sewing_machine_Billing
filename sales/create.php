<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

// Redirect if not logged in
if(!isset($_SESSION['user_id'])){
    redirect("../login/login.php");
}

$success = "";
$error = "";

// INSERT ORDER
if($_SERVER['REQUEST_METHOD'] == "POST"){

    $order_status = clean($_POST['order_status']);
    $order_date   = clean($_POST['order_date']);
    $customer_phone = clean($_POST['customer_phone']);
    $customer_name  = clean($_POST['customer_name']);
    $addr1 = clean($_POST['addr1']);
    $addr2 = clean($_POST['addr2']);
    $city  = clean($_POST['city']);

    // Totals
    $total_amount = floatval($_POST['total_amount']);
    $paid_amount  = floatval($_POST['paid_amount']);
    $balance      = floatval($_POST['balance']);

    // Create main sales record
    $sql = "
        INSERT INTO sales (
            order_status, order_date,
            customer_phone, customer_name, addr1, addr2, city,
            total_amount, paid_amount, balance,
            created_at
        ) VALUES (
            '$order_status', '$order_date',
            '$customer_phone', '$customer_name', '$addr1', '$addr2', '$city',
            '$total_amount', '$paid_amount', '$balance',
            NOW()
        )
    ";

    if(mysqli_query($conn, $sql)){
        $sale_id = mysqli_insert_id($conn);

        // Insert each item row
        foreach($_POST['item'] as $i => $item_name){

            if(trim($item_name) == "") continue;

            $part = clean($_POST['part'][$i]);
            $qty  = floatval($_POST['qty'][$i]);
            $price = floatval($_POST['price'][$i]);
            $gst   = floatval($_POST['gst'][$i]);
            $tprice = floatval($_POST['tprice'][$i]);

            // Image upload
            $item_img = "";
            if(isset($_FILES['item_img']['name'][$i]) && $_FILES['item_img']['name'][$i] != ""){
                $imgName = time()."_".basename($_FILES['item_img']['name'][$i]);
                $path = "../uploads/".$imgName;
                if(move_uploaded_file($_FILES['item_img']['tmp_name'][$i], $path)){
                    $item_img = $imgName;
                }
            }

            mysqli_query($conn, "
                INSERT INTO sales_items (
                    sale_id, item_name, part_no, qty,
                    price_per_qty, gst_percent, total_price,
                    item_image
                ) VALUES (
                    '$sale_id', '$item_name', '$part', '$qty',
                    '$price', '$gst', '$tprice',
                    '$item_img'
                )
            ");
        }

        $success = "Sales order created successfully!";
    } else {
        $error = "Error saving order!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Sales Order</title>
    <link rel="stylesheet" href="../style.css">

    <style>
        .item-row{border:1px solid #ddd;padding:10px;margin-bottom:10px;border-radius:5px;}
        .delete-btn{background:#e74c3c;color:#fff;padding:5px 10px;border-radius:4px;cursor:pointer;}
        .add-btn{background:#2ecc71;color:#fff;padding:7px 15px;border-radius:4px;cursor:pointer;}
    </style>
</head>
<body>

<?php include("../includes/header.php"); ?>

<div class="container">
    <div class="card">
        <h2>Create Sales Order</h2>

        <?php if($success!=""){ echo "<p style='color:green;'>$success</p>"; } ?>
        <?php if($error!=""){ echo "<p style='color:red;'>$error</p>"; } ?>

        <form method="POST" enctype="multipart/form-data">

            <h3>Order Info</h3>

            <label>Order Status</label>
            <select name="order_status">
                <option value="New">New</option>
                <option value="Invoice">Invoice</option>
            </select>

            <label>Order Date</label>
            <input type="date" name="order_date" value="<?php echo date('Y-m-d'); ?>">

            <h3>Customer Information</h3>

            <label>Phone Number</label>
            <input type="text" name="customer_phone">

            <label>Customer Name</label>
            <input type="text" name="customer_name">

            <label>Address Line 1</label>
            <input type="text" name="addr1">

            <label>Address Line 2</label>
            <input type="text" name="addr2">

            <label>City</label>
            <input type="text" name="city">

            <br><br>
            <h3>Sales Items</h3>

            <div id="items-container">

                <div class="item-row">

                    <label>Image</label>
                    <input type="file" name="item_img[]">

                    <label>Item Name</label>
                    <input type="text" name="item[]" required>

                    <label>Part/Serial No.</label>
                    <input type="text" name="part[]">

                    <label>Quantity</label>
                    <input type="number" step="0.01" name="qty[]" onkeyup="calcRow(this)" value="1">

                    <label>Price / Qty</label>
                    <input type="number" step="0.01" name="price[]" onkeyup="calcRow(this)" value="0">

                    <label>GST %</label>
                    <input type="number" step="0.01" name="gst[]" onkeyup="calcRow(this)" value="0">

                    <label>Total Price</label>
                    <input type="number" step="0.01" name="tprice[]" class="row-total" readonly>

                    <br>
                    <span class="delete-btn" onclick="deleteRow(this)">Delete</span>
                </div>

            </div>

            <br>
            <span class="add-btn" onclick="addItem()">+ Add New Item</span>

            <br><br>
            <h3>Payment Details</h3>

            <label>Total Amount</label>
            <input type="number" step="0.01" name="total_amount" id="total_amount" readonly>

            <label>Paid Amount</label>
            <input type="number" step="0.01" name="paid_amount" id="paid_amount" onkeyup="updateBalance()">

            <label>Balance</label>
            <input type="number" step="0.01" name="balance" id="balance" readonly>

            <br><br>
            <button type="submit" class="btn">Submit Order</button>
        </form>

    </div>
</div>

<?php include("../includes/footer.php"); ?>

<script>
function calcRow(input){
    var row = input.parentNode;
    var qty = parseFloat(row.querySelector("input[name='qty[]']").value) || 0;
    var price = parseFloat(row.querySelector("input[name='price[]']").value) || 0;
    var gst = parseFloat(row.querySelector("input[name='gst[]']").value) || 0;

    var gstAmt = (price * gst) / 100;
    var total = (price + gstAmt) * qty;

    row.querySelector("input[name='tprice[]']").value = total.toFixed(2);

    calcTotalAmount();
}

function calcTotalAmount(){
    var totals = document.querySelectorAll(".row-total");
    var sum = 0;
    for(var i=0;i<totals.length;i++){
        sum += parseFloat(totals[i].value) || 0;
    }
    document.getElementById('total_amount').value = sum.toFixed(2);
    updateBalance();
}

function updateBalance(){
    var total = parseFloat(document.getElementById('total_amount').value) || 0;
    var paid  = parseFloat(document.getElementById('paid_amount').value) || 0;
    document.getElementById('balance').value = (total - paid).toFixed(2);
}

function deleteRow(btn){
    var row = btn.parentNode;
    row.remove();
    calcTotalAmount();
}

function addItem(){
    var container = document.getElementById('items-container');
    var clone = container.children[0].cloneNode(true);

    // Reset fields
    clone.querySelectorAll("input").forEach(function(input){
        if(input.type=="file"){
            input.value = "";
        } else {
            input.value = "";
        }
    });

    container.appendChild(clone);
}
</script>

</body>
</html>
