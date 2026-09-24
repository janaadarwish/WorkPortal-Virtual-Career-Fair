<?php
// FileName: models/waits_in.php

class waits_in {
    private $db;

    public function __construct($connection) {
        $this->db = $connection;
    }

    // التأكد إذا كان الطالب مسجل في طابور حالي
    public function isStudentWaiting($student_id): bool {
        $sql = "SELECT w.Queue_ID FROM waits_in w 
                JOIN queue q ON w.Queue_ID = q.Queue_ID 
                WHERE w.Student_ID = ? AND q.Status = 'Waiting'";
        
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $student_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_num_rows($result) > 0;
    }

    // ربط الطالب بالطابور
    public function linkStudentToQueue($queue_id, $student_id): bool {
        $sql = "INSERT INTO waits_in (Queue_ID, Student_ID) VALUES (?, ?)";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $queue_id, $student_id);
        return mysqli_stmt_execute($stmt);
    }

    public function attachResumeToBooth($student_id, $queue_id, $resume_id)
    {
        $sql = "UPDATE waits_in
                SET Selected_Resume_ID = ?
                WHERE Student_ID = ?
                AND Queue_ID = ?";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "iii",
            $resume_id,
            $student_id,
            $queue_id
        );

        return mysqli_stmt_execute($stmt);
    }
}