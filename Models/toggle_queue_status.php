<?php
session_start();
require_once('../DataBase/DataBase/db_connect.php');

if (isset($_POST['status']) && isset($_SESSION['User_ID'])) {
    $status = $_POST['status'];
    $uid = $_SESSION['User_ID'];
    
    $stmt = mysqli_prepare($connect, "UPDATE recruiter SET Queue_Status = ? WHERE Recruiter_ID = ?");
    mysqli_stmt_bind_param($stmt, "ss", $status, $uid);
    mysqli_stmt_execute($stmt);
}