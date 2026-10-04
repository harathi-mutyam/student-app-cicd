<?php
session_start();
include("../config/db.php");   // ✅ FIXED (VERY IMPORTANT)

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    Admin Dashboard
</div>

<!-- MENU -->
<div class="menu" style="text-align:center; margin-top:20px;">
    <a href="add_student.php">Add Student</a>
    <a href="manage_marks.php">Manage Marks</a>
    <a href="manage_attendance.php">Manage Attendance</a>
    <a href="logout.php">Logout</a>
</div>

<hr>

<!-- STUDENTS TABLE -->
<div class="dashboard">

<h2 style="text-align:center;">All Students</h2>

<table border="1" width="100%" style="margin-top:20px;">

<tr style="background:#0d6efd; color:white;">
    <th>ID</th>
    <th>Student ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Course</th>
    <th>Actions</th>
</tr>

<?php
$result = mysqli_query($conn, "SELECT * FROM students");

// ❌ QUERY FAIL SAFE CHECK
if(!$result){
    echo "<tr><td colspan='6'>Database Error</td></tr>";
}
else if(mysqli_num_rows($result) == 0){
    echo "<tr><td colspan='6'>No students data found</td></tr>";
}
else{

    while($row = mysqli_fetch_assoc($result)){
?>

<tr>
    <td><?php echo $row['id']; ?></td>
    <td><?php echo $row['student_id']; ?></td>

    <td>
        <a href="student_profile.php?id=<?php echo $row['id']; ?>">
            <?php echo $row['full_name']; ?>
        </a>
    </td>

    <td><?php echo $row['email']; ?></td>
    <td><?php echo $row['course']; ?></td>

    <td>
        <a href="edit_student.php?id=<?php echo $row['id']; ?>" style="color:green;">
            Edit
        </a>
        |
        <a href="delete_student.php?id=<?php echo $row['id']; ?>"
           style="color:red;"
           onclick="return confirm('Delete this student?');">
            Delete
        </a>
    </td>
</tr>

<?php
    }
}
?>

</table>

</div>

</body>
</html>
