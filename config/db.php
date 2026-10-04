<?php

$conn = mysqli_connect(
    "mysql",
    "root",
    "root",
    "student_management"
);

if(!$conn){
    die("Connection Failed");
}

?>