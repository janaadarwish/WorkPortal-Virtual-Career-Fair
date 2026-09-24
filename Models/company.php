<?php
// FileName: models/company.php

class company {
    private $db;
    protected int $companyID;
    protected string $companyName;
    protected string $location;
    protected string $industry;
    protected string $websiteUrl;

    public function __construct($connection, $data = null) {
        $this->db = $connection;
        if ($data) {
            $this->companyID   = $data['Company_ID'] ?? 0;
            $this->companyName = $data['Company_Name'] ?? '';
            $this->location    = $data['Location'] ?? '';
            $this->industry    = $data['Industry'] ?? '';
            $this->websiteUrl  = $data['Website_URL'] ?? '';
        }
    }

    // جلب الشركات المشاركة في معرض معين
    public function getCompaniesByFair($fair_id): array {
        $sql = "SELECT c.* FROM company c 
                JOIN participates_in p ON c.Company_ID = p.Company_ID 
                WHERE p.Fair_ID = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $fair_id);
        mysqli_stmt_execute($stmt);
        return mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    }

    public function updateApprovalStatus($company_id, $admin_id, $is_approved) {
        // لو وافق بنسجل الـ ID بتاعه، لو رفض بنرجعها لـ 0 (أو NULL حسب تصميمك)
        $approval_value = $is_approved ? $admin_id : 0;
        
        $sql = "UPDATE company SET Approved_By_Admin = ? WHERE Company_ID = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $approval_value, $company_id);
        
        return mysqli_stmt_execute($stmt);
    }
}