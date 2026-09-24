<?php
// FileName: models/invitations.php

class invitations {
    private $db;

    public function __construct($connection) {
        $this->db = $connection;
    }

    // جلب دعوات طالب معين اللي لسه منتظرة
    public function getPendingForStudent($student_id): array {
        $sql = "SELECT i.*, c.Company_Name, j.Job_Title 
                FROM invitations i
                JOIN company c ON i.Company_ID = c.Company_ID
                JOIN job j ON i.Job_ID = j.Job_ID
                WHERE i.Student_ID = ? AND i.Status = 'Pending'";
        
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $student_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // تحديث حالة الدعوة (قبول أو رفض)
    public function updateStatus($invitation_id, $student_id, $new_status): bool {
        $sql = "UPDATE invitations SET Status = ? WHERE Invitation_ID = ? AND Student_ID = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "sii", $new_status, $invitation_id, $student_id);
        return mysqli_stmt_execute($stmt);
    }

    public function decline($invitation_id, $student_id): bool {
    // بنحدث الحالة لـ Declined وبنسجل وقت الرد
    $sql = "UPDATE invitations SET Status = 'Declined', Response_Time = NOW() 
            WHERE Invitation_ID = ? AND Student_ID = ?";
    
    $stmt = mysqli_prepare($this->db, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $invitation_id, $student_id);
    return mysqli_stmt_execute($stmt);
    }
}