<?php
include("../config/db.php");

if(isset($_POST['save'])){

    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $full_name  = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    $password   = mysqli_real_escape_string($conn, $_POST['password']);
    $address    = mysqli_real_escape_string($conn, $_POST['address']);
    $course     = mysqli_real_escape_string($conn, $_POST['course']);

    // CHECK IF STUDENT EXISTS
    $check = mysqli_query($conn,
        "SELECT * FROM students WHERE student_id='$student_id'");

    if(mysqli_num_rows($check) > 0){

        echo "<script>
            alert('Student ID already exists! Please use a different ID.');
            window.location='add_student.php';
        </script>";

    } else {

            mysqli_query($conn,
        "INSERT INTO students
        (student_id,full_name,email,password,address,course)
        VALUES
        ('$student_id','$full_name','$email','$password','$address','$course')");

        echo "<script>
            alert('Student Added Successfully');
            window.location='add_student.php';
        </script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Add Student</title>

<link rel="stylesheet" href="../assets/css/style.css">
<style>

textarea{
    width:100%;
    padding:12px;
    margin-top:10px;
    border:1px solid #ccc;
    border-radius:5px;
    resize:vertical;
}

</style>

</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    Add Student
</div>

<!-- MENU -->
<div class="menu" style="text-align:center; margin-top:20px;">

    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_marks.php">Manage Marks</a>
    <a href="manage_attendance.php">Manage Attendance</a>
    <a href="logout.php">Logout</a>

</div>

<hr>

<div class="container">

<h2>Add Student</h2>

<form method="POST">

<input type="text"
name="student_id"
placeholder="Student ID"
required>

<input type="text"
name="full_name"
placeholder="Full Name"
required>

<input type="email"
name="email"
placeholder="Email"
required>

<input type="password"
name="password"
placeholder="Password"
required>

<textarea
name="address"
placeholder="Address"
rows="4"
required></textarea>




<input type="text"
name="course"
placeholder="Course"
required>

<button name="save">Save Student</button>

</form>

</div>

</body>
</html>
