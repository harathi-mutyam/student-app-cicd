<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

// Get student ID and redirect ID
$student_id = $_GET['student_id'] ?? '';
$redirect_id = $_GET['redirect_id'] ?? '';

if(!$student_id || !$redirect_id){
    echo "Invalid request!";
    exit;
}

// Fetch student info
$student_res = mysqli_query($conn, "SELECT * FROM students WHERE student_id='$student_id'");
$student = mysqli_fetch_assoc($student_res);
if(!$student){
    echo "Student not found!";
    exit;
}

// For edit, check if attendance ID is provided
$editData = null;
if(isset($_GET['edit'])){
    $id = $_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM attendance WHERE id='$id'");
    $editData = mysqli_fetch_assoc($res);
}

// Save new attendance
if(isset($_POST['save'])){
    $date = $_POST['attendance_date'];
    $status = $_POST['status'];

    mysqli_query($conn, "INSERT INTO attendance(student_id, attendance_date, status) VALUES('$student_id','$date','$status')");
    header("Location: student_profile.php?id=$redirect_id");
    exit;
}

// Update existing attendance
if(isset($_POST['update'])){
    $id = $_POST['id'];
    $date = $_POST['attendance_date'];
    $status = $_POST['status'];

    mysqli_query($conn, "UPDATE attendance SET attendance_date='$date', status='$status' WHERE id='$id'");
    header("Location: student_profile.php?id=$redirect_id");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Attendance</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.container{width:40%;margin:50px auto;background:white;padding:20px;border-radius:10px;}
input, select{width:100%;padding:10px;margin:10px 0;}
button{padding:10px 15px;background:#0d6efd;color:white;border:none;cursor:pointer;}
</style>
</head>
<body>

<div class="container">
<h2><?php echo $editData ? "Edit Attendance" : "Add Attendance for ".$student['full_name']; ?></h2>

<form method="POST">
    <?php if($editData){ ?>
        <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
    <?php } ?>

    <label>Date</label><br>
    <input type="date" name="attendance_date" required value="<?php echo $editData['attendance_date'] ?? ''; ?>"><br>

    <label>Status</label><br>
    <select name="status" required>
        <option value="">--Select--</option>
        <option value="Present" <?php if(isset($editData) && $editData['status']=='Present') echo 'selected'; ?>>Present</option>
        <option value="Absent" <?php if(isset($editData) && $editData['status']=='Absent') echo 'selected'; ?>>Absent</option>
    </select><br>

    <button type="submit" name="<?php echo $editData ? 'update' : 'save'; ?>">
        <?php echo $editData ? "Update Attendance" : "Save Attendance"; ?>
    </button>
</form>
</div>

</body>
</html>

