<?php
// api.php
session_start();

// 1. استدعاء ملف الاتصال والكنترولرز
require_once __DIR__ . '/DataBase/DataBase/db_connect.php'; 
require_once __DIR__ . '/Controllers/adminController.php';
require_once __DIR__ . '/Controllers/studentController.php';
require_once __DIR__ . '/Controllers/queueController.php';

// 2. استقبال نوع الكنترولر والأكشن المطلوبة
$controllerName = $_POST['controller'] ?? '';
$action = $_POST['action'] ?? '';

// 3. توجيه الطلب حسب النوع
switch ($controllerName) {
    case 'student':
        $controller = new StudentController($connect);

        $response =
        $controller->$action(
            $_SESSION['User_ID'],
            $_POST
        );

        // Flash Message
        $_SESSION['flash_message'] =
        $response['message'];

        $_SESSION['flash_status'] =
        $response['success']
        ? 'success'
        : 'danger';

        // Redirect
        header(
            "Location: /workportal/Views/student/S_profile.php"
        );

        exit();

    case 'admin':
        $controller = new AdminController($connect);
        // تجهيز أوبجكت الأدمن بناءً على كلاس admin اللي عندك
        require_once __DIR__ . '/Models/admin.php';
        $adminObj = new admin($connect, $_SESSION['admin_data']);
        $response = $controller->$action($adminObj, $_POST);
        // الرجوع لداشبورد الأدمن
        header("Location: Views/admin/A_adminDashboard.php?done=1");
        break;

    case 'queue':
        $controller = new QueueController($connect);
        // ميثود joinQueue بتحتاج ID الطالب والبيانات (مثل company_id)
        $response = $controller->$action($_SESSION['User_ID'], $_POST);
        // الرجوع لصفحة المعرض أو الداشبورد
        header("Location: Views/student/S_studentDashboard.php?msg=" . urlencode($response['message']));
        break;

    default:
        // لو مفيش كنترولر معروف يرجعه للرئيسية
        header("Location: index.php");
        break;
}
exit();