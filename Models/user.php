<?php
// FileName: models/user.php

abstract class user {
    // الخصائص مطابقة تماماً لجدول الـ user في الداتا بيز
    protected int $userID; // ID في الـ SQL هو int
    protected string $fName;
    protected string $lName;
    protected string $email;
    protected string $password;
    protected string $role;
    protected string $status;

    public function __construct($data) {
        // بنمرر مصفوفة البيانات اللي راجعة من الداتا بيز مباشرة
        $this->userID = $data['User_ID'] ?? 0;
        $this->fName  = $data['F_Name'] ?? '';
        $this->lName  = $data['L_Name'] ?? '';
        $this->email  = $data['Email'] ?? '';
        $this->password = $data['Password'] ?? ''; // بنسيبها زي ما هي من الداتا بيز
        $this->role   = $data['Role'] ?? '';
        $this->status = $data['Status'] ?? 'Active';
    }

    // الـ Update Profile لازم يستخدم أسماء الأعمدة الصح
    public function updateProfile($connect, $newName, $newEmail): bool {
        $sql = "UPDATE user SET F_Name = ?, Email = ? WHERE User_ID = ?";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $newName, $newEmail, $this->userID);
        
        if (mysqli_stmt_execute($stmt)) {
            $this->fName = $newName;
            $this->email = $newEmail;
            return true;
        }
        return false;
    }

    // ميثود تسجيل الخروج
    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        session_unset();
        session_destroy();
    }

    // ميثود مجردة لازم الطالب والريكروتر ينفذوها
    abstract public function getDashboardUrl(): string;

    // Getters
    public function getFullName(): string { return $this->fName . " " . $this->lName; }
    public function getEmail(): string { return $this->email; }
}