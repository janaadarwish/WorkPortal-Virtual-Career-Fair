<?php
// FileName: models/student.php
require_once 'user.php';

class student extends user {
    private $db;
    protected string $major;
    protected string $resumeUrl;
    protected float $gpa;
    protected int $classYear;

    public function __construct($connection, $data) {
        // بننادي الـ constructor بتاع الأب (User) أولاً
        parent::__construct($data); 
        $this->db = $connection;
        
        // بيانات الطالب الخاصة من جدول student
        $this->major     = $data['Major'] ?? '';
        $this->resumeUrl = $data['Resume_URL'] ?? '';
        $this->gpa       = (float)($data['GPA'] ?? 0.0);
        $this->classYear = (int)($data['Class_Year'] ?? 0);
    }

    // تطبيق الـ Abstract Method اللي كانت في كلاس الأب
    public function getDashboardUrl(): string {
        return "pages/student/S_dashboard.php";
    }


    public function getMajor(): string { return $this->major; }


    // 1. ميثود لتحديث الحالة
    public function updateVisibility($student_id, $visibility): bool {

        $sql = "UPDATE student

                SET Profile_Visibility = ?

                WHERE Student_ID = ?";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(

            $stmt,

            "si",

            $visibility,

            $student_id
        );

        return mysqli_stmt_execute($stmt);
    }

    // 2. ميثود لجلب الحالة الحالية
    public function getVisibility($student_id): ?string {
        $sql = "SELECT Profile_Visibility FROM student WHERE Student_ID = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $student_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return $row['Profile_Visibility'] ?? null;
    }

    public function updateResumePath($student_id, $new_path): bool {
        $sql = "UPDATE student SET Resume_URL = ? WHERE Student_ID = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "si", $new_path, $student_id);
        return mysqli_stmt_execute($stmt);
    }

    public function updateStatus($student_id, $status): bool {
    // التأكد من القيم المسموحة حسب الـ SQL Enum
    $valid_statuses = ['Available', 'In-Chat', 'Offline'];
    if (!in_array($status, $valid_statuses)) {
        return false;
    }

    $sql = "UPDATE student SET Ready_Status = ? WHERE Student_ID = ?";
    $stmt = mysqli_prepare($this->db, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $student_id);
    return mysqli_stmt_execute($stmt);
    }

    public function getStatus($student_id): ?string {

        $sql = "SELECT Ready_Status

                FROM student

                WHERE Student_ID = ?";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(

            $stmt,

            "i",

            $student_id
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $row = mysqli_fetch_assoc($result);

        return $row['Ready_Status'] ?? null;
    }

    public function getProfileData($student_id): ?array {
    // كويري بتجيب البيانات من الجدولين مع بعض
    $sql = "SELECT u.F_Name, u.L_Name, u.Email, s.Major, s.GPA, s.Bio, s.Resume_URL 
            FROM user u 
            LEFT JOIN student s ON u.User_ID = s.Student_ID 
            WHERE u.User_ID = ?";
    
    $stmt = mysqli_prepare($this->db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // لو الـ Major بـ NULL ده معناه إن الطالب ملوش سجل في جدول student
        // بنعمل الـ Fallback هنا يدوي وبكل نظافة
        return [
            'F_Name' => $row['F_Name'] ?? '',
            'L_Name' => $row['L_Name'] ?? '',
            'Email' => $row['Email'] ?? '',
            'Major' => $row['Major'] ?? 'Not Specified',
            'GPA' => $row['GPA'] ?? 0.0,
            'Bio' => $row['Bio'] ?? '',
            'Resume_URL' => $row['Resume_URL'] ?? ''
        ];
    }
    return null;
    }

    public function updateFullProfile($student_id, $data): bool {
    // 1. نبدأ الـ Transaction
    mysqli_begin_transaction($this->db);

    try{
        // 2. تحديث جدول الـ user
        $user_sql = "UPDATE user SET F_Name = ?, L_Name = ? WHERE User_ID = ?";
        $stmt1 = mysqli_prepare($this->db, $user_sql);
        mysqli_stmt_bind_param($stmt1, "ssi", $data['first_name'], $data['last_name'], $student_id);
        mysqli_stmt_execute($stmt1);

        // 3. تحديث جدول الـ student
        $student_sql = "UPDATE student SET Major = ?, GPA = ?, Class_Year = ? WHERE Student_ID = ?";
        $stmt2 = mysqli_prepare($this->db, $student_sql);

        mysqli_stmt_bind_param($stmt2, "sdii", $data['major'], $data['gpa'], $data['class_year'], $student_id);


        mysqli_stmt_execute($stmt2);

                // 4. لو كله تمام، ثبت التغييرات
                mysqli_commit($this->db);
                return true;

            } catch (Exception $e) {
                // 5. لو حصل أي غلط، ارجع في كلامك كأن شيئاً لم يكن
                mysqli_rollback($this->db);
                return false;
            }
    }
}





