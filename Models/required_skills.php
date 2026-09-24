<?php
// FileName: models/required_skills.php

class required_skills {
    private $db;
    protected int $jobID;
    protected string $skillName;

    public function __construct($connection) {
        $this->db = $connection;
    }

    /**
     * جلب كل المهارات المطلوبة لوظيفة معينة
     * ترجع مصفوفة (Array) بأسماء المهارات فقط
     */
    public function getSkillsByJob($job_id): array {
        $sql = "SELECT Skill_Name FROM required_skills WHERE Job_ID = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $job_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $skills = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $skills[] = $row['Skill_Name'];
        }
        return $skills;
    }

    /**
     * إضافة مهارة مطلوبة لوظيفة معينة
     */
    public function addRequiredSkill($job_id, $skill_name): bool {
        $sql = "INSERT INTO required_skills (Job_ID, Skill_Name) VALUES (?, ?)";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "is", $job_id, $skill_name);
        return mysqli_stmt_execute($stmt);
    }

    /**
     * حذف مهارة معينة من وظيفة
     */
    public function deleteRequiredSkill($job_id, $skill_name): bool {
        $sql = "DELETE FROM required_skills WHERE Job_ID = ? AND Skill_Name = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "is", $job_id, $skill_name);
        return mysqli_stmt_execute($stmt);
    }
}