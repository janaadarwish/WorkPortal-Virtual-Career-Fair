<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../Models/student.php';
require_once __DIR__ . '/../Models/resumes.php';
require_once __DIR__ . '/../DataBase/DataBase/db_connect.php';
require_once __DIR__ . '/../Models/student_skills.php';
require_once __DIR__ . '/../Models/required_skills.php';
require_once __DIR__ . '/../Models/student_projects.php';
require_once __DIR__ . '/../Models/participates_in.php';
require_once __DIR__ . '/../Models/invitations.php';

class studentController {
    private $db;

    public function __construct($connection) {
        $this->db = $connection;
    }

    public function getResumeInfo($student_id) {

        $query = "SELECT * FROM resumes
                WHERE Student_ID = '$student_id'
                ORDER BY Created_At DESC";

        $result = mysqli_query($this->db, $query);

        $resumes = [];

        while($row = mysqli_fetch_assoc($result)){

            $resumes[] = [

                'resume_id' => $row['Resume_ID'],

                'resume_label' => $row['Resume_Label'],

                'resume_file' => basename($row['File_URL']),

                'original_path' => $row['File_URL'],

                'created_at' => $row['Created_At']

            ];
        }

        return $resumes;
    }


    public function setProfilePrivacy() {

        if(session_status() === PHP_SESSION_NONE){

            session_start();

        }

        $student_id = $_SESSION['User_ID'];

        $visibility = $_POST['visibility'];

        $studentModel = new student($this->db, []);

        if(
            $studentModel->updateVisibility(
                $student_id,
                $visibility
            )
        ){

            $_SESSION['privacy_success'] =
            "Profile visibility updated successfully!";

        }else{

            $_SESSION['privacy_error'] =
            "Failed to update privacy settings.";
        }

        header("Location: /workportal/Views/student/S_profile.php");

        exit();
    }


    public function analyzeSkillGap($student_id, $job_id): array {

        $studentSkillsModel = new student_skills($this->db);

        $requiredSkillsModel = new required_skills($this->db);

        // مهارات الطالب
        $studentSkills =
            $studentSkillsModel
            ->getSkillsByStudent($student_id);

        // مهارات الوظيفة
        $requiredSkills =
            $requiredSkillsModel
            ->getSkillsByJob($job_id);

        // نخليهم lowercase للمقارنة
        $studentSkills =
            array_map('strtolower', $studentSkills);

        $requiredSkills =
            array_map('strtolower', $requiredSkills);

        // المهارات الناقصة
        $missingSkills =
            array_diff(
                $requiredSkills,
                $studentSkills
            );

        return [

            'has_all_skills' =>
                empty($missingSkills),

            'missing_count' =>
                count($missingSkills),

            'missing_list' =>
                array_values($missingSkills)

        ];
    }

    public function handleResumeUpload($student_id, $file, $resume_label) {

        // 1. التحقق من أخطاء الرفع
        if ($file['error'] !== UPLOAD_ERR_OK) {

            return [
                'success' => false,
                'message' => 'File upload error'
            ];
        }

        // 2. التحقق من الامتداد
        $extension = strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        if ($extension !== 'pdf') {

            return [
                'success' => false,
                'message' => 'Only PDF files are allowed'
            ];
        }

        // 3. التحقق من الحجم
        if ($file['size'] > 5 * 1024 * 1024) {

            return [
                'success' => false,
                'message' => 'File size must be less than 5MB'
            ];
        }

        // 4. مسار الحفظ
        $upload_dir = '../../uploads/resumes/';

        if (!file_exists($upload_dir)) {

            mkdir($upload_dir, 0777, true);

        }

        // 5. اسم جديد للملف
        $filename =
            'student_' .
            $student_id .
            '_' .
            time() .
            '.pdf';

        $target_file = $upload_dir . $filename;

        // 6. رفع الملف
        if (move_uploaded_file($file['tmp_name'], $target_file)) {

            // 7. حفظه في جدول resumes
            $query = "INSERT INTO resumes

            (Student_ID, Resume_Label, File_URL)

            VALUES

            ('$student_id', '$resume_label', '$filename')";

            $result = mysqli_query($this->db, $query);

            if ($result) {

                return [

                    'success' => true,

                    'filename' => $filename

                ];

            } else {

                unlink($target_file);

                return [

                    'success' => false,

                    'message' => 'Database insert failed'

                ];
            }
        }

        return [

            'success' => false,

            'message' => 'File move failed'

        ];
    }



