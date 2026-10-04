<?php
session_start();
include("../config/db.php");   // ✅ correct path for admin folder

// security check (admin only)
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

// fetch student
$result = mysqli_query($conn, "SELECT * FROM students WHERE id='$id'");
$row = mysqli_fetch_assoc($result);

// if student not found
if(!$row){
    echo "Student not found";
    exit();
}

// update logic
if(isset($_POST['update'])){

    $student_id = $_POST['student_id'];
    $full_name  = $_POST['full_name'];
    $email      = $_POST['email'];
    $password   = $_POST['password'];
    $address    = $_POST['address'];

    $course     = $_POST['course'];

    $sql = "UPDATE students SET
            student_id='$student_id',
            full_name='$full_name',
            email='$email',
            password='$password',
            address='$address',
            course='$course'
            WHERE id='$id'";

    mysqli_query($conn, $sql);

    header("Location: admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="navbar">
    Edit Student
</div>

<div class="container">

<h2>Edit Student Details</h2>

<form method="POST">

    <label>Student ID</label>
    <input type="text" name="student_id"
           value="<?php echo $row['student_id']; ?>" required>

    <label>Full Name</label>
    <input type="text" name="full_name"
           value="<?php echo $row['full_name']; ?>" required>

    <label>Email</label>
    <input type="email" name="email"
           value="<?php echo $row['email']; ?>">
<label>Password</label>
<input type="text" name="password"
       value="<?php echo $row['password']; ?>">

<label>Address</label>
<textarea name="address"
style="width:100%;padding:10px;"><?php echo $row['address']; ?></textarea>

    <label>Course</label>
    <input type="text" name="course"
           value="<?php echo $row['course']; ?>">

    <button type="submit" name="update">Update Student</button>

</form>

<br>
<a href="admin_dashboard.php">⬅ Back to Dashboard</a>

</div>

</body>
</html>
