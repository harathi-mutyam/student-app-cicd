<?php
session_start();
include("../config/db.php");   // ✅ correct path for admin folder

// only admin can delete
if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

// check ID
if(!isset($_GET['id'])){
    header("Location: admin_dashboard.php");
    exit();
}

$id = $_GET['id'];

// delete query (safe format)
mysqli_query($conn, "DELETE FROM students WHERE id='$id'");

// redirect back to dashboard
header("Location: admin_dashboard.php");
exit();
?>
