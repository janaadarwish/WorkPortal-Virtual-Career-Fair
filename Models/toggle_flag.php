<?php
require_once('../DataBase/DataBase/db_connect.php');

if (isset($_POST['student_id'])) {
    $sid = $_POST['student_id'];
    
    // Efficiently toggle 0 to 1 or 1 to 0
    $query = "UPDATE waits_in SET Is_Priority = Is_Priority XOR 1 WHERE Student_ID = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "s", $sid);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Success";
    } else {
        echo "Error";
    }
}
?>