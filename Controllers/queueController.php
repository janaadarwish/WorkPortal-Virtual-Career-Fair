<?php
session_start();
// FileName: Controllers/queueController.php

require_once __DIR__ . '/../DataBase/DataBase/db_connect.php';
require_once __DIR__ . '/../Models/queue.php';
require_once __DIR__ . '/../Models/waits_in.php';
require_once __DIR__ . '/../Models/participates_in.php';

class queueController {
    private $db;

    public function __construct($connection) {
        $this->db = $connection;
    }

    public function joinQueue($student_id, $company_id, $job_id, $booth_no) {
        $queueModel = new queue($this->db);
        $waitsInModel = new waits_in($this->db);

        // 1. تشيك: هل هو في طابور أصلاً؟
        if ($waitsInModel->isStudentWaiting($student_id)) {
            return ['success' => false, 'message' => 'You are already in a queue'];
        }

        // 2. إنشاء السجل في جدول queue
        $new_id = $queueModel->createQueueEntry(
            $student_id,
            $company_id,
            $job_id,
            $booth_no
        );
        
        if ($new_id) {
            // 3. ربطه في جدول waits_in
            if ($waitsInModel->linkStudentToQueue($new_id, $student_id)) {
                return ['success' => true, 'queue_id' => $new_id];
            }
        }

        return ['success' => false, 'message' => 'System Error. Please try again.'];
    }

    public function leaveCurrentQueue($student_id) {
    $queueModel = new queue($this->db);
    
    $affectedRows = $queueModel->updateStatusToLeft($student_id);

    if ($affectedRows > 0) {
        return [
            'success' => true,
            'message' => 'Successfully left the queue.'
        ];
    }

    return [
        'success' => false,
        'message' => 'You are not currently in any waiting queue.'
    ];
    }

    public function enterRoom($student_id) {
    $queueModel = new queue($this->db);
    $interviewData = $queueModel->startInterviewSession($student_id);

    if ($interviewData) {
        return [
            'success' => true,
            'company_name' => $interviewData['Company_Name'],
            'message' => 'تم دخول غرفة المقابلة مع شركة ' . $interviewData['Company_Name']
        ];
    }

    return [
        'success' => false,
        'message' => 'عذراً، لم يتم العثور على دورك في الانتظار حالياً.'
    ];
    }

    public function endSession($student_id) {
    $queueModel = new queue($this->db);
    
    if ($queueModel->archiveActiveSession($student_id)) {
        return [
            'success' => true,
            'message' => 'تم إنهاء الجلسة وأرشفتها بنجاح.'
        ];
    }

    return [
        'success' => false,
        'message' => 'عذراً، لم يتم العثور على جلسة نشطة لإنهائها.'
    ];
    }

    public function endInterview($student_id) {
    $queueModel = new queue($this->db);
    $studentModel = new student($this->db, []);

    // 1. إنهاء جلسة الطابور
    if ($queueModel->finalizeSession($student_id)) {
        
        // 2. تحديث حالة الطالب ليكون متاحاً لمقابلات أخرى
        $studentModel->updateStatus($student_id, 'Available');
        
        return [
            'success' => true, 
            'message' => 'انتهت المقابلة بنجاح، أنت متاح الآن لمقابلات أخرى.'
        ];
    }

    return [
        'success' => false, 
        'message' => 'لم يتم العثور على مقابلة نشطة لإنهاؤها.'
    ];
    }


    public function handleJoinRequest($connect) {

        if (isset($_GET['action'])) {

            if (session_status() === PHP_SESSION_NONE) {

                session_start();

            }

            $student_id = $_SESSION['User_ID'];

            $action = $_GET['action'];

            // =========================
            // JOIN QUEUE
            // =========================

            if ($action == 'join') {

                $company_id = $_GET['company_id'];

                $job_id = $_GET['job_id'];

                $participatesModel =
                new participates_in($this->db);

                $boothData =
                $participatesModel
                ->getBoothNumberByCompany($company_id);

                if (!$boothData) {

                    $_SESSION['error_msg'] =
                    "Booth not found.";

                    header("Location: ../Views/student/S_studentDashboard.php");

                    exit();

                }

                $booth_no = $boothData['Booth_No'];

                $check_sql = "SELECT Queue_ID
                FROM queue
                WHERE Student_ID = ?
                AND Status IN ('Waiting', 'In Progress')";

                $check_stmt = mysqli_prepare($this->db, $check_sql);

                mysqli_stmt_bind_param($check_stmt, "i", $student_id);

                mysqli_stmt_execute($check_stmt);

                $check_result = mysqli_stmt_get_result($check_stmt);

                if (mysqli_num_rows($check_result) > 0) {

                    $_SESSION['error_msg'] =
                    "You are already in a queue.";

                    header("Location: ../Views/student/S_studentDashboard.php");

                    exit();
                }

                $result = $this->joinQueue(

                    $student_id,
                    $company_id,
                    $job_id,
                    $booth_no

                );

            }

            // =========================
            // LEAVE QUEUE
            // =========================

            elseif ($action == 'leave') {

                $result =
                $this->leaveCurrentQueue($student_id);

            }

            // =========================
            // ATTACH RESUME
            // =========================

            elseif ($action == 'attach_resume') {

                $result = $this->attachResume(

                    $_SESSION['User_ID'],

                    $_GET['queue_id'],

                    $_GET['resume_id']

                );

                if ($result['success']) {

                    $_SESSION['success_msg'] =
                    $result['message'];

                }

                else {

                    $_SESSION['error_msg'] =
                    $result['message'];

                }

                header("Location: ../Views/student/S_profile.php");

                exit();

            }

            else {

                $result = [

                    'success' => false,

                    'message' => 'Invalid action.'

                ];

            }

            // =========================
            // REDIRECT
            // =========================

            if ($result['success']) {

                $_SESSION['success_msg'] =
                $result['message'];

            }

            else {

                $_SESSION['error_msg'] =
                $result['message'];

            }

            header("Location: ../Views/student/S_studentDashboard.php");

            exit();

        }

    }

    public function attachResume($student_id, $queue_id, $resume_id)
    {
        require_once "../Models/waits_in.php";

        $waitsModel = new waits_in($this->db);

        if ($waitsModel->attachResumeToBooth(
            $student_id,
            $queue_id,
            $resume_id
        )) {

            $_SESSION['success_msg'] =
                "Resume attached to booth successfully.";

        } else {

            $_SESSION['error_msg'] =
                "Failed to attach resume.";
        }

        header("Location: ../Views/student/S_profile.php");
        exit();
    }
    


}

if (isset($connect)) {
    // 2. عمل نسخة من الكنترولر وتمرير الاتصال له
    $controller = new queueController($connect);
    
    // 3. تشغيل المعالج للأكشنز (Join/Leave)
    $controller->handleJoinRequest($connect);
} else {
    // ده عشان لو ملف db_connect مجاش صح تعرفي السبب
    die("Database connection failed. Check your db_connect.php file.");
}

