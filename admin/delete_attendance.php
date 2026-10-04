<?php
include("../config/db.php");

$id = $_GET['id'];
$student_id = $_GET['student_id'];

/* Delete attendance */
mysqli_query($conn,
"DELETE FROM attendance WHERE id=$id");

/* Redirect back */
header("Location: student_profile.php?id=".$student_id);
exit();
?>
