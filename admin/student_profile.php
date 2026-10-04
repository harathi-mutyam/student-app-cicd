<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

$id = $_GET['id'];

// Fetch student info
$student = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM students WHERE id='$id'")
);

if(!$student){
    echo "Student not found!";
    exit;
}

// Fetch marks for this student
$marks = mysqli_query($conn, "SELECT * FROM marks WHERE student_id='{$student['student_id']}'");

// Fetch attendance for this student
$attendance = mysqli_query($conn, "SELECT * FROM attendance WHERE student_id='{$student['student_id']}'");

/* Delete Mark (same page) */
if(isset($_POST['delete_mark'])){
    $mark_id = $_POST['id'];
    mysqli_query($conn, "DELETE FROM marks WHERE id='$mark_id'");
    header("Location: student_profile.php?id=$id");
    exit;
}

/* Delete Attendance (same page) */
if(isset($_POST['delete_attendance'])){
    $att_id = $_POST['id'];
    mysqli_query($conn, "DELETE FROM attendance WHERE id='$att_id'");
    header("Location: student_profile.php?id=$id");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Profile</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.navbar{background:#0d6efd;color:white;padding:15px;font-size:18px;}
.menu{text-align:center;margin-top:20px;}
.menu a{margin:10px;padding:10px 15px;background:#0d6efd;color:white;text-decoration:none;border-radius:5px;}
.container{width:80%;margin:20px auto;background:white;padding:20px;border-radius:10px;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th{background:#0d6efd;color:white;padding:10px;}
td{border:1px solid #ccc;padding:10px;text-align:center;}
.btn{padding:6px 10px;background:#0d6efd;color:white;text-decoration:none;border-radius:5px;}
.btn-green{background:green;}
.btn-red{background:red;}
form{display:inline;}
</style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    Student Profile
</div>

<!-- MENU -->
<div class="menu">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="add_student.php">Add Student</a>
    <a href="logout.php">Logout</a>
</div>

<!-- STUDENT INFO -->
<div class="container">
<h2>Student Info</h2>
<p><b>Name:</b> <?php echo $student['full_name']; ?></p>
<p><b>Student ID:</b> <?php echo $student['student_id']; ?></p>
<p><b>Email:</b> <?php echo $student['email'] ?? 'N/A'; ?></p>
<p><b>Address:</b> <?php echo $student['address']; ?></p>
<p><b>Password:</b> <?php echo $student['password']; ?></p>
<p><b>Course:</b> <?php echo $student['course']; ?></p>

<a class="btn btn-green" href="edit_student.php?id=<?php echo $student['id']; ?>">Edit Student</a>
</div>

<!-- MARKS -->
<div class="container">
<h2>Marks</h2>

<a class="btn" href="edit_student_marks.php?student_id=<?php echo $student['student_id']; ?>&redirect_id=<?php echo $student['id']; ?>">
    + Add Marks
</a>

<table>
<tr>
    <th>Subject</th>
    <th>Marks</th>
    <th>Actions</th>
</tr>

<?php if(mysqli_num_rows($marks) > 0){ ?>
    <?php while($m = mysqli_fetch_assoc($marks)){ ?>
    <tr>
        <td><?php echo $m['subject_name']; ?></td>
        <td><?php echo $m['marks']; ?></td>
        <td>
            <a class="btn btn-green"
               href="edit_student_marks.php?student_id=<?php echo $student['student_id']; ?>&redirect_id=<?php echo $student['id']; ?>&edit=<?php echo $m['id']; ?>">
               Edit
            </a>

            <form method="POST" style="display:inline;">
                <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
                <button type="submit" name="delete_mark"
style="
background:#dc3545;
color:white;
border:none;
padding:5px 10px;
border-radius:4px;
cursor:pointer;
font-size:13px;
width:auto;
display:inline-block;"
onclick="return confirm('Delete this mark?');">Delete</button>
            </form>
        </td>
    </tr>
    <?php } ?>
<?php } else { ?>
<tr>
    <td colspan="3">No marks available</td>
</tr>
<?php } ?>
</table>
</div>

<!-- ATTENDANCE -->
<!-- ATTENDANCE -->
<div class="container">
<h2>Attendance</h2>

<a class="btn" href="edit_student_attendance.php?student_id=<?php echo $student['student_id']; ?>&redirect_id=<?php echo $student['id']; ?>">
    + Add Attendance
</a>

<table>
<tr>
    <th>Date</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php if(mysqli_num_rows($attendance) > 0){ ?>
    <?php while($a = mysqli_fetch_assoc($attendance)){ ?>
    <tr>
        <td><?php echo $a['attendance_date']; ?></td>
        <td><?php echo $a['status']; ?></td>
        <td>
<a class="btn btn-green"
   href="edit_student_attendance.php?student_id=<?php echo $student['student_id']; ?>&redirect_id=<?php echo $student['id']; ?>&edit=<?php echo $a['id']; ?>">
   Edit
</a>
            <form method="POST"  style="display:inline;">
                <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                <button type="submit" name="delete_attendance"
                  style="
background:#dc3545;
color:white;
border:none;
padding:5px 10px;
border-radius:4px;
cursor:pointer;
font-size:13px;
width:auto;
display:inline-block;" onclick="return confirm('Delete this record?');">Delete</button>
            </form>
        </td>
    </tr>
    <?php } ?>
<?php } else { ?>
<tr>
    <td colspan="3">No attendance available</td>
</tr>
<?php } ?>
</table>
</div>
</body>
</html>
