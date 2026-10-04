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

// For edit, check if mark ID is provided
$editData = null;
if(isset($_GET['edit'])){
    $id = $_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM marks WHERE id='$id'");
    $editData = mysqli_fetch_assoc($res);
}

// Save new mark
if(isset($_POST['save'])){
    $subject_name = $_POST['subject_name'];
    $marks = $_POST['marks'];

    mysqli_query($conn, "INSERT INTO marks(student_id, subject_name, marks) VALUES('$student_id','$subject_name','$marks')");

    header("Location: student_profile.php?id=$redirect_id");
    exit;
}

// Update existing mark
if(isset($_POST['update'])){
    $id = $_POST['id'];
    $subject_name = $_POST['subject_name'];
    $marks = $_POST['marks'];

    mysqli_query($conn, "UPDATE marks SET subject_name='$subject_name', marks='$marks' WHERE id='$id'");
    header("Location: student_profile.php?id=$redirect_id");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Student Marks</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.container{width:40%;margin:50px auto;background:white;padding:20px;border-radius:10px;}
input{width:100%;padding:10px;margin:10px 0;}
button{padding:10px 15px;background:#0d6efd;color:white;border:none;cursor:pointer;}
</style>
</head>
<body>

<div class="container">
<h2><?php echo $editData ? "Edit Marks" : "Add Marks for ".$student['full_name']; ?></h2>

<form method="POST">
    <?php
    if($editData){
        echo '<input type="hidden" name="id" value="'.$editData['id'].'">';
    }
    ?>

    <label>Subject Name</label><br>
    <input type="text" name="subject_name" required value="<?php echo $editData['subject_name'] ?? ''; ?>"><br>

    <label>Marks</label><br>
    <input type="number" name="marks" required value="<?php echo $editData['marks'] ?? ''; ?>"><br>

    <button type="submit" name="<?php echo $editData ? 'update' : 'save'; ?>">
        <?php echo $editData ? "Update Marks" : "Save Marks"; ?>
    </button>
</form>
</div>

</body>
</html>