    public function getRecommendedBooths($student_id) {

        $studentModel = new student($this->db, []);

        $skillsModel = new student_skills($this->db);

        $boothModel = new participates_in($this->db);

        // بيانات الطالب
        $studentData =
        $studentModel->getProfileData($student_id);

        if (
            !$studentData ||
            empty($studentData['Major'])
        ) {

            return [

                'success' => false,

                'message' =>
                'Student major not found'
            ];
        }

        // المهارات
        $skills =
        $skillsModel->getSkillsByStudent($student_id);

        // التوصيات
        $booths =
        $boothModel->getRecommendations(

            $studentData['Major'],

            $skills
        );

        return [

            'success' => true,

            'count' => count($booths),

            'booths' => $booths
        ];
    }


    public function changeReadyStatus($student_id, $status) {
        $studentModel = new student($this->db, []);
        
        if ($studentModel->updateStatus($student_id, $status)) {
            return [
                'success' => true,
                'new_status' => $status,
                'message' => 'Your status is now ' . $status
            ];
        }

        return [
            'success' => false,
            'message' => 'Could not update status. Please try again.'
        ];
    }



    public function addNewSkill($student_id, $post_data) {

        $skillsModel = new student_skills($this->db);

        // تنظيف اسم المهارة
        $skill_name = trim($post_data['skill_name']);

        // المهارات الحالية
        $currentSkills =
            array_map(
                'strtolower',
                $skillsModel->getSkillsByStudent($student_id)
            );

        // منع التكرار
        if (
            in_array(
                strtolower($skill_name),
                $currentSkills
            )
        ) {

            return [

                'success' => false,

                'message' =>
                'You already have this skill listed.'
            ];
        }

        // إضافة المهارة
        if (
            $skillsModel->addSkill(
                $student_id,
                $skill_name
            )
        ) {

            return [

                'success' => true,

                'message' =>
                'Skill added successfully.'
            ];
        }

        return [

            'success' => false,

            'message' =>
            'An error occurred while adding the skill.'
        ];
    }



    public function sendChat($student_id, $message) {
        // 1. هنجيب رقم المقابلة الحالية من جدول الـ interview والـ queue
        $sql = "SELECT i.Interview_ID 
                FROM interview i
                JOIN queue q ON i.Job_ID = q.Job_ID AND i.Company_ID = q.Company_ID
                JOIN waits_in w ON q.Queue_ID = w.Queue_ID
                WHERE w.Student_ID = ? AND q.Status = 'In Progress' 
                ORDER BY i.Interview_Date DESC LIMIT 1";

        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $student_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $interview = mysqli_fetch_assoc($result);

        if ($interview) {
            $chatModel = new chat_messages($this->db);
            if ($chatModel->saveMessage($interview['Interview_ID'], 'Student', $message)) {
                return ['success' => true, 'message' => 'Message sent!'];
            }
        }

        return ['success' => false, 'message' => 'No active interview found to chat.'];
    }


    public function acceptInvitation($student_id, $invitation_id) {
        $invitationModel = new invitations($this->db);
        
        // 1. تحديث حالة الدعوة لـ Accepted
        if ($invitationModel->updateStatus($invitation_id, $student_id, 'Accepted')) {
            
            // 2. تريكة هندسية: ممكن هنا تنادي ميثود في موديل الـ interview 
            // عشان تنشئ سجل المقابلة أوتوماتيك بمجرد القبول.
            
            return ['success' => true, 'message' => 'تم قبول الدعوة بنجاح!'];
        }
        return ['success' => false, 'message' => 'فشل في قبول الدعوة.'];
    }


    public function rejectInvitation($student_id, $invitation_id) {
        $invitationModel = new invitations($this->db);
        
        if ($invitationModel->decline($invitation_id, $student_id)) {
            return [
                'success' => true,
                'message' => 'تم رفض الدعوة بنجاح.'
            ];
        }

        return [
            'success' => false,
            'message' => 'عذراً، لم نتمكن من معالجة طلبك حالياً.'
        ];
    }



    public function showProfile($student_id) {
        $studentModel = new student($this->db, []);
        $profile = $studentModel->getProfileData($student_id);

        if ($profile) {
            return [
                'success' => true,
                'data' => $profile
            ];
        }

        return [
            'success' => false,
            'message' => 'عذراً، البروفايل ده مش موجود.'
        ];
    }



    public function updateProfileFull($student_id, $post_data) {
        // استدعاء الموديل
        $studentModel = new student($this->db, []);

        // تنفيذ التحديث في قاعدة البيانات (جدول user وجدول student)
        if ($studentModel->updateFullProfile($student_id, $post_data)) {

            $clean_data = [
                'major' => $post_data['major'],
                'gpa' => (float)$post_data['gpa'], // تأكدي إنه decimal
                'class_year' => (int)$post_data['class_year'] // تأكدي إنه integer
            ];
            
            // 🔥 تحديث الـ Session بكل البيانات عشان تظهر في الصفحة فوراً
            $_SESSION['User_Name']  = $post_data['user_name'];
            $_SESSION['User_Major'] = $post_data['major'];
            $_SESSION['GPA']        =  $clean_data['gpa'];        // زودنا ده
            $_SESSION['Class_Year'] =  $clean_data['class_year']; // وزودنا ده

            return [
                'success' => true,
                'message' => 'Profile has been edited successfully!'
            ];
        }

        return [
            'success' => false,
            'message' => 'An error occurred while updating your profile. Please try again.'
        ];
    }



