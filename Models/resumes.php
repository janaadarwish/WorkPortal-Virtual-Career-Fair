<?php

class resume {

    private $db;

    public function __construct($connection) {

        $this->db = $connection;

    }

    // رفع Resume جديدة
    public function addResume($student_id, $label, $file_url) {

        $query = "INSERT INTO resumes

        (Student_ID, Resume_Label, File_URL)

        VALUES

        (?, ?, ?)";

        $stmt = mysqli_prepare($this->db, $query);

        mysqli_stmt_bind_param(

            $stmt,

            "iss",

            $student_id,

            $label,

            $file_url
        );

        return mysqli_stmt_execute($stmt);
    }

    // كل Resumes الطالب
    public function getStudentResumes($student_id) {

        $query = "SELECT *

                  FROM resumes

                  WHERE Student_ID = ?

                  ORDER BY Created_At DESC";

        $stmt = mysqli_prepare($this->db, $query);

        mysqli_stmt_bind_param($stmt, "i", $student_id);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $resumes = [];

        while($row = mysqli_fetch_assoc($result)){

            $resumes[] = $row;

        }

        return $resumes;
    }

    // Resume واحدة
    public function getResumeById($resume_id) {

        $query = "SELECT *

                  FROM resumes

                  WHERE Resume_ID = ?";

        $stmt = mysqli_prepare($this->db, $query);

        mysqli_stmt_bind_param($stmt, "i", $resume_id);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_assoc($result);
    }
}
?>