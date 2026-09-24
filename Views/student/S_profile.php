<?php
session_start();

// لو مفيش مستخدم عامل login، رجعه لصفحة اللوجين (اختياري بس أمان)
if (!isset($_SESSION['User_ID'])) {
    header("Location: ../login.php");
    exit();
} ?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | WorkPortal</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" />

    <style>
        :root {
            --brand-green: #28a745;
            --brand-slate: #001d3f;
            --bg-light: #f8f9fa;
            --ai-purple: #6f42c1;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Inter', sans-serif;
        }

        .main-content {
            margin-left: 260px;
            padding: 2rem;
        }

        /* 🧭 1. Profile Header */
        .profile-header {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            background: #e9ecef;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .resume-card {
            border: 2px dashed #dee2e6;
            border-radius: 15px;
            transition: 0.3s;
        }

        .resume-card:hover {
            border-color: var(--brand-green);
            background: #f0fdf4;
        }

        .gap-card {
            background: linear-gradient(135deg, #ffffff 0%, #f3e8ff 100%);
            border: 1px solid #dcd3ff;
        }

        .skill-tag {
            background: white;
            border: 1px solid #dee2e6;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .missing-skill {
            background: rgba(111, 66, 193, 0.1);
            color: var(--ai-purple);
            border: 1px dashed var(--ai-purple);
        }

        .strength-meter {
            height: 10px;
            border-radius: 10px;
            background: #e9ecef;
        }

        .sidebar {
            width: 260px;
            background: var(--brand-slate);
            color: white;
            padding: 2rem 1.5rem;
            position: fixed;
            height: 100vh;
        }

        .nav-link {
            color: #adb5bd;
            padding: 0.8rem;
            text-decoration: none;
            display: block;
            border-radius: 10px;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: var(--brand-green);
        }
    </style>
</head>

<body data-student-id="<?php echo (int) $_SESSION['User_ID']; ?>">

    <nav class="sidebar">
        <h4 class="fw-bold mb-5">
            <span class="text-white">WorkPortal</span> <br />
            <span class="text-success" style="font-size: 1.2rem;">Student</span>
        </h4>
        <div class="nav flex-column">
            <a href="S_studentDashboard.php" class="nav-link "> Dashboard</a>
            <a href="S_fair.php" class="nav-link"> Browse Fairs</a>
            <a href="S_liveSessions.php" class="nav-link">Live Interview & Chat Session</a>
            <a href="S_profile.php" class="nav-link active"> Profile</a>
            <a href="../../index.php" class="nav-link text-danger mt-auto"> Logout</a>
        </div>
    </nav>

    <div class="main-content">


        <?php if(isset($_SESSION['flash_message'])){ ?>

            <div class="alert alert-<?php echo $_SESSION['flash_status']; ?> alert-dismissible fade show">

                <?php

                    echo $_SESSION['flash_message'];

                    unset($_SESSION['flash_message']);

                    unset($_SESSION['flash_status']);

                ?>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                </button>

            </div>

        <?php } ?>


        <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['cv_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> <?php echo $_SESSION['cv_success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['cv_success']); // السطر ده هو اللي بيمسحها عشان متظهرش تاني في الـ Refresh 
            ?>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Error!</strong> Something went wrong with the file upload.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- حطي الكود هنا عشان الرسالة تظهر في وش الطالب أول ما يفتح البروفايل -->
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>Success!</strong> <?php echo htmlspecialchars($_GET['msg'] ?? 'Profile updated successfully!'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'error'): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Error!</strong> <?php echo htmlspecialchars($_GET['msg'] ?? 'Something went wrong.'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>


        <?php if(isset($_SESSION['privacy_success'])){ ?>

            <div class="alert alert-success alert-dismissible fade show mb-4">

                <i class="bi bi-shield-check me-2"></i>

                <?php
                    echo $_SESSION['privacy_success'];
                    unset($_SESSION['privacy_success']);
                ?>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                </button>

            </div>

        <?php } ?>

        <?php if(isset($_SESSION['privacy_error'])){ ?>

            <div class="alert alert-danger alert-dismissible fade show mb-4">

                <i class="bi bi-exclamation-triangle me-2"></i>

                <?php
                    echo $_SESSION['privacy_error'];
                    unset($_SESSION['privacy_error']);
                ?>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                </button>

            </div>

        <?php } ?>


        <div class="profile-header">
            <div class="row align-items-center">
                <div class="col-md-auto">
                    <div class="profile-pic d-flex align-items-center justify-content-center">
                        <i class="bi bi-person-fill fs-1 text-muted"></i>
                    </div>
                </div>
                <div class="col">
                    <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($_SESSION['User_Name']); ?></h2>
                    <p class="text-muted mb-2"><?php echo htmlspecialchars($_SESSION['User_Major']); ?> Student • Class of <?php echo htmlspecialchars($_SESSION['Class_Year'] ?? '2026'); ?></p>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="bi bi-pencil me-1"></i> Edit Profile
                        </button>
                        <button type="button" id="btn-preview-recruiter" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i> Preview as Recruiter</button>

                    </div>
                </div>
                <div class="col-md-3 text-end">
                    <small class="text-muted d-block mb-1">Profile Completion</small>
                    <h4 class="fw-bold text-success mb-2">78%</h4>
                    <div class="progress strength-meter">
                        <div class="progress-bar bg-success" style="width: 78%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">

                <div class="card p-4 gap-card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0"><i class="bi bi-cpu me-2 text-primary"></i> AI Skill Engine</h5>
                        <span class="badge bg-purple text-white" style="background: var(--ai-purple);">AI Insights Active</span>
                    </div>

                    <div class="mb-4">
                        <h6 class="small fw-bold text-muted text-uppercase mb-3">Current Expertise</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <?php

                            require_once __DIR__ . '/../../DataBase/DataBase/db_connect.php';
                            require_once __DIR__ . '/../../Models/student_skills.php';

                            $skillsModel = new student_skills($connect);

                            $skills =
                            $skillsModel->getSkillsByStudent(

                                $_SESSION['User_ID']
                            );

                            foreach($skills as $skill){

                            ?>

                                <span class="skill-tag">

                                    <?php echo htmlspecialchars($skill); ?>

                                </span>

                            <?php } ?>
                            <button type="button"
                                    class="btn btn-sm text-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#addSkillModal">

                                + Add Skill

                            </button>
                        </div>
                    </div>

                    <div class="p-3 bg-white rounded-4 border">
                        <h6 class="small fw-bold text-danger text-uppercase mb-3"><i class="bi bi-lightning-charge-fill"></i> Missing Skills for Target Roles</h6>
                        <?php

                        require_once __DIR__ . '/../../Controllers/studentController.php';

                        require_once __DIR__ . '/../../DataBase/DataBase/db_connect.php';

                        $controller = new studentController($connect);

                        /*
                            حطي أي Job ID موجود عندك للتجربة
                        */
                        $job_id = 1;

                        $analysis = $controller->analyzeSkillGap(

                            $_SESSION['User_ID'],

                            $job_id
                        );

                        ?>

                        <div class="d-flex flex-wrap gap-2 mb-3">

                        <?php if($analysis['missing_count'] > 0){ ?>

                            <?php foreach($analysis['missing_list'] as $skill){ ?>

                                <span class="skill-tag missing-skill">

                                    <?php echo ucfirst($skill); ?>

                                </span>

                            <?php } ?>

                        <?php } else { ?>

                            <span class="badge bg-success">

                                You match all required skills 🎉

                            </span>

                        <?php } ?>

                        </div>

                    </div>
                </div>

                <div class="card p-4 mb-4">
                    <h5 class="fw-bold mb-3">Resume Management</h5>
                    <div class="resume-card p-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <h5 class="fw-bold mb-0">
                                My Resume Versions
                            </h5>

                            <!-- زرار رفع Resume جديدة -->
                            <button type="button"
                                    id="btn-replace-resume"
                                    class="btn btn-sm btn-outline-dark"
                                    data-bs-toggle="modal"
                                    data-bs-target="#uploadCVModal">

                                Upload Resume

                            </button>

                        </div>

                    <?php

                    require_once __DIR__ . '/../../DataBase/DataBase/db_connect.php';

                    $student_id = $_SESSION['User_ID'];

                    $query = "SELECT * FROM resumes
                    WHERE Student_ID = '$student_id'
                    ORDER BY Created_At DESC";

                    $result = mysqli_query($connect, $query);

                    

                    $queue_query = "SELECT Queue_ID
                                    FROM waits_in
                                    WHERE Student_ID = '$student_id'
                                    ORDER BY Queue_ID DESC
                                    LIMIT 1";

                    $queue_res = mysqli_query($connect, $queue_query);

                    $queue_data = mysqli_fetch_assoc($queue_res);

                    $active_queue_id = $queue_data['Queue_ID'] ?? null;


                    if(mysqli_num_rows($result) > 0){

                        while($resume = mysqli_fetch_assoc($result)){

                    ?>

                        <div class="border rounded p-3 mb-3">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <i class="bi bi-file-earmark-pdf text-danger me-2"></i>

                                    <strong>
                                        <?php echo htmlspecialchars($resume['Resume_Label']); ?>
                                    </strong>

                                    <p class="small text-muted mb-0 mt-1">

                                        Uploaded:
                                        <?php echo $resume['Created_At']; ?>

                                    </p>

                                </div>

                                <div class="d-flex gap-2">

                                    <!-- Preview -->

                                    <a href="../../uploads/resumes/<?php echo $resume['File_URL']; ?>"
                                    target="_blank"
                                    class="btn btn-sm btn-light">

                                        Preview

                                    </a>

                                    <a href="../../Controllers/queueController.php?action=attach_resume&queue_id=<?php echo $active_queue_id; ?>&resume_id=<?php echo $resume['Resume_ID']; ?>"
                                    class="btn btn-success btn-sm">

                                        Attach To Booth

                                    </a>

                                </div>

                            </div>

                        </div>

                    <?php

                        }

                    }else{

                    ?>

                        <div class="text-center text-muted">

                            No Resume Uploaded Yet

                        </div>

                    <?php } ?>

                    </div>
                    <br>
                    <div class="card p-4 mb-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h5 class="fw-bold mb-0">
            Portfolio & Projects
        </h5>

        <button type="button"
                class="btn btn-dark"
                data-bs-toggle="modal"
                data-bs-target="#addProjectModal">

            <i class="bi bi-plus-circle me-1"></i>

            Add Project

        </button>

    </div>
    
    <?php

    require_once __DIR__ . '/../../DataBase/DataBase/db_connect.php';
    require_once __DIR__ . '/../../Models/student_projects.php';

    $projectModel = new student_projects($connect);

    $projects = $projectModel->getStudentProjects($_SESSION['User_ID']);

    if(!empty($projects)){

        foreach($projects as $project){

    ?>

    <div class="resume-card p-4">

        <div class="text-center">

            <i class="bi bi-folder2-open fs-1 text-primary mb-2"></i>

            <h6 class="fw-bold mb-1">
                <?php echo htmlspecialchars($project['Project_Name']); ?>
            </h6>

            <p class="small text-muted mb-3">
                <?php echo htmlspecialchars($project['Description']); ?>
            </p>

        </div>

        <!-- Technologies -->

        <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">

            <?php

            $techs = explode(',', $project['Technologies']);

            foreach($techs as $tech){

            ?>

                <span class="badge bg-light text-dark border">
                    <?php echo trim($tech); ?>
                </span>

            <?php } ?>

        </div>

        <!-- Links -->

        <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">

            <?php if(!empty($project['Project_URL'])){ ?>

                <a href="<?php echo $project['Project_URL']; ?>"
                target="_blank"
                class="btn btn-sm btn-dark">

                    <i class="bi bi-github me-1"></i> GitHub

                </a>

            <?php } ?>

            <?php if(!empty($project['Project_File'])){ ?>

                <a href="../../uploads/projects/<?php echo $project['Project_File']; ?>"
                target="_blank"
                class="btn btn-sm btn-outline-primary">

                    <i class="bi bi-file-earmark-pdf me-1"></i> Portfolio PDF

                </a>

            <?php } ?>

            <?php if(!empty($project['Video_URL'])){ ?>

                <a href="<?php echo $project['Video_URL']; ?>"
                target="_blank"
                class="btn btn-sm btn-outline-danger">

                    <i class="bi bi-play-circle me-1"></i> Video Intro

                </a>

            <?php } ?>

        </div>

        <!-- Buttons -->

        <div class="d-flex justify-content-center gap-2">

            <form action="../../api.php"
                method="POST"
                onsubmit="return confirm('Delete this project?')">

                <input type="hidden"
                    name="controller"
                    value="student">

                <input type="hidden"
                    name="action"
                    value="deleteProject">

                <input type="hidden"
                    name="project_id"
                    value="<?php echo $project['Project_ID']; ?>">

                <button type="submit"
                        class="btn btn-sm btn-danger">

                    Delete

                </button>

            </form>

        </div>

    </div>

    <?php

        }

    }else{

    ?>

    <div class="text-center text-muted">
        No Projects Added Yet
    </div>

    <?php } ?>

</div>
                </div>
            </div>

            <div class="col-lg-4">

                <div class="card p-4 mb-4 bg-dark text-white border-0 shadow">
                    <h6 class="fw-bold text-uppercase small text-success mb-3">AI Recommendations</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-3 d-flex gap-2">
                            <i class="bi bi-check2-circle text-success"></i>
                            <span>Add 2 more backend projects to rank #1 for "Server Engineer" roles.</span>
                        </li>
                        <li class="mb-3 d-flex gap-2">
                            <i class="bi bi-plus-circle text-warning"></i>
                            <span>Include a GitHub link to verify your code quality.</span>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="bi bi-exclamation-triangle text-info"></i>
                            <span>Update your "About Me" with cloud-computing interests.</span>
                        </li>
                    </ul>
                    <button type="button" id="btn-optimize-now" class="btn btn-success btn-sm w-100 mt-2">Optimize Now</button>
                </div>

                <div class="card p-4">
                    <h6 class="fw-bold mb-3 text-uppercase small text-muted">Privacy & Visibility</h6>
                    <div class="mb-3">
                        <label class="small fw-bold">Profile Visibility</label>
                        <form action="../../api.php" method="POST">

                            <input type="hidden"
                                name="controller"
                                value="student">

                            <input type="hidden"
                                name="action"
                                value="setProfilePrivacy">

                            <select name="visibility"
                                    class="form-select form-select-sm mt-1">

                                <option value="Public"
                                <?php if(($studentData['Profile_Visibility'] ?? '') == 'Public') echo 'selected'; ?>>
                                    Public
                                </option>

                                <option value="Recruiters Only"
                                <?php if(($studentData['Profile_Visibility'] ?? '') == 'Recruiters Only') echo 'selected'; ?>>
                                    Recruiters Only
                                </option>

                                <option value="Private"
                                <?php if(($studentData['Profile_Visibility'] ?? '') == 'Private') echo 'selected'; ?>>
                                    Private
                                </option>

                            </select>

                            <button type="submit"
                                    class="btn btn-light btn-sm w-100 mt-3">

                                Save Preferences

                            </button>

                        </form>
                        
                </div>

                <h6 class="fw-bold mb-3 text-uppercase small text-muted">Status</h6>
                

                    <div class="mb-3">

                     <label class="small fw-bold">Update Status</label>

                        <form action="../../api.php"
                            method="POST">

                            <input type="hidden"
                                name="controller"
                                value="student">

                            <input type="hidden"
                                name="action"
                                value="updateStudentStatus">

                            <select name="status"
                                    class="form-select mb-3">

                                <option value="Available">

                                    Available

                                </option>

                                <option value="In-Chat">

                                    In-Chat

                                </option>

                                <option value="Offline">

                                    Offline

                                </option>

                            </select>

                            <button type="submit"
                                    class="btn btn-light btn-sm w-100 mt-3">

                                Save Status

                            </button>

                        </form>

                    </div>

                
            </div>
        </div>
    </div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <!-- <script src="../../assets/js/student_module.js"></script> -->

    <!-- Modal: Edit Profile -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Your Professional Info</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../../api.php" method="POST">
                    <div class="modal-body">
                        <!-- بيانات الكنترولر والأكشن -->
                        <input type="hidden" name="controller" value="student">
                        <input type="hidden" name="action" value="updateProfileFull">

                        <!-- تعديل الاسم -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Full Name</label>
                            <input type="text" name="user_name" class="form-control" value="<?php echo htmlspecialchars($_SESSION['User_Name']); ?>" required>
                        </div>

                        <!-- تعديل التخصص -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Major</label>
                            <input type="text" name="major" class="form-control" value="<?php echo htmlspecialchars($_SESSION['User_Major']); ?>" required>
                        </div>

                        <div class="row">
                            <!-- تعديل الـ GPA -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">GPA</label>
                                <!-- بنخلي الـ value تقرأ من السيشين لو موجودة -->
                                <input type="number"
                                    step="0.01"
                                    name="gpa"
                                    class="form-control"
                                    value="<?php echo $_SESSION['GPA'] ?? '0.00'; ?>"
                                    placeholder="e.g. 3.5">
                            </div>

                            <!-- سنة التخرج -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Class Year</label>
                                <!-- بنخلي الـ value تقرأ السنة من السيشين أو القيمة الافتراضية 2026 -->
                                <input type="number"
                                    name="class_year"
                                    class="form-control"
                                    value="<?php echo $_SESSION['Class_Year'] ?? '2026'; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal fade" id="uploadCVModal" tabindex="-1" aria-labelledby="uploadCVModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="uploadCVModalLabel">
                        Upload Resume Version
                    </h5>

                    <button type="button" 
                            class="btn-close" 
                            data-bs-dismiss="modal" 
                            aria-label="Close">
                    </button>
                </div>

                <form action="../../Controllers/studentController.php?action=updateCV" 
                    method="POST" 
                    enctype="multipart/form-data">

                    <div class="modal-body text-center">

                        <!-- اسم النسخة -->
                        <div class="mb-3">

                            <label class="form-label">
                                Resume Version Name
                            </label>

                            <input type="text"
                                name="resume_name"
                                class="form-control"
                                placeholder="Example: SWE Resume"
                                required>

                        </div>

                        <!-- رفع الملف -->
                        <div class="mb-3">

                            <label for="resumeFile" class="form-label">
                                Select PDF File
                            </label>

                            <input type="file"
                                name="resume"
                                id="resumeFile"
                                class="form-control"
                                accept=".pdf"
                                required>

                        </div>

                        <p class="text-muted small">
                            Allowed format: PDF only (Max 2MB)
                        </p>

                    </div>

                    <div class="modal-footer">

                        <button type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal">

                            Cancel

                        </button>

                        <button type="submit"
                                class="btn btn-success">

                            Upload Resume

                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>


    <div class="modal fade"
        id="addSkillModal"
        tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">

                        Add New Skill

                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                    </button>

                </div>

                <form action="../../api.php"
                    method="POST">

                    <div class="modal-body">

                        <input type="hidden"
                            name="controller"
                            value="student">

                        <input type="hidden"
                            name="action"
                            value="addNewSkill">

                        <input type="text"
                            name="skill_name"
                            class="form-control"
                            placeholder="Example: Docker"
                            required>

                    </div>

                    <div class="modal-footer">

                        <button type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal">

                            Cancel

                        </button>

                        <button type="submit"
                                class="btn btn-success">

                            Add Skill

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>


    <div class="modal fade"
        id="addProjectModal"
        tabindex="-1">

        <div class="modal-dialog modal-lg modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">

                        Add New Project

                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                    </button>

                </div>

                <form action="../../api.php"
                    method="POST"
                    enctype="multipart/form-data">

                    <div class="modal-body">

                        <input type="hidden"
                            name="controller"
                            value="student">

                        <input type="hidden"
                            name="action"
                            value="addProject">

                        <input type="text"
                            name="project_name"
                            class="form-control mb-3"
                            placeholder="Project Name"
                            required>

                        <textarea name="description"
                                class="form-control mb-3"
                                placeholder="Project Description"></textarea>

                        <input type="text"
                            name="technologies"
                            class="form-control mb-3"
                            placeholder="Technologies Used">

                        <input type="url"
                            name="project_url"
                            class="form-control mb-3"
                            placeholder="GitHub Link">

                        <input type="url"
                            name="video_url"
                            class="form-control mb-3"
                            placeholder="Video Intro Link">

                        <input type="file"
                            name="project_file"
                            class="form-control"
                            accept=".pdf">

                    </div>

                    <div class="modal-footer">

                        <button type="submit"
                                class="btn btn-success">

                            Add Project

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>


</body>

</html>