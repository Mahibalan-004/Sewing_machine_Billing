<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

// Login Check
if(!isset($_SESSION['user_id'])){
    redirect("../login/login.php");
}

// Check if ID provided
if(!isset($_GET['id'])){
    redirect("list.php");
}

$id = intval($_GET['id']);

// Fetch existing data
$query = "SELECT * FROM stock WHERE id=$id LIMIT 1";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0){
    redirect("list.php");
}

$data = mysqli_fetch_assoc($result);

$success = "";
$error = "";

// On Form Submit
if($_SERVER['REQUEST_METHOD'] == "POST"){

    $item_name     = clean($_POST['item_name']);
    $brand         = clean($_POST['brand']);
    $model         = clean($_POST['model']);
    $new_stock     = intval($_POST['new_stock']);
    $min_quantity  = intval($_POST['min_quantity']);

    // Quantity Calculations
    $old_stock = intval($data['total_stock']);
    $total_stock = $old_stock + $new_stock;

    // Price fields
    $purchase      = floatval($_POST['purchase_price']);
    $actual_price  = floatval($_POST['actual_price']);
    $selling_price = floatval($_POST['selling_price']);
    $gst           = floatval($_POST['gst_percent']);
    $total_price   = floatval($_POST['total_price']);
    $warranty      = intval($_POST['warranty_months']);

    if($item_name == ""){
        $error = "Item name is required!";
    } else {

        // Image upload (optional)
        $stock_image = $data['stock_image'];

        if(isset($_FILES['stock_image']['name']) && $_FILES['stock_image']['name'] != ""){
            $imageName = time() . "_" . basename($_FILES['stock_image']['name']);
            $target = "../uploads/" . $imageName;

            if(move_uploaded_file($_FILES['stock_image']['tmp_name'], $target)){
                $stock_image = $imageName;
            }
        }

        // Update query
        $u = "
            UPDATE stock SET
                item_name = '$item_name',
                brand = '$brand',
                model = '$model',
                stock_image = '$stock_image',
                old_stock = '$old_stock',
                new_stock = '$new_stock',
                total_stock = '$total_stock',
                min_quantity = '$min_quantity',
                purchase_price = '$purchase',
                actual_price = '$actual_price',
                selling_price = '$selling_price',
                gst_percent = '$gst',
                total_price = '$total_price',
                warranty_months = '$warranty'
            WHERE id = $id
        ";

        if(mysqli_query($conn, $u)){
            $success = "Stock updated successfully!";

            // Refresh data
            $result = mysqli_query($conn, $query);
            $data = mysqli_fetch_assoc($result);

        } else {
            $error = "Database Error!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Stock Item</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .modal{
            display:none;position:fixed;left:0;top:0;width:100%;height:100%;
            background:rgba(0,0,0,0.6);justify-content:center;align-items:center;
        }
        .modal-content{
            background:#fff;padding:20px;border-radius:8px;width:300px;
        }
        .close-btn{float:right;font-weight:bold;color:red;cursor:pointer;}
    </style>
</head>
<body>

<?php include("../includes/header.php"); ?>

<div class="container">
    <div class="card">
        <h2>Edit Stock Item</h2>

        <?php if($success != ""): ?>
            <p style="color:green;"><?php echo $success; ?></p>
        <?php endif; ?>

        <?php if($error != ""): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <h3>Current Image</h3>
            <?php if($data['stock_image'] != "") { ?>
                <img src="../uploads/<?php echo $data['stock_image']; ?>" width="100" height="100">
            <?php } else { echo "No Image"; } ?>

            <br><br>

            <label>Change Image (optional)</label>
            <input type="file" name="stock_image">


            <h3>Item Details</h3>

            <label>Item Name *</label>
            <input type="text" name="item_name" value="<?php echo $data['item_name']; ?>" required>

            <label>Brand</label>
            <select name="brand" id="brand">
                <option value="">Select</option>
                <option <?php if($data['brand']=="Usha") echo "selected"; ?>>Usha</option>
                <option <?php if($data['brand']=="Jack") echo "selected"; ?>>Jack</option>
                <option <?php if($data['brand']=="Singer") echo "selected"; ?>>Singer</option>
                <option <?php if($data['brand']=="Brother") echo "selected"; ?>>Brother</option>
                <option <?php if($data['brand']=="Other") echo "selected"; ?>>Other</option>
            </select>

            <button type="button" class="btn" onclick="openBrandModal()">+ Add New Brand</button>

            <br><br>

            <label>Model</label>
            <select name="model" id="model">
                <option value="">Select</option>
                <option <?php if($data['model']=="Zigzag") echo "selected"; ?>>Zigzag</option>
                <option <?php if($data['model']=="Straight Stitch") echo "selected"; ?>>Straight Stitch</option>
                <option <?php if($data['model']=="Domestic") echo "selected"; ?>>Domestic</option>
                <option <?php if($data['model']=="Industrial") echo "selected"; ?>>Industrial</option>
            </select>

            <button type="button" class="btn" onclick="openModelModal()">+ Add New Model</button>


            <h3>Stock Quantities</h3>

            <label>Old Stock</label>
            <input type="number" value="<?php echo $data['total_stock']; ?>" disabled>

            <label>Add New Stock</label>
            <input type="number" name="new_stock" id="new_stock"
                value="0" onkeyup="calcStock()">

            <label>Total Stock</label>
            <input type="number" id="total_stock"
                value="<?php echo $data['total_stock']; ?>" disabled>

            <label>Reorder Level (Min Qty)</label>
            <input type="number" name="min_quantity" value="<?php echo $data['min_quantity']; ?>">


            <h3>Price Details</h3>

            <label>Price Per Quantity</label>
            <input type="number" step="0.01" name="purchase_price"
                id="purchase_price" value="<?php echo $data['purchase_price']; ?>" onkeyup="calcTotal()">

            <label>Actual Price</label>
            <input type="number" step="0.01" name="actual_price"
                id="actual_price" value="<?php echo $data['actual_price']; ?>" onkeyup="calcTotal()">

            <label>Selling Price</label>
            <input type="number" step="0.01" name="selling_price"
                id="selling_price" value="<?php echo $data['selling_price']; ?>" onkeyup="calcTotal()">

            <label>GST %</label>
            <input type="number" step="0.01" name="gst_percent"
                id="gst_percent" value="<?php echo $data['gst_percent']; ?>" onkeyup="calcTotal()">

            <label>Total Price</label>
            <input type="number" step="0.01" name="total_price"
                id="total_price" value="<?php echo $data['total_price']; ?>" readonly>


            <h3>Warranty</h3>
            <label>Warranty in Months</label>
            <input type="number" name="warranty_months" value="<?php echo $data['warranty_months']; ?>">


            <br><br>
            <button type="submit" class="btn">Update Stock</button>
            <a href="list.php" class="btn" style="background:#7f8c8d;">Back</a>

        </form>
    </div>
</div>


<!-- BRAND MODAL -->
<div class="modal" id="brandModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeBrandModal()">X</span>
        <h3>Add New Brand</h3>
        <input type="text" id="newBrand" placeholder="Brand Name">
        <button class="btn" onclick="saveBrand()">Save</button>
    </div>
</div>

<!-- MODEL MODAL -->
<div class="modal" id="modelModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModelModal()">X</span>
        <h3>Add New Model</h3>
        <input type="text" id="newModel" placeholder="Model Name">
        <button class="btn" onclick="saveModel()">Save</button>
    </div>
</div>


<script>
function calcStock(){
    var oldStock = <?php echo $data['total_stock']; ?>;
    var newStock = parseInt(document.getElementById('new_stock').value) || 0;
    document.getElementById('total_stock').value = oldStock + newStock;
}

function calcTotal(){
    var purchase = parseFloat(document.getElementById('purchase_price').value) || 0;
    var gst = parseFloat(document.getElementById('gst_percent').value) || 0;
    var qty = parseInt(document.getElementById('new_stock').value) || 0;

    var gstAmount = (purchase * gst) / 100;
    var total = (purchase + gstAmount) * qty;

    document.getElementById('total_price').value = total.toFixed(2);
}

// Brand modal
function openBrandModal(){ document.getElementById('brandModal').style.display="flex"; }
function closeBrandModal(){ document.getElementById('brandModal').style.display="none"; }

function saveBrand(){
    var b = document.getElementById('newBrand').value;
    if(b == "") return;
    var select = document.getElementById('brand');
    var option = document.createElement("option");
    option.text = b;
    option.value = b;
    select.add(option);
    select.value = b;
    closeBrandModal();
}

// Model modal
function openModelModal(){ document.getElementById('modelModal').style.display="flex"; }
function closeModelModal(){ document.getElementById('modelModal').style.display="none"; }

function saveModel(){
    var m = document.getElementById('newModel').value;
    if(m == "") return;
    var select = document.getElementById('model');
    var option = document.createElement("option");
    option.text = m;
    option.value = m;
    select.add(option);
    select.value = m;
    closeModelModal();
}
</script>

<?php include("../includes/footer.php"); ?>

</body>
</html>
