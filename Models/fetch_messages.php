<?php
session_start();
require_once('../DataBase/DataBase/db_connect.php');

// Security check: ensure user is logged in
if (!isset($_SESSION['User_ID'])) {
    echo json_encode([]);
    exit();
}

$current_user = $_SESSION['User_ID'];
// Note: Using 'Reciever_ID' to match your database schema
$recruiter_id = mysqli_real_escape_string($connect, $_GET['receiver_id']);

// 1. Fixed "Receiver_ID" to "Reciever_ID" (spelled with 'ie' in your SQL)
// 2. Fixed "timestamp" to "Time_Stamp" (matching your table structure)
$sql = "SELECT * FROM messages 
        WHERE (Sender_ID = '$current_user' AND Reciever_ID = '$recruiter_id')
        OR (Sender_ID = '$recruiter_id' AND Reciever_ID = '$current_user')
        ORDER BY Time_Stamp ASC";

$result = mysqli_query($connect, $sql);

if (!$result) {
    // Log error if query fails for debugging
    error_log("Database Error: " . mysqli_error($connect));
    echo json_encode([]);
    exit();
}

$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}

header('Content-Type: application/json');
echo json_encode($messages);