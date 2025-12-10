<?php
$conn = mysqli_connect("localhost", "root","gasc", "billing_db");

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
} else {
    echo "Database connected successfully!";
}
?>
