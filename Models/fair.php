<?php
// FileName: models/fair.php

class fair {
    private $db;
    protected int $fairID;
    protected string $fairTitle;
    protected string $status;
    protected string $startDate;
    protected string $endDate;
    protected int $approvedByAdmin;

    public function __construct($connection, $data = null) {
        $this->db = $connection;
        if ($data) {
            $this->fairID          = $data['Fair_ID'] ?? 0;
            $this->fairTitle       = $data['Fair_Title'] ?? '';
            $this->status          = $data['Status'] ?? '';
            $this->startDate       = $data['Start_Date'] ?? '';
            $this->endDate         = $data['End_Date'] ?? '';
            $this->approvedByAdmin = $data['Approved_By_Admin'] ?? 0;
        }
    }

    // جلب المعارض المتاحة حالياً
    public function getActiveFairs(): array {
        $sql = "SELECT * FROM fair WHERE Status = 'Active' AND End_Date > NOW()";
        $result = mysqli_query($this->db, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function insertFair($title, $start, $end, $admin_id) {
    $sql = "INSERT INTO fair (Fair_Title, Status, Start_Date, End_Date, Approved_By_Admin) 
            VALUES (?, 'Pending', ?, ?, ?)";
    
    $stmt = mysqli_prepare($this->db, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $title, $start, $end, $admin_id);
    
    if (mysqli_stmt_execute($stmt)) {
        return mysqli_insert_id($this->db);
    }
    return false;
    }

    public function getTrafficData($fair_id): array {
    // الكويري اللي إنتي كاتباها ممتازة وبتعمل JOIN صح بين المشاركين والشركات والطابور
    $sql = "SELECT p.Booth_No, c.Company_Name, COUNT(q.Queue_ID) as waiting_count
            FROM participates_in p
            JOIN company c ON p.Company_ID = c.Company_ID
            LEFT JOIN queue q ON p.Company_ID = q.Company_ID AND q.Status = 'Waiting'
            WHERE p.Fair_ID = ?
            GROUP BY p.Booth_No, c.Company_Name";
    
    $stmt = mysqli_prepare($this->db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $fair_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $traffic_data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // إضافة الـ Logic بتاع تصنيف الزحمة
        $row['traffic_level'] = $row['waiting_count'] > 5 ? 'Hot' : 'Normal';
        $traffic_data[] = $row;
    }
    return $traffic_data;
    }


    public function getFairPerformanceReport($fair_id): array {
    // 1. حساب إجمالي المقابلات في هذا المعرض
    $sql1 = "SELECT COUNT(*) as interview_count FROM interview WHERE Student_ID IN (
                SELECT w.Student_ID FROM waits_in w 
                JOIN queue q ON w.Queue_ID = q.Queue_ID 
                JOIN participates_in p ON q.Company_ID = p.Company_ID 
                WHERE p.Fair_ID = ?)";
    
    $stmt1 = mysqli_prepare($this->db, $sql1);
    mysqli_stmt_bind_param($stmt1, "i", $fair_id);
    mysqli_stmt_execute($stmt1);
    $res1 = mysqli_stmt_get_result($stmt1);
    $count1 = mysqli_fetch_assoc($res1)['interview_count'] ?? 0;

    // 2. حساب عدد الطلاب اللي خلصوا (Finished) فعلاً
    $sql2 = "SELECT COUNT(DISTINCT w.Student_ID) as finished_students 
             FROM waits_in w 
             JOIN queue q ON w.Queue_ID = q.Queue_ID 
             JOIN participates_in p ON q.Company_ID = p.Company_ID 
             WHERE p.Fair_ID = ? AND q.Status = 'Finished'";
    
    $stmt2 = mysqli_prepare($this->db, $sql2);
    mysqli_stmt_bind_param($stmt2, "i", $fair_id);
    mysqli_stmt_execute($stmt2);
    $res2 = mysqli_stmt_get_result($stmt2);
    $count2 = mysqli_fetch_assoc($res2)['finished_students'] ?? 0;

    return [
        'total_interviews' => $count1,
        'unique_finished_students' => $count2
    ];
    }

    public function getLocalizedTimes($fair_id, $timezone): ?array {
    $sql = "SELECT Start_Date, End_Date FROM fair WHERE Fair_ID = ?";
    $stmt = mysqli_prepare($this->db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $fair_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        try {
            // استخدام الـ DateTime لتحويل التوقيت بمرونة
            $start_dt = new DateTime($row['Start_Date']);
            $end_dt = new DateTime($row['End_Date']);
            
            $user_timezone = new DateTimeZone($timezone);
            $start_dt->setTimezone($user_timezone);
            $end_dt->setTimezone($user_timezone);
            
            return [
                'start_date' => $start_dt->format('Y-m-d H:i:s'),
                'end_date' => $end_dt->format('Y-m-d H:i:s'),
                'timezone' => $timezone
            ];
        } catch (Exception $e) {
            return null; // خطأ في الـ timezone
        }
    }
    return null; // المعرض غير موجود
    }


    public function getFairParticipantEmails($fair_id): array {
    // الكويري بتاعتك ممتازة لأنها بتجيب الطلاب اللي ليهم علاقة بالمعرض ده حصراً
    $sql = "SELECT DISTINCT u.Email 
            FROM user u 
            JOIN student s ON u.User_ID = s.Student_ID 
            JOIN waits_in w ON s.Student_ID = w.Student_ID 
            JOIN queue q ON w.Queue_ID = q.Queue_ID 
            JOIN participates_in p ON q.Company_ID = p.Company_ID 
            WHERE p.Fair_ID = ?";
    
    $stmt = mysqli_prepare($this->db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $fair_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $emails = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $emails[] = $row['Email'];
    }
    return $emails;
    }


}