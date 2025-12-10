<?php
session_start();
require_once("../config/db.php");
require_once("../includes/functions.php");

// Check login
if(!isset($_SESSION['user_id'])){
    redirect("../login/login.php");
}

$success = "";
$error = "";

// Insert new stock
if($_SERVER['REQUEST_METHOD'] == "POST"){

    $item_name     = clean($_POST['item_name']);
    $brand         = clean($_POST['brand']);
    $model         = clean($_POST['model']);
    $new_stock     = intval($_POST['new_stock']);
    $min_quantity  = intval($_POST['min_quantity']);
    $purchase      = floatval($_POST['purchase_price']);
    $actual_price  = floatval($_POST['actual_price']);
    $selling_price = floatval($_POST['selling_price']);
    $gst           = floatval($_POST['gst_percent']);
    $total_price   = floatval($_POST['total_price']);
    $warranty      = intval($_POST['warranty_months']);

    // Old stock always 0 for new items
    $old_stock  = 0;
    $total_stock = $new_stock;

    if($item_name == ""){
        $error = "Item name is required!";
    } else {

        // Image upload
        $stock_image = "";
        if(isset($_FILES['stock_image']['name']) && $_FILES['stock_image']['name'] != ""){
            $imageName = time() . "_" . basename($_FILES['stock_image']['name']);
            $target = "../uploads/" . $imageName;
            if(move_uploaded_file($_FILES['stock_image']['tmp_name'], $target)){
                $stock_image = $imageName;
            }
        }

        // Insert
        $query = "
            INSERT INTO stock (
                item_name, brand, model, stock_image,
                old_stock, new_stock, total_stock, min_quantity,
                purchase_price, actual_price, selling_price,
                gst_percent, total_price, warranty_months,
                created_at
            ) VALUES (
                '$item_name', '$brand', '$model', '$stock_image',
                '$old_stock', '$new_stock', '$total_stock', '$min_quantity',
                '$purchase', '$actual_price', '$selling_price',
                '$gst', '$total_price', '$warranty',
                NOW()
            )
        ";

        if(mysqli_query($conn, $query)){
            $success = "Stock item added successfully!";
        } else {
            $error = "Database Error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>New Stock Item</title>
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
        <h2>Add New Stock Item</h2>

        <?php if($success != ""): ?>
            <p style="color:green;"><?php echo $success; ?></p>
        <?php endif; ?>

        <?php if($error != ""): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <h3>Stock Image</h3>
            <input type="file" name="stock_image">

            <h3>Item Details</h3>

            <label>Item Name *</label>
            <input type="text" name="item_name" required>

            <label>Brand</label>
            <select name="brand" id="brand">
                <option value="">Select Brand</option>
                <option>Usha</option>
                <option>Jack</option>
                <option>Singer</option>
                <option>Brother</option>
                <option>Other</option>
            </select>

            <button type="button" class="btn" onclick="openBrandModal()">+ Add New Brand</button>

            <br><br>

            <label>Model</label>
            <select name="model" id="model">
                <option value="">Select Model</option>
                <option>Zigzag</option>
                <option>Straight Stitch</option>
                <option>Domestic</option>
                <option>Industrial</option>
            </select>

            <button type="button" class="btn" onclick="openModelModal()">+ Add New Model</button>



            <h3>Stock Quantities</h3>

            <label>Old Stock (Auto = 0)</label>
            <input type="number" value="0" disabled>

            <label>New Stock *</label>
            <input type="number" name="new_stock" id="new_stock" value="0" onkeyup="calcStock()" required>

            <label>Total Stock</label>
            <input type="number" id="total_stock" value="0" disabled>

            <label>Reorder Level (Min Quantity)</label>
            <input type="number" name="min_quantity" value="0">

            <h3>Price Details</h3>

            <label>Price Per Quantity *</label>
            <input type="number" step="0.01" name="purchase_price" id="purchase_price" value="0" onkeyup="calcTotal()" required>

            <label>Actual Price *</label>
            <input type="number" step="0.01" name="actual_price" id="actual_price" value="0" onkeyup="calcTotal()" required>

            <label>Selling Price *</label>
            <input type="number" step="0.01" name="selling_price" id="selling_price" value="0" onkeyup="calcTotal()" required>

            <label>GST %</label>
            <input type="number" step="0.01" name="gst_percent" id="gst_percent" value="0" onkeyup="calcTotal()">

            <label>Total Price</label>
            <input type="number" step="0.01" name="total_price" id="total_price" readonly>


            <h3>Warranty</h3>
            <label>Warranty in Months</label>
            <input type="number" name="warranty_months" value="0">


            <br><br>
            <button type="submit" class="btn">Submit</button>
            <button type="reset" class="btn" style="background:#e74c3c;">Reset</button>

        </form>
    </div>
</div>


<!-- BRAND MODAL -->
<div class="modal" id="brandModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeBrandModal()">X</span>
        <h3>Add Brand</h3>
        <input type="text" id="newBrand" placeholder="Brand Name">
        <button class="btn" onclick="saveBrand()">Save</button>
    </div>
</div>

<!-- MODEL MODAL -->
<div class="modal" id="modelModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModelModal()">X</span>
        <h3>Add Model</h3>
        <input type="text" id="newModel" placeholder="Model Name">
        <button class="btn" onclick="saveModel()">Save</button>
    </div>
</div>


<script>
function calcStock(){
    var newStock = parseInt(document.getElementById('new_stock').value) || 0;
    document.getElementById('total_stock').value = newStock;
}

function calcTotal(){
    var purchase = parseFloat(document.getElementById('purchase_price').value) || 0;
    var gst = parseFloat(document.getElementById('gst_percent').value) || 0;
    var qty = parseInt(document.getElementById('new_stock').value) || 0;

    var gstAmount = (purchase * gst) / 100;
    var total = (purchase + gstAmount) * qty;

    document.getElementById('total_price').value = total.toFixed(2);
}

// BRAND MODAL
function openBrandModal(){ document.getElementById('brandModal').style.display="flex"; }
function closeBrandModal(){ document.getElementById('brandModal').style.display="none"; }
function saveBrand(){
    var newBrand = document.getElementById('newBrand').value;
    if(newBrand == "") return;
    var select = document.getElementById('brand');
    var option = document.createElement("option");
    option.text = newBrand;
    option.value = newBrand;
    select.add(option);
    select.value = newBrand;
    closeBrandModal();
}

// MODEL MODAL
function openModelModal(){ document.getElementById('modelModal').style.display="flex"; }
function closeModelModal(){ document.getElementById('modelModal').style.display="none"; }
function saveModel(){
    var newModel = document.getElementById('newModel').value;
    if(newModel == "") return;
    var select = document.getElementById('model');
    var option = document.createElement("option");
    option.text = newModel;
    option.value = newModel;
    select.add(option);
    select.value = newModel;
    closeModelModal();
}
</script>

<?php include("../includes/footer.php"); ?>

</body>
</html>
