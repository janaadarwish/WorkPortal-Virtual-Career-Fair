<?php
// FileName: models/admin.php
require_once 'user.php';

class admin extends user {
    private $db;
    protected string $adminCode;
    protected string $privilegeLevel; // SuperAdmin, Manager, Editor

    public function __construct($connection, $data) {
        // نداء الـ constructor بتاع الأب (User)
        parent::__construct($data); 
        $this->db = $connection;
        
        // بيانات الأدمن الخاصة
        $this->adminCode      = $data['Admin_Code'] ?? '';
        $this->privilegeLevel = $data['Privilege_Level'] ?? 'Editor';
    }

    // تطبيق الـ Abstract Method للـ Dashboard الخاص بالأدمن
    public function getDashboardUrl(): string {
        return "pages/admin/A_dashboard.php";
    }

    // ميثود للتأكد من الصلاحيات
    public function isSuperAdmin(): bool {
        return $this->privilegeLevel === 'SuperAdmin';
    }

    public function getAdminCode(): string { return $this->adminCode; }
}