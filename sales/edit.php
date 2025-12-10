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
$sale_id = intval($_GET['id']);

// Fetch order
$orderQ = mysqli_query($conn, "SELECT * FROM sales WHERE id=$sale_id LIMIT 1");
if(mysqli_num_rows($orderQ) == 0){
    redirect("list.php");
}
$order = mysqli_fetch_assoc($orderQ);

// Fetch items
$itemQ = mysqli_query($conn, "SELECT * FROM sales_items WHERE sale_id=$sale_id");

$success = "";
$error = "";

// On update
if($_SERVER['REQUEST_METHOD'] == "POST"){

    $order_status = clean($_POST['order_status']);
    $order_date   = clean($_POST['order_date']);
    $customer_phone = clean($_POST['customer_phone']);
    $customer_name  = clean($_POST['customer_name']);
    $addr1 = clean($_POST['addr1']);
    $addr2 = clean($_POST['addr2']);
    $city  = clean($_POST['city']);

    $total_amount = floatval($_POST['total_amount']);
    $paid_amount  = floatval($_POST['paid_amount']);
    $balance      = floatval($_POST['balance']);

    // Update order table
    $u = "
        UPDATE sales SET 
            order_status='$order_status',
            order_date='$order_date',
            customer_phone='$customer_phone',
            customer_name='$customer_name',
            addr1='$addr1',
            addr2='$addr2',
            city='$city',
            total_amount='$total_amount',
            paid_amount='$paid_amount',
            balance='$balance'
        WHERE id=$sale_id
    ";

    if(mysqli_query($conn, $u)){

        // Delete existing items
        mysqli_query($conn, "DELETE FROM sales_items WHERE sale_id=$sale_id");

        // Re-insert updated items
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
                    price_per_qty, gst_percent, total_price, item_image
                ) VALUES (
                    '$sale_id', '$item_name', '$part', '$qty',
                    '$price', '$gst', '$tprice', '$item_img'
                )
            ");
        }

        $success = "Sales order updated successfully!";
        // Refresh new data
        $orderQ = mysqli_query($conn, "SELECT * FROM sales WHERE id=$sale_id LIMIT 1");
        $order = mysqli_fetch_assoc($orderQ);
        $itemQ = mysqli_query($conn, "SELECT * FROM sales_items WHERE sale_id=$sale_id");

    } else {
        $error = "Error updating order!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Sales Order</title>
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
        <h2>Edit Sales Order</h2>

        <?php if($success!=""){ echo "<p style='color:green;'>$success</p>"; } ?>
        <?php if($error!=""){ echo "<p style='color:red;'>$error</p>"; } ?>

        <form method="POST" enctype="multipart/form-data">

            <h3>Order Info</h3>

            <label>Order Status</label>
            <select name="order_status">
                <option value="New" <?php if($order['order_status']=="New") echo "selected"; ?>>New</option>
                <option value="Invoice" <?php if($order['order_status']=="Invoice") echo "selected"; ?>>Invoice</option>
            </select>

            <label>Order Date</label>
            <input type="date" name="order_date" value="<?php echo $order['order_date']; ?>">

            <h3>Customer Information</h3>

            <label>Phone Number</label>
            <input type="text" name="customer_phone" value="<?php echo $order['customer_phone']; ?>">

            <label>Customer Name</label>
            <input type="text" name="customer_name" value="<?php echo $order['customer_name']; ?>">

            <label>Address Line 1</label>
            <input type="text" name="addr1" value="<?php echo $order['addr1']; ?>">

            <label>Address Line 2</label>
            <input type="text" name="addr2" value="<?php echo $order['addr2']; ?>">

            <label>City</label>
            <input type="text" name="city" value="<?php echo $order['city']; ?>">

            <br><br>
            <h3>Sales Items</h3>

            <div id="items-container">

                <?php while($item = mysqli_fetch_assoc($itemQ)) { ?>
                <div class="item-row">

                    <label>Current Image</label><br>
                    <?php if($item['item_image']!=""){ ?>
                        <img src="../uploads/<?php echo $item['item_image']; ?>" width="60">
                    <?php } else { echo "No Image"; } ?>
                    <br>

                    <label>Change Image</label>
                    <input type="file" name="item_img[]">

                    <label>Item Name</label>
                    <input type="text" name="item[]" value="<?php echo $item['item_name']; ?>">

                    <label>Part/Serial No.</label>
                    <input type="text" name="part[]" value="<?php echo $item['part_no']; ?>">

                    <label>Quantity</label>
                    <input type="number" step="0.01" name="qty[]" value="<?php echo $item['qty']; ?>" onkeyup="calcRow(this)">

                    <label>Price / Qty</label>
                    <input type="number" step="0.01" name="price[]" value="<?php echo $item['price_per_qty']; ?>" onkeyup="calcRow(this)">

                    <label>GST %</label>
                    <input type="number" step="0.01" name="gst[]" value="<?php echo $item['gst_percent']; ?>" onkeyup="calcRow(this)">

                    <label>Total Price</label>
                    <input type="number" step="0.01" name="tprice[]" class="row-total" value="<?php echo $item['total_price']; ?>" readonly>

                    <br>
                    <span class="delete-btn" onclick="deleteRow(this)">Delete</span>
                </div>
                <?php } ?>

            </div>

            <br>
            <span class="add-btn" onclick="addItem()">+ Add New Item</span>

            <br><br>
            <h3>Payment Details</h3>

            <label>Total Amount</label>
            <input type="number" name="total_amount" id="total_amount" value="<?php echo $order['total_amount']; ?>" readonly>

            <label>Paid Amount</label>
            <input type="number" name="paid_amount" id="paid_amount" value="<?php echo $order['paid_amount']; ?>" onkeyup="updateBalance()">

            <label>Balance</label>
            <input type="number" name="balance" id="balance" value="<?php echo $order['balance']; ?>" readonly>

            <br><br>
            <button type="submit" class="btn">Update Order</button>
            <a href="list.php" class="btn" style="background:#7f8c8d;">Back</a>

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
    totals.forEach(function(t){ sum += parseFloat(t.value) || 0; });

    document.getElementById('total_amount').value = sum.toFixed(2);
    updateBalance();
}

function updateBalance(){
    var total = parseFloat(document.getElementById('total_amount').value) || 0;
    var paid  = parseFloat(document.getElementById('paid_amount').value) || 0;
    document.getElementById('balance').value = (total - paid).toFixed(2);
}

function deleteRow(btn){
    btn.parentNode.remove();
    calcTotalAmount();
}

function addItem(){
    var container = document.getElementById('items-container');
    var rows = container.getElementsByClassName('item-row');
    var clone = rows[0].cloneNode(true);

    clone.querySelectorAll("input").forEach(function(inp){
        if(inp.type == "file"){ inp.value = ""; }
        else{ inp.value = ""; }
    });

    container.appendChild(clone);
}
</script>

</body>
</html>
