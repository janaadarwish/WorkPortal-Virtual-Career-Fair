<?php
session_start();
require_once('../DataBase/DataBase/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['User_ID'])) {
    
    $recruiter_id = $_SESSION['User_ID'];
    // Sanitize inputs
    $student_id   = intval($_POST['student_id']);
    $queue_id     = intval($_POST['queue_id']); 
    $rating       = intval($_POST['rating']);
    $comm         = intval($_POST['comm_score']);
    $tech         = intval($_POST['tech_score']);
    $culture      = intval($_POST['culture_score']);
    $notes        = mysqli_real_escape_string($connect, $_POST['notes']);

    if ($student_id <= 0 || $queue_id <= 0) {
        die("Error: Invalid Student or Queue ID.");
    }

    // Start Transaction to ensure all or nothing
    mysqli_begin_transaction($connect);

    try {
        // 1. DELETE this specific student from the waiting list
        // This removes them from the sidebar but keeps the Queue active for others
        $stmt2 = mysqli_prepare($connect, "DELETE FROM waits_in WHERE Student_ID = ? AND Queue_ID = ?");
        mysqli_stmt_bind_param($stmt2, "ii", $student_id, $queue_id);
        mysqli_stmt_execute($stmt2);

        // 2. Record the evaluation data
        $stmt3 = mysqli_prepare($connect, "INSERT INTO evaluations 
                        (Recruiter_ID, Student_ID, Rating, score_communication, score_technical, score_culture, Notes) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt3, "iiiiiis", $recruiter_id, $student_id, $rating, $comm, $tech, $culture, $notes);
        mysqli_stmt_execute($stmt3);

        // 3. OPTIONAL: Only update queue status if you want to close the booth entirely.
        // For now, I recommend commenting out the 'UPDATE queue' line.

        mysqli_commit($connect);
        echo "Success";

    } catch (Exception $e) {
        mysqli_rollback($connect);
        echo "Error: " . $e->getMessage();
    }
}
?>