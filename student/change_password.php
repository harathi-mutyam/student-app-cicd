<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['student_id'])){
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

if(isset($_POST['update'])){

    $old = $_POST['old_password'];
    $new = $_POST['new_password'];

    $check = mysqli_query($conn,
    "SELECT * FROM students
     WHERE student_id='$student_id'
     AND password='$old'");

    if(mysqli_num_rows($check) > 0){

        mysqli_query($conn,
        "UPDATE students
         SET password='$new'
         WHERE student_id='$student_id'");

        // ✅ SUCCESS + REDIRECT
        echo "
        <script>
            alert('Password Updated Successfully');
            window.location.href = 'student_dashboard.php';
        </script>
        ";
        exit();

    } else {

        echo "
        <script>
            alert('Old password is wrong');
        </script>
        ";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Change Password</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="container">

<h2>Change Password</h2>

<form method="POST">

<input type="password" name="old_password" placeholder="Old Password" required>

<input type="password" name="new_password" placeholder="New Password" required>

<button name="update">Update Password</button>

</form>

<br>

<!-- Optional manual link -->
<a href="student_dashboard.php">⬅ Back to Dashboard</a>

</div>

</body>
</html>
