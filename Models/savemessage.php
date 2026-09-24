<?php
require_once('../../DataBase/DataBase/db_connect.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['User_ID'])) {
    $queue_id = $_POST['queue_id'];
    $sender_id = $_SESSION['User_ID'];
    $message = $_POST['message'];

    // Securely log message into the transcript archiver
    $sql = "INSERT INTO chat_transcripts (Queue_ID, Sender_ID, Message_Text) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $queue_id, $sender_id, $message);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Logged";
    }
}
?>