<?php
include("../config/db.php");

$id = $_GET['id'];
$student_id = $_GET['student_id'];

mysqli_query($conn, "DELETE FROM marks WHERE id=$id");

header("Location: student_profile.php?id=".$student_id);
exit();
?>
