<?php
// FileName: models/participates_in.php

class participates_in {
    private $db;

    public function __construct($connection) {
        $this->db = $connection;
    }

    public function getRecommendations($major, $skills = []) {

        $baseQuery = "SELECT DISTINCT 
                        p.Booth_No,
                        c.Company_Name,
                        c.Location,
                        j.Job_Title,
                        j.Department
                    FROM participates_in p
                    JOIN company c ON p.Company_ID = c.Company_ID
                    JOIN job j ON j.Company_ID = p.Company_ID
                    LEFT JOIN required_skills rs ON j.Job_ID = rs.Job_ID
                    WHERE 1";

        if (empty($skills)) {

            $sql = $baseQuery . " AND j.Department = ?";

            $stmt = mysqli_prepare($this->db, $sql);

            mysqli_stmt_bind_param($stmt, "s", $major);

        } else {

            $placeholders = implode(',', array_fill(0, count($skills), '?'));

            $sql = $baseQuery . "
                AND (
                    j.Department = ?
                    OR rs.Skill_Name IN ($placeholders)
                )";

            $stmt = mysqli_prepare($this->db, $sql);

            $types = "s" . str_repeat("s", count($skills));

            mysqli_stmt_bind_param($stmt, $types, $major, ...$skills);
        }

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }


    public function getBoothNumberByCompany($company_id){

        $sql = "SELECT Booth_No
                FROM participates_in
                WHERE Company_ID = ?
                LIMIT 1";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param($stmt, "i", $company_id);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_assoc($result);
    }
}