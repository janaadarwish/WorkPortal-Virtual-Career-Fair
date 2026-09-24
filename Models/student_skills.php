<?php
// FileName: models/student_skills.php

class student_skills {
    private $db;
    protected int $studentID;
    protected string $skillName;

    public function __construct($connection) {
        $this->db = $connection;
    }

    // جلب كل مهارات طالب معين في مصفوفة (Array)
    public function getSkillsByStudent($student_id): array {
        $sql = "SELECT Skill_Name FROM student_skills WHERE Student_ID = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $student_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $skills = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $skills[] = $row['Skill_Name'];
        }
        return $skills;
    }

    // إضافة مهارة جديدة للطالب
    public function addSkill($student_id, $skill): bool {
        $sql = "INSERT INTO student_skills (Student_ID, Skill_Name) VALUES (?, ?)";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "is", $student_id, $skill);
        return mysqli_stmt_execute($stmt);
    }


}