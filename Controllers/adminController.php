<?php
// FileName: controllers/admin_module.php

require_once '../Models/fair.php';
require_once '../Models/company.php';
require_once '../Models/admin.php';
require_once '../Models/user.php';

class adminController {
    private $db;

    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    // عرض الـ Dashboard بناءً على الرابط اللي في الموديل عندك
    public function loadDashboard($admin_obj) {
        // بنستخدم الميثود اللي إنتي كاتباها في الصورة
         $url = $admin_obj->getDashboardUrl();
        include($url);
    }

    // مثال لميزة مسموحة للـ SuperAdmin بس
    public function deleteCompany($admin_obj, $company_id) {
        // بنستخدم ميثود التأكد من الصلاحية اللي في صورتك
        if (!$admin_obj->isSuperAdmin()) {
            return [
                'success' => false,
                'message' => 'عذراً، هذه الصلاحية للمدير الخارق فقط!'
            ];
        }

        // لو هو SuperAdmin بنكمل عملية الحذف...
        $adminModel = new admin($this->db, []);
        if ($adminModel->deleteUser($company_id)) {
            return ['success' => true, 'message' => 'تم حذف الشركة بنجاح.'];
        }
    }


    public function setupNewFair($admin_obj, $data) {
    // التأكد إن الأدمن ده SuperAdmin من الميثود اللي في صورتك السابقة
        if (!$admin_obj->isSuperAdmin()) {
            return ['success' => false, 'message' => 'صلاحيات غير كافية'];
        }

        $fairModel = new fair($this->db);
        $new_id = $fairModel->insertFair($data['title'], $data['start'], $data['end'], $admin_obj->getAdminCode());

        return $new_id ? ['success' => true, 'id' => $new_id] : ['success' => false];
    }


    public function viewFairAnalytics($admin_obj, $fair_id) {
    // الأدمن بس هو اللي يشوف التحليلات دي
        if (!$admin_obj) {
            return ['success' => false, 'message' => 'Unauthorized access'];
        }

        $fairModel = new fair($this->db);
        $data = $fairModel->getTrafficData($fair_id);

        return [
            'success' => true,
            'data' => $data,
            'message' => 'تم جلب بيانات الزحمة بنجاح'
        ];
    }


    public function generateReport($admin_obj, $fair_id) {
    // التأكد إن الأدمن معاه صلاحية عرض التقارير (مثلاً SuperAdmin أو Manager)
    if (!$admin_obj) {
        return ['success' => false, 'message' => 'غير مصرح لك بالوصول'];
    }

    $fairModel = new fair($this->db);
    $reportData = $fairModel->getFairPerformanceReport($fair_id);

    return [
        'success' => true,
        'data' => $reportData,
        'message' => 'تم إنشاء تقرير أداء المعرض بنجاح'
    ];
    }

    public function handleCompanyApproval($admin_obj, $data) {
    // 1. التحقق من الصلاحية (SuperAdmin فقط هو اللي يقبل الشركات)
    if (!$admin_obj->isSuperAdmin()) {
        return [
            'success' => false, 
            'message' => 'عذراً، هذه الصلاحية للمدير الخارق فقط!'
        ];
    }

    // 2. استدعاء موديل الشركة
    $companyModel = new company($this->db);
    $company_id = (int)$data['company_id'];
    $approved = filter_var($data['approved'], FILTER_VALIDATE_BOOLEAN);

    if ($companyModel->updateApprovalStatus($company_id, $admin_obj->getAdminCode(), $approved)) {
        $status_msg = $approved ? 'قبولها' : 'رفضها';
        return [
            'success' => true,
            'message' => "تم $status_msg الشركة بنجاح بواسطة " . $admin_obj->getAdminCode()
        ];
    }

    return ['success' => false, 'message' => 'فشل تحديث حالة الشركة.'];
    }


    public function getFairSchedule($fair_id, $timezone) {
    $fairModel = new fair($this->db);
    $times = $fairModel->getLocalizedTimes($fair_id, $timezone);

    if ($times) {
        return ['success' => true, 'data' => $times];
    }
    return ['success' => false, 'message' => 'تعذر الحصول على مواعيد المعرض.'];
    }


    public function broadcastFairReminder($admin_obj, $fair_id) {
    // التأكد إن الأدمن معاه صلاحية (مثلاً SuperAdmin أو Manager)
    if (!$admin_obj) {
        return ['success' => false, 'message' => 'غير مصرح لك'];
    }

    $fairModel = new fair($this->db);
    $emails = $fairModel->getFairParticipantEmails($fair_id);

    $emails_sent = 0;
    $subject = "Virtual Career Fair Reminder";
    $message = "This is a reminder about the upcoming Virtual Career Fair. Please check your schedule and prepare accordingly.";
    $headers = "From: admin@careerfair.com\r\n" . "Content-Type: text/plain; charset=UTF-8\r\n";

    foreach ($emails as $email) {
        if (mail($email, $subject, $message, $headers)) {
            $emails_sent++;
        }
    }

    return [
        'success' => true,
        'data' => ['emails_sent' => $emails_sent],
        'message' => "تم إرسال التذكيرات بنجاح لـ $emails_sent طالب."
    ];
    }

    public function reject($id) {
    $company = Company::find($id);
    $company->delete();

    return redirect()->back()->with('success', 'تم رفض الشركة بنجاح');
}

}
?>