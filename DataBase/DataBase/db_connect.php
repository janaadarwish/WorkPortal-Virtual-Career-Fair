<?php
// FileName: DataBase/DataBase/db_connect.php
$host = "localhost";
$user_name = "root";
$pass = "";
$db_name = "virtual_career_fair"; 

// 2. إعدادات الأخطاء (سيبيها زي ما هي)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 3. دالة الاتصال
function getDatabaseConnection() {
    global $host, $user_name, $pass, $db_name;
    
    try {
        $connect = mysqli_connect($host, $user_name, $pass, $db_name);
        if (!$connect) {
            throw new Exception("Database connection failed: " . mysqli_connect_error());
        }
        mysqli_set_charset($connect, "utf8");
        return $connect;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return null;
    }
}

// 4. إنشاء الاتصال الأساسي
$connect = getDatabaseConnection();

// 5. لو فشل الاتصال نطلع رسالة بسيطة (شيلنا الـ JSON عشان ميبوظش الداشبورد)
if (!$connect) {
    die("عذراً، فشل الاتصال بقاعدة البيانات. تأكد من تشغيل MySQL في XAMPP.");
}