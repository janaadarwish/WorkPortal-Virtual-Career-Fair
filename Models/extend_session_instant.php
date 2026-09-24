<?php
require_once('../DataBase/DataBase/db_connect.php');
session_start();

if (isset($_POST['queue_id'])) {
    $queue_id = $_POST['queue_id'];
    // Simply increment the wait time or status in the DB
    $stmt = mysqli_prepare($connect, "UPDATE queue SET Wait_Time = Wait_Time + 2 WHERE Queue_ID = ?");
    mysqli_stmt_bind_param($stmt, "i", $queue_id);
    mysqli_stmt_execute($stmt);
    echo "Success";
}
?>