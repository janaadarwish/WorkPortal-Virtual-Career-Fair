<?php
session_start();
// التأكد من مسار ملف الاتصال الصحيح
require_once '../DataBase/DataBase/db_connect.php'; 

$message = "";

if (isset($_POST['signup_btn'])) {
    $fname = mysqli_real_escape_string($connect, $_POST['f_name']);
    $lname = mysqli_real_escape_string($connect, $_POST['l_name']);
    $email = mysqli_real_escape_string($connect, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($connect, $_POST['regRole']);
    $status = "Active";

    // 1. إدخال البيانات في جدول 'user' الأساسي
    $user_query = "INSERT INTO user (F_Name, L_Name, Email, Password, Role, Status, Created_At) 
                   VALUES ('$fname', '$lname', '$email', '$password', '$role', '$status', NOW())";

    if (mysqli_query($connect, $user_query)) {
        $new_user_id = mysqli_insert_id($connect);
        $role_query = "";

        // 2. تجهيز بيانات جدول الطالب أو الشركة
        if ($role === 'student') {
            $role_query = "INSERT INTO student (Student_ID, Major, Resume_URL, GPA, Class_Year, Ready_Status) 
                           VALUES ('$new_user_id', 'Computer Engineering', '', 0.00, 2026, 'Available')";
        } else if ($role === 'recruiter') {
            // لو شركة، بنربطه بـ Company_ID (تأكدي إن الفورم بتبعت القيمة دي)
            $company_id = isset($_POST['company_id']) ? mysqli_real_escape_string($connect, $_POST['company_id']) : 1;
            $role_query = "INSERT INTO recruiter (Recruiter_ID, Company_ID, Position, Queue_Status) 
                           VALUES ('$new_user_id', '$company_id', 'Hiring Manager', 'Available')";
        }

        // 3. تنفيذ الكويري الفرعية وتوجيه المستخدم
        if ($role_query !== "" && mysqli_query($connect, $role_query)) {
            // تسجيل الدخول تلقائياً بعد التسجيل بنجاح
            $_SESSION['User_ID'] = $new_user_id;
            $_SESSION['User_Role'] = $role;
            $_SESSION['User_Name'] = $fname . " " . $lname; // عشان تظهر في البروفايل
            
            if ($role === 'student') {
                $_SESSION['User_Major'] = 'Computer Engineering'; // التخصص الافتراضي
                header("Location: student/S_studentDashboard.php");
            } else {
                header("Location: recruiter/R_recruiterDashboard.php");
            }
            exit();
        } else {
            $message = "حدث خطأ في إنشاء الملف الشخصي: " . mysqli_error($connect);
        }
    } else {
        $message = "هذا الإيميل مسجل مسبقاً أو هناك خطأ في البيانات.";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>WorkPortal | Create Account</title>
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    :root { --brand-green: #28a745; --brand-green-dark: #1e7e34; }
    .signup-container { min-height: 100vh; display: flex; }
    .signup-visual { flex: 1; background: linear-gradient(rgba(40,167,69, 0.8), rgba(30, 126, 52, 0.9)), url("assets/background.jpg"); background-size: cover; display: none; flex-direction: column; justify-content: center; padding: 4rem; color: white; }
    .signup-form-side { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem; background: #f8f9fa; }
    @media (min-width: 992px) { .signup-visual { display: flex; } }
    .form-content { width: 100%; max-width: 450px; }
    .btn-green { background: var(--brand-green); color: white; border: none; padding: 0.8rem; border-radius: 0.75rem; font-weight: bold; }
    .btn-green:hover { background: var(--brand-green-dark); color: white; }
    .form-control { border-radius: 0.75rem; padding: 0.75rem 1rem; }
    .btn-outline-green { border-color: var(--brand-green); color: var(--brand-green); border-radius: 0.75rem; }
    .btn-check:checked+.btn-outline-green { background-color: var(--brand-green); color: white; }
    /* Hidden company field style */
    #companyWrapper { display: none; }
  </style>
</head>
<body>

  <?php if ($message === "success"): ?>
    <script>
      alert("Account created successfully! Please log in.");
      window.location.href = "login.php";
    </script>
  <?php elseif ($message !== ""): ?>
    <div class="alert alert-danger"><?php echo $message; ?></div>
  <?php endif; ?>

  <div class="signup-container">
    <div class="signup-visual">
      <h1 class="display-3 fw-bold mb-4">Join the <br />WorkPortal.</h1>
      <p class="lead mb-5">Create an account to start managing your professional journey today.</p>
    </div>

    <div class="signup-form-side">
      <div class="form-content">
        <div class="mb-5">
          <h2 class="fw-bold">Create Account</h2>
          <p class="text-muted">Fill in your details to get started.</p>
        </div>

        <form id="signupForm" method="POST" action="">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold">First Name</label>
              <input type="text" name="f_name" class="form-control" placeholder="John" required />
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">Last Name</label>
              <input type="text" name="l_name" class="form-control" placeholder="Doe" required />
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold">Account Type</label>
            <div class="btn-group w-100 role-pill-group" role="group">
              <input type="radio" class="btn-check" name="regRole" id="regStudent" value="student" checked onclick="toggleCompany(false)"/>
              <label class="btn btn-outline-green" for="regStudent">Student</label>

              <input type="radio" class="btn-check" name="regRole" id="regRecruiter" value="recruiter" onclick="toggleCompany(true)"/>
              <label class="btn btn-outline-green" for="regRecruiter">Recruiter</label>
            </div>
          </div>

          <!-- NEW: Company Selection Field -->
          <div class="mb-3" id="companyWrapper">
            <label class="form-label small fw-bold">Select Your Company</label>
            <select name="company_id" class="form-control">
                <option value="1">General Company</option>
                <!-- As you add more companies to your DB, they go here -->
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="john@university.edu" required />
          </div>

          <div class="mb-4">
            <label class="form-label small fw-bold">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required />
          </div>

          <button type="submit" name="signup_btn" class="btn btn-green w-100 mb-4">
            Create Free Account
          </button>

          <div class="text-center">
            <span class="small text-muted">Already have an account?</span>
            <a href="login.php" class="small fw-bold text-decoration-none ms-1" style="color: var(--brand-green)">Log In</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Logic to show/hide the company box
    function toggleCompany(isRecruiter) {
        const wrapper = document.getElementById('companyWrapper');
        wrapper.style.display = isRecruiter ? 'block' : 'none';
    }
  </script>
</body>
</html>