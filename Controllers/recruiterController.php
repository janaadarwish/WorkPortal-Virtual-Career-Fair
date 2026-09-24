<?php
session_start();
require_once('../../DataBase/DataBase/db_connect.php');
global $connect;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $recruiter_id = $_SESSION['User_ID'];

    // --- 1. ميزة الـ Invite (زيادة العداد) ---
    if ($action === 'fast_pass') {
        $student_id = $_POST['student_id'];

        // ثابت على Queue_ID = 28 عشان Booth_No = 1
        $queue_id = 28;

        $sql = "REPLACE INTO waits_in (Student_ID, Queue_ID, Is_Priority) 
                VALUES ('$student_id', '$queue_id', 1)";

        if (mysqli_query($connect, $sql)) {
            echo "success";
        } else {
            echo "db_error: " . mysqli_error($connect);
        }
    }

    // --- 2. ميزة التقييم (نقص العداد) ---
    if ($action === 'submit_eval') {
        $student_id = $_POST['student_id'];
        $notes = mysqli_real_escape_string($connect, $_POST['notes']);

        $sql_eval = "INSERT INTO interview (Recruiter_ID, Student_ID, Feedback_Tags) 
                     VALUES ('$recruiter_id', '$student_id', '$notes')";

        if (mysqli_query($connect, $sql_eval)) {
            mysqli_query($connect, "DELETE FROM waits_in WHERE Student_ID = '$student_id'");
            echo "success";
        } else {
            echo "error";
        }
    }

    // --- 3. ميزة الأرشفة ---
    if ($action === 'archive_transcript') {
        $queue_id = $_POST['queue_id'];
        $message = mysqli_real_escape_string($connect, $_POST['message']);

        $sql_archive = "UPDATE queue 
                        SET Chat_Transcript = CONCAT(IFNULL(Chat_Transcript,''), '\n[Recruiter Note]: ', '$message') 
                        WHERE Queue_ID = '$queue_id'";

        if (mysqli_query($connect, $sql_archive)) {
            echo "success";
        } else {
            echo "error";
        }
    }
}