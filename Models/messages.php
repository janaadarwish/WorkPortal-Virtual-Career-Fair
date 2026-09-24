<?php

class messages {

    private $db;

    public function __construct($connect) {
        $this->db = $connect;
    }

    public function sendMessage(
        $sender,
        $receiver,
        $content,
        $file_path = null
    ) {

        $sql = "INSERT INTO messages
                (
                    Sender_ID,
                    Reciever_ID,
                    Content,
                    File_Path,
                    Status,
                    Time_Stamp
                )
                VALUES
                (?, ?, ?, ?, 'Sent', NOW())";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(

            $stmt,

            "iiss",

            $sender,
            $receiver,
            $content,
            $file_path

        );

        return mysqli_stmt_execute($stmt);
    }

    public function getMessages($student_id, $recruiter_id) {

        $sql = "SELECT *
                FROM messages
                WHERE
                (Sender_ID = ? AND Reciever_ID = ?)
                OR
                (Sender_ID = ? AND Reciever_ID = ?)
                ORDER BY Time_Stamp ASC";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "iiii",
            $student_id,
            $recruiter_id,
            $recruiter_id,
            $student_id
        );

        mysqli_stmt_execute($stmt);

        return mysqli_stmt_get_result($stmt);
    }
}