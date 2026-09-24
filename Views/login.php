<?php
session_start();
// التأكد من مسار ملف الاتصال الصحيح
require_once '../DataBase/DataBase/db_connect.php'; 

if (isset($_POST['login_btn'])) {
    $role = mysqli_real_escape_string($connect, $_POST['userRole']);
    $identifier = mysqli_real_escape_string($connect, $_POST['email']);
    $password = $_POST['password'];

    if ($role === 'admin') {
        // البحث في جدول الأدمن باستخدام الكود (identifier)
        $query = "SELECT * FROM admin WHERE Admin_Code = '$identifier' LIMIT 1";
        $result = mysqli_query($connect, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $admin_data = mysqli_fetch_assoc($result);
            // لاحظي: الأدمن غالباً ملوش PasswordVerify لو بتستخدمي Admin_Code مباشر
            $_SESSION['User_ID'] = $admin_data['Admin_ID'];
            $_SESSION['User_Role'] = 'admin';
            $_SESSION['User_Name'] = 'Admin ' . $admin_data['Admin_Code'];
            
            // تخزين بيانات الأدمن كاملة لاستخدامها في api.php
            $_SESSION['admin_data'] = $admin_data; 

            header("Location: admin/A_adminDashboard.php");
            exit();
        }
    } else {
        // البحث في جدول المستخدمين (طالب أو شركة)
        $query = "SELECT * FROM user WHERE Email='$identifier' AND Role='$role' LIMIT 1";
        $result = mysqli_query($connect, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user_data = mysqli_fetch_assoc($result);
            
            if (password_verify($password, $user_data['Password'])) {
                $_SESSION['User_ID'] = $user_data['User_ID']; 
                $_SESSION['User_Role'] = $user_data['Role'];
                $_SESSION['User_Name'] = $user_data['F_Name'] . " " . $user_data['L_Name']; // مهم جداً للبروفايل
            
                // لو طالب، نجيب بياناته الإضافية (مثل التخصص)
            if ($user_data['Role'] === 'Student') {
                // 1. املئي السيشين من الـ $user_data (الاسم والـ ID موجودين في أول كويري في الصفحة)
                $_SESSION['User_ID']   = $user_data['User_ID'];
                $_SESSION['User_Name'] = $user_data['F_Name'] . " " . $user_data['L_Name'];

                // 2. هاتي بيانات الطالب الإضافية (التخصص والـ CV)
                $s_query = "SELECT Major, Resume_URL FROM student WHERE Student_ID = " . $user_data['User_ID'];
                $s_res = mysqli_query($connect, $s_query);
                $s_data = mysqli_fetch_assoc($s_res);

                if ($s_data) {
                    $_SESSION['User_Major'] = $s_data['Major'] ?? 'Engineering';
                    // بناخد اسم الملف فقط باستخدام basename
                    $_SESSION['User_CV']    = basename($s_data['Resume_URL'] ?? '');
                }

                session_write_close();
                header("Location: student/S_studentDashboard.php");
                exit();
            } else {
                header("Location: recruiter/R_recruiterDashboard.php");
                exit();
            }
        }
    }
    echo "<script>alert('Invalid credentials!'); window.location.href='login.php';</script>";
}
}
?>
<!-- الـ HTML يبدأ هنا -->

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>WorkPortal | Split Layout</title>
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    :root {
      --brand-green: #28a745;
      --brand-green-dark: #1e7e34;
      --transition-speed: 0.3s;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
      background-color: #ffffff;
      margin: 0;
    }

    .main-container {
      display: flex;
      min-height: 100vh;
      flex-wrap: wrap;
    }

    .side-visual {
      flex: 1;
      min-width: 400px;
      background: linear-gradient(135deg, rgba(40, 167, 69, 0.9), rgba(20, 80, 35, 0.9)), url("assets/back2.jpg");
      background-size: cover;
      background-position: center;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 4rem;
      color: white;
    }

    .form-content {
      flex: 1;
      min-width: 400px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 4rem;
      background: #fdfdfd;
    }

    .login-box {
      width: 100%;
      max-width: 400px;
    }

    .btn-green {
      background: var(--brand-green);
      color: white;
      border: none;
      padding: 0.9rem;
      border-radius: 0.5rem;
      font-weight: bold;
      transition: all var(--transition-speed);
    }

    .btn-green:hover {
      background: var(--brand-green-dark);
      color: white;
    }

    .role-selector {
      background: #f1f3f5;
      padding: 0.4rem;
      border-radius: 0.75rem;
      display: flex;
      gap: 5px;
    }

    .btn-check:checked+.btn-outline-green {
      background-color: white;
      border-color: transparent;
      color: var(--brand-green);
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .btn-outline-green {
      border: 1px solid transparent;
      color: #6c757d;
      flex: 1;
      border-radius: 0.5rem;
      font-weight: 600;
    }

    .feature-item {
      margin-bottom: 2rem;
      display: flex;
      align-items: flex-start;
      gap: 1.5rem;
    }

    .mini-circle {
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.4);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      font-weight: bold;
    }

    .form-control {
      padding: 0.75rem;
      border-radius: 0.5rem;
      border: 1px solid #dee2e6;
    }
  </style>
</head>

<body>

  <main class="main-container">
    <section class="side-visual">
      <h1 class="display-4 fw-bold mb-4">WorkPortal</h1>
      <p class="lead mb-5 opacity-75">Streamlining professional connections in your local workspace.</p>

      <div class="feature-item">
        <div class="mini-circle">S</div>
        <div>
          <h5 class="mb-1">Students</h5>
          <p class="small opacity-75">Build profiles.</p>
        </div>
      </div>
      <div class="feature-item">
        <div class="mini-circle">R</div>
        <div>
          <h5 class="mb-1">Recruiters</h5>
          <p class="small opacity-75">Manage pipelines.</p>
        </div>
      </div>
      <div class="feature-item">
        <div class="mini-circle">A</div>
        <div>
          <h5 class="mb-1">Admins</h5>
          <p class="small opacity-75">Secure control.</p>
        </div>
      </div>
    </section>

    <section class="form-content">
      <div class="login-box">
        <div class="mb-5">
          <h2 class="fw-bold">Welcome Back</h2>
          <p class="text-muted">Please enter your details to continue</p>
        </div>

        <form id="loginForm" method="POST" action="">
          <div class="mb-4">
            <label class="form-label small fw-bold">Select Role</label>
            <div class="role-selector">
              <input type="radio" class="btn-check" name="userRole" id="student" value="student" checked />
              <label class="btn btn-outline-green" for="student">Student</label>

              <input type="radio" class="btn-check" name="userRole" id="recruiter" value="recruiter" />
              <label class="btn btn-outline-green" for="recruiter">Recruiter</label>

              <input type="radio" class="btn-check" name="userRole" id="admin" value="admin" />
              <label class="btn btn-outline-green" for="admin">Admin</label>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold" id="identifierLabel">Email Address</label>
            <input type="text" class="form-control" name="email" id="email" placeholder="name@domain.local" required />
          </div>

          <div class="mb-4">
            <label class="form-label small fw-bold">Password</label>
            <input type="password" name="password" class="form-control" id="password" placeholder="••••••••" required />
          </div>

          <button type="submit" name="login_btn" class="btn btn-green w-100 mb-4">Sign In</button>
        </form>
        <div class="text-center">
          <span class="small text-muted">Doesnt have an account?</span>
          <a href="signup.php" class="small fw-bold text-decoration-none ms-1" style="color: var(--brand-green)">Sign Up</a>
        </div>
      </div>
    </section>

  </main>

  <script>
    const roleRadios = document.querySelectorAll('input[name="userRole"]');
    const idLabel = document.getElementById('identifierLabel');
    const idInput = document.getElementById('email');

    roleRadios.forEach(radio => {
    radio.addEventListener('change', function() {
        const passwordInput = document.getElementById('password');
        
        if (this.value === 'admin') {
            idLabel.innerText = 'Admin Code';
            idInput.placeholder = 'Enter your admin code...';
            

            passwordInput.disabled = true;
            passwordInput.placeholder = 'Not required';
            passwordInput.required = false;
            passwordInput.value = ''; 
        } else {
            idLabel.innerText = 'Email Address';
            idInput.placeholder = 'name@domain.local';
            

            passwordInput.disabled = false;
            passwordInput.placeholder = '••••••••';
            passwordInput.required = true;
        }
    });
});

// Update sessionStorage for JavaScript compatibility
document.getElementById("loginForm").addEventListener("submit", function(e) {
  const selectedInput = document.querySelector('input[name="userRole"]:checked');
  const idInput = document.getElementById('email');
  
  // Set sessionStorage with proper structure for students
  const userSession = {
    id: selectedInput.value === 'student' ? 1 : idInput.value.trim(), 
    role: selectedInput.value,
    name: selectedInput.value === 'student' ? "Student" : idInput.value.trim(),
    loggedInAt: new Date().toISOString()
  };

  sessionStorage.setItem('workportal_user', JSON.stringify(userSession));
});
  </script>
</body>

</html>