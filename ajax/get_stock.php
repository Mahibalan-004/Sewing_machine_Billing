<?php
require_once("../config/db.php");

$q = $_GET['q'] ?? '';

$sql = "SELECT * FROM stocks WHERE item_name LIKE '%$q%' OR part_no LIKE '%$q%'";
$res = mysqli_query($conn, $sql);

$data = [];
while($row = mysqli_fetch_assoc($res)){
    $data[] = $row;
}

echo json_encode($data);