    public function addProject($student_id, $post_data) {

        $projectModel = new student_projects($this->db);

        // رفع ملف المشروع
        $project_file = '';

        if(

            isset($_FILES['project_file']) &&

            $_FILES['project_file']['error'] == 0

        ){

            $extension = strtolower(

                pathinfo(

                    $_FILES['project_file']['name'],

                    PATHINFO_EXTENSION
                )
            );

            // PDF فقط
            if($extension == 'pdf'){

                $upload_dir =
                "../uploads/projects/";

                // إنشاء الفولدر لو مش موجود
                if(!file_exists($upload_dir)){

                    mkdir($upload_dir, 0777, true);

                }

                $newFileName =
                "project_" .
                time() .
                ".pdf";

                move_uploaded_file(

                    $_FILES['project_file']['tmp_name'],

                    $upload_dir . $newFileName
                );

                $project_file = $newFileName;
            }
        }

        // إضافة بيانات إضافية للبوست
        $post_data['project_file'] = $project_file;

        $post_data['video_url'] =
            trim($post_data['video_url']);

        // حفظ المشروع
        $new_id =
            $projectModel->insertProject(

                $student_id,

                $post_data
            );

        if ($new_id) {

            return [

                'success' => true,

                'project_id' => $new_id,

                'message' =>
                'Project added successfully.'

            ];
        }

        return [

            'success' => false,

            'message' =>
            'Failed to add project.'
        ];
    }

    public function deleteProject($student_id, $post_data){

        require_once __DIR__ . '/../Models/student_projects.php';

        $project_id = $post_data['project_id'];

        $projectModel = new student_projects($this->db);

        if(

            $projectModel->deleteProject(

                $project_id,

                $student_id
            )

        ){

            return [

                'success' => true,

                'message' =>
                'Project deleted successfully.'
            ];
        }

        return [

            'success' => false,

            'message' =>
            'Failed to delete project.'
        ];
    }


    public function updateCV() {

        if (session_status() === PHP_SESSION_NONE) {

            session_start();

        }

        if (!isset($_FILES['resume'])) {

            header("Location: ../Views/student/S_profile.php");

            exit();
        }

        $student_id = $_SESSION['User_ID'];

        $resume_label = $_POST['resume_name'];

        $file = $_FILES['resume'];

        // التحقق من الامتداد
        $extension = strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        if ($extension !== 'pdf') {

            $_SESSION['cv_error'] =
            "Only PDF files are allowed.";

            header("Location: ../Views/student/S_profile.php");

            exit();
        }

        // الفولدر
        $upload_dir = "../uploads/resumes/";

        if (!file_exists($upload_dir)) {

            mkdir($upload_dir, 0777, true);

        }

        // اسم الملف
        $fileNewName =
            "student_" .
            $student_id .
            "_" .
            time() .
            ".pdf";

        $fileDestination =
            $upload_dir .
            $fileNewName;

        // رفع الملف
        if (
            move_uploaded_file(
                $file['tmp_name'],
                $fileDestination
            )
        ) {

            // استخدام Resume Model
            $resumeModel = new resume($this->db);

            $saved = $resumeModel->addResume(

                $student_id,

                $resume_label,

                $fileNewName
            );

            if ($saved) {

                $_SESSION['cv_success'] =
                "Resume uploaded successfully!";

            } else {

                $_SESSION['cv_error'] =
                "Database insert failed.";
            }

        } else {

            $_SESSION['cv_error'] =
            "File upload failed.";
        }

        header("Location: ../Views/student/S_profile.php");

        exit();
    }

    public function updateStudentStatus(

        $student_id,

        $post_data

    ){

        $status = $post_data['status'];

        $studentModel = new student($this->db, []);

        if(

            $studentModel->updateStatus(

                $student_id,

                $status
            )

        ){

            return [

                'success' => true,

                'message' =>
                'Status updated successfully.'
            ];
        }

        return [

            'success' => false,

            'message' =>
            'Failed to update status.'
        ];
    }
}


if (isset($_GET['action']) && $_GET['action'] == 'updateCV') {
    // استخدمي $db_connect (أو اسم المتغير اللي جوه ملف db_connect.php)
    include_once '__DIR__ . ../../DataBase/DataBase/db_connect.php'; 
    $controller = new studentController($connect); 
    $controller->updateCV();
}

?>