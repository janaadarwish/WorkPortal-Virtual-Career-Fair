<?php
// FileName: models/queue.php

class queue {
    private $db;
    protected int $queueID;
    protected int $jobID;
    protected int $waitTime;
    protected int $positionNo;
    protected string $status;
    protected string $boothNo;
    protected int $companyID;
    protected string $entryTime;

    public function __construct($connection, $data = null) {
        $this->db = $connection;
        if ($data) {
            $this->queueID   = $data['Queue_ID'] ?? 0;
            $this->jobID     = $data['Job_ID'] ?? 0;
            $this->waitTime  = $data['Wait_Time'] ?? 0;
            $this->positionNo = $data['.Position_No'] ?? 0; // لاحظي النقطة في اسم العمود
            $this->status     = $data['Status'] ?? 'Waiting';
            $this->boothNo    = $data['.Booth_No'] ?? '';
            $this->companyID  = $data['Company_ID'] ?? null;
            $this->entryTime  = $data['Entry_Time'] ?? '';
        }
    }

    // جلب الطابور الخاص بشركة معينة
    public function getQueueByCompany($company_id): array {
        $sql = "SELECT * FROM queue WHERE Company_ID = ? ORDER BY `.Position_No` ASC";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $company_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // تحديث حالة الطالب في الطابور
    public function updateStatus($queue_id, $new_status): bool {
        $sql = "UPDATE queue SET Status = ? WHERE Queue_ID = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "si", $new_status, $queue_id);
        return mysqli_stmt_execute($stmt);
    }

    public function createQueueEntry($student_id, $company_id, $job_id, $booth_no) {

        // نحسب رقم الدور الحالي
        $count_sql = "SELECT COUNT(*) as total
                    FROM queue
                    WHERE Job_ID = ?
                    AND Status IN ('Waiting', 'In Progress')";

        $count_stmt = mysqli_prepare($this->db, $count_sql);

        mysqli_stmt_bind_param($count_stmt, "i", $job_id);

        mysqli_stmt_execute($count_stmt);

        $count_result = mysqli_stmt_get_result($count_stmt);

        $count_data = mysqli_fetch_assoc($count_result);

        $position = $count_data['total'] + 1;

        // متوسط وقت المقابلة = 5 دقايق
        $wait_time = ($position - 1) * 5;

        // أول شخص يدخل مباشرة In Progress
        $status = ($position == 1) ? 'In Progress' : 'Waiting';
        $session_start = null;
        $session_end = null;

        if($status == 'In Progress') {

            $session_start =
            date('Y-m-d H:i:s');

            $session_end =
            date(
                'Y-m-d H:i:s',
                strtotime('+10 minutes')
            );
}

        $sql = "INSERT INTO queue
        (Student_ID, Company_ID, Job_ID, Booth_No,
        Position_No, Wait_Time, Status, Entry_Time,Session_Start, Session_End)

        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "iiiiiisss",
            $student_id,
            $company_id,
            $job_id,
            $booth_no,
            $position,
            $wait_time,
            $status,
            $session_start,
            $session_end
        );

        if (mysqli_stmt_execute($stmt)) {
            return mysqli_insert_id($this->db);
        }

        return false;
    }

    public function rebalanceQueue($job_id) {

        $sql = "SELECT Queue_ID
                FROM queue
                WHERE Job_ID = ?
                AND Status = 'Waiting'
                ORDER BY Entry_Time ASC";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param($stmt, "i", $job_id);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $position = 1;

        while ($row = mysqli_fetch_assoc($result)) {

            $wait = ($position - 1) * 5;

            $update = "UPDATE queue
                    SET Position_No = ?,
                        Wait_Time = ?
                    WHERE Queue_ID = ?";

            $up_stmt = mysqli_prepare($this->db, $update);

            mysqli_stmt_bind_param(
                $up_stmt,
                "iii",
                $position,
                $wait,
                $row['Queue_ID']
            );

            mysqli_stmt_execute($up_stmt);

            $position++;
        }
    }

    public function updateStatusToLeft($student_id): int {
    // استخدمنا JOIN في الـ UPDATE زي ما عملتي بالظبط عشان نوصل للـ Student_ID من جدول waits_in
    $sql = "UPDATE queue q 
            JOIN waits_in w ON q.Queue_ID = w.Queue_ID 
            SET q.Status = 'Finished' 
            WHERE w.Student_ID = ? AND q.Status = 'Waiting'";
    
    $stmt = mysqli_prepare($this->db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    
    // بنرجع عدد الصفوف اللي اتأثرت عشان الـ Controller يعرف هل هو كان في طابور أصلاً ولا لأ
    return mysqli_stmt_affected_rows($stmt);
    }

    public function startInterviewSession($student_id) {
    // 1. كويري معقدة شوية عشان نجيب اسم الشركة ورقم الطابور للطالب
    $sql = "SELECT q.Queue_ID, c.Company_Name 
            FROM queue q 
            JOIN waits_in w ON q.Queue_ID = w.Queue_ID 
            JOIN company c ON q.Company_ID = c.Company_ID 
            WHERE w.Student_ID = ? AND q.Status = 'Waiting' 
            LIMIT 1";
            
    $stmt = mysqli_prepare($this->db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if ($data) {
        // 2. تحديث الحالة لـ In Progress
        $update_sql = "UPDATE queue SET Status = 'In Progress' WHERE Queue_ID = ?";
        $up_stmt = mysqli_prepare($this->db, $update_sql);
        mysqli_stmt_bind_param($up_stmt, "i", $data['Queue_ID']);
        
        if (mysqli_stmt_execute($up_stmt)) {
            return $data; // بنرجع اسم الشركة ورقم الـ Queue
        }
    }
    return null;
    }

    public function archiveActiveSession($student_id): bool {
    // تحديث حالة الطابور من 'In Progress' إلى 'Finished' بناءً على الطالب
    $sql = "UPDATE queue q 
            JOIN waits_in w ON q.Queue_ID = w.Queue_ID 
            SET q.Status = 'Finished' 
            WHERE w.Student_ID = ? AND q.Status = 'In Progress'";
    
    $stmt = mysqli_prepare($this->db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    return mysqli_stmt_execute($stmt);
    }
    
    public function finalizeSession($student_id): bool {
    // بنحدث حالة الطابور للطالب ده تحديداً لما يخلص المقابلة
    $sql = "UPDATE queue q 
            JOIN waits_in w ON q.Queue_ID = w.Queue_ID 
            SET q.Status = 'Finished' 
            WHERE w.Student_ID = ? AND q.Status = 'In Progress'";
    
    $stmt = mysqli_prepare($this->db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    return mysqli_stmt_execute($stmt);
    }




}