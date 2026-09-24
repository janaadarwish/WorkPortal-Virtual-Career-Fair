<?php
// FileName: models/student_projects.php

class student_projects {
    private $db;

    public function __construct($connection) {
        $this->db = $connection;
    }

    public function insertProject($student_id, $data) {

        $sql = "INSERT INTO student_projects

        (

            Student_ID,

            Project_Name,

            Description,

            Technologies,

            Project_URL,

            Project_File,

            Video_URL,

            Created_At

        ) 

        VALUES 

        (?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(

            $stmt,

            "issssss",

            $student_id,

            $data['project_name'],

            $data['description'],

            $data['technologies'],

            $data['project_url'],

            $data['project_file'],

            $data['video_url']
        );

        if (mysqli_stmt_execute($stmt)) {

            return mysqli_insert_id($this->db);

        }

        return false;
    }

    public function getStudentProjects($student_id){

        $sql = "SELECT *

                FROM student_projects

                WHERE Student_ID = ?

                ORDER BY Created_At DESC";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param($stmt, "i", $student_id);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $projects = [];

        while($row = mysqli_fetch_assoc($result)){

            $projects[] = $row;
        }

        return $projects;
    }

    public function deleteProject($project_id, $student_id){

        $query = "DELETE FROM student_projects

                WHERE Project_ID = ?

                AND Student_ID = ?";

        $stmt = mysqli_prepare($this->db, $query);

        mysqli_stmt_bind_param(

            $stmt,

            "ii",

            $project_id,

            $student_id
        );

        return mysqli_stmt_execute($stmt);
    }
}