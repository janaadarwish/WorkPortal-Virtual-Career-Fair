<?php
session_start(); 

$saved_name = isset($_SESSION['User_Name']) ? $_SESSION['User_Name'] : "Student";
$saved_major = isset($_SESSION['User_Major']) ? $_SESSION['User_Major'] : "Information Systems";


include "../../DataBase/DataBase/db_connect.php";

if (!isset($_SESSION['User_ID'])) {
    header("Location: ../login.php");
    exit();
}?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | WorkPortal</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" />

    <style>
        :root {
            --brand-green: #28a745;
            --brand-green-hover: #218838;
            --brand-slate: #001d3f;
            --bg-light: #f4f7f6;
            --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        .sidebar {
            width: 260px;
            background: var(--brand-slate);
            color: white;
            padding: 2rem 1.5rem;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }

        .sidebar h4 {
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 2.5rem;
        }

        .nav-link {
            color: #adb5bd;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.4rem;
            font-weight: 500;
            transition: 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(40, 167, 69, 0.15);
            color: var(--brand-green) !important;
        }

        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            min-height: 100vh;
        }

        .top-nav {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            background: white;
            margin-bottom: 1.5rem;
        }

        .queue-hero {
            background: linear-gradient(135deg, var(--brand-slate) 0%, #003366 100%);
            color: white;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }

        .queue-hero::after {
            content: "";
            position: absolute;
            top: -20px;
            right: -20px;
            width: 100px;
            height: 100px;
            background: var(--brand-green);
            opacity: 0.2;
            border-radius: 50%;
        }

        .pos-circle {
            width: 65px;
            height: 65px;
            border: 3px solid var(--brand-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 800;
        }

        .btn-green {
            background: var(--brand-green);
            color: white;
            font-weight: 600;
            border-radius: 10px;
            padding: 10px 20px;
            border: none;
        }

        .btn-green:hover {
            background: var(--brand-green-hover);
            color: white;
        }

        .match-score {
            background: rgba(40, 167, 69, 0.1);
            color: var(--brand-green);
            font-weight: 700;
            font-size: 0.8rem;
            padding: 4px 10px;
            border-radius: 20px;
        }

        .skill-tag {
            background: #f0f2f5;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #495057;
            margin-right: 5px;
        }

        .activity-dot {
            height: 8px;
            width: 8px;
            background: #dee2e6;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
        }

        .activity-dot.active {
            background: var(--brand-green);
        }
    </style>
</head>

<body data-student-id="<?php echo isset($_SESSION['User_ID']) ? (int)$_SESSION['User_ID'] : 1; ?>">

    <div class="d-flex">
        <nav class="sidebar">
            <h4 class="fw-bold mb-5">
                <span class="text-white">WorkPortal</span> <br />
                <span class="text-success" style="font-size: 1.2rem;">Student</span>
            </h4>
            <div class="nav flex-column">
                <a href="S_studentDashboard.php" class="nav-link active"> Dashboard</a>
                <a href="S_fair.php" class="nav-link"> Browse Fairs</a>
                <a href="S_liveSessions.php" class="nav-link">Live Interview & Chat Session</a>
                <a href="S_profile.php" class="nav-link"> Profile</a>
                <a href="../../index.php" class="nav-link text-danger mt-auto"> Logout</a>
            </div>
        </nav>

        <div class="main-content">

            <div class="top-nav">
                <div>
                    <h4 class="fw-bold mb-0">Student Command Center</h4>
                    <small class="text-muted">Spring Tech Career Fair 2026 • <span class="text-success fw-bold">Live</span></small>
                </div>
                <div class="d-flex align-items-center gap-4">
                    <button type="button" 
                            id="btn-update-resume" 
                            class="btn btn-outline-dark btn-sm rounded-pill px-3"
                            data-bs-toggle="modal" 
                            data-bs-target="#uploadCVModal">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Update Resume
                    </button>
                </div>
            </div>

            <!-- Success messages -->
            <?php if (isset($_SESSION['success_msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 12px; background-color: #d1e7dd; color: #0f5132;">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Done!</strong> <?php echo htmlspecialchars($_SESSION['success_msg']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_msg']); ?>
            <?php endif; ?>

            <!-- Error messages -->
            <?php if (isset($_SESSION['error_msg'])): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 12px;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Oops!</strong> <?php echo htmlspecialchars($_SESSION['error_msg']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_msg']); ?>
            <?php endif; ?>

            <div class="container-fluid p-4">
                <div class="row">

                    <!-- ========== LEFT COLUMN (col-lg-8) ========== -->
                    <div class="col-lg-8">

                        <?php
                        $student_id = $_SESSION['User_ID'];
                        $active_queue_query = "SELECT q.*, j.Job_Title, c.Company_Name 
                                            FROM queue q 
                                            JOIN waits_in wi ON q.Queue_ID = wi.Queue_ID 
                                            JOIN job j ON q.Job_ID = j.Job_ID 
                                            JOIN company c ON j.Company_ID = c.Company_ID 
                                            WHERE wi.Student_ID = '$student_id' 
                                            AND q.Status IN ('Waiting', 'In Progress')
                                            ORDER BY q.Entry_Time DESC LIMIT 1";

                        $active_res = mysqli_query($connect, $active_queue_query);
                        $active_data = mysqli_fetch_assoc($active_res);

                        if ($active_data): 
                            $job_id = $active_data['Job_ID'];
                            $position = str_pad(
                                $active_data['Position_No'],
                                2,
                                '0',
                                STR_PAD_LEFT
                            );

                            $est_wait = $active_data['Wait_Time'];
                        ?>
                            <div class="card queue-hero">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-success">ACTIVE QUEUE</span>
                                            <small class="opacity-75">• Est. Wait: <?php echo $est_wait; ?> Mins</small>
                                        </div>
                                        
                                        <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($active_data['Company_Name']); ?></h2>
                                        <p class="opacity-75 mb-4"><?php echo htmlspecialchars($active_data['Job_Title']); ?> Interview</p>
                                        
                                        <div class="d-flex gap-2">
                                            <?php if ($active_data['Status'] == 'In Progress'): ?>

                                                <a href="S_liveSessions.php"
                                                class="btn btn-green">

                                                🚀 Enter Interview Room

                                                </a>

                                            <?php else: ?>

                                                <button type="button"
                                                        class="btn btn-secondary"
                                                        disabled>

                                                    Waiting For Your Turn

                                                </button>

                                            <?php endif; ?>
                                            <a href="../../Controllers/queueController.php?action=leave" 
                                            id="btn-leave-queue" class="btn btn-outline-light border-0">
                                                Leave Queue
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 text-center border-start border-white border-opacity-10">
                                        <div class="pos-circle mx-auto mb-2">#<?php echo $position; ?></div>
                                        <p class="small mb-0 opacity-75">Your Position</p>
                                        <span class="badge bg-white text-dark mt-2">
                                            Status: <?php echo htmlspecialchars($active_data['Status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- ✅ AI Match Suggestions — inside col-lg-8 -->
                        <div class="d-flex justify-content-between align-items-end mb-3">
                            <h5 class="fw-bold mb-0">✨ AI Match Suggestions</h5>
                            <a href="#" class="small text-success fw-bold text-decoration-none">View All</a>
                        </div>

                        <div class="row g-3 mb-4">
                            <?php 
                            $query = "SELECT c.*, j.Job_ID, j.Job_Title 
                                    FROM company c 
                                    JOIN job j ON c.Company_ID = j.Company_ID 
                                    WHERE 1 
                                    GROUP BY c.Company_ID";

                            $result = mysqli_query($connect, $query);

                            while ($data = mysqli_fetch_assoc($result)): 
                                $current_job_id = $data['Job_ID'];

                                $count_queue_sql = "SELECT COUNT(*) as total
                                                    FROM queue
                                                    WHERE Job_ID = '$current_job_id'
                                                    AND Status IN ('Waiting', 'In Progress')";

                                $count_queue_res = mysqli_query($connect, $count_queue_sql);

                                $count_queue_data = mysqli_fetch_assoc($count_queue_res);

                                $queue_count = $count_queue_data['total'];


                                
                                 $check_q = "SELECT q.Queue_ID FROM queue q 
                                            JOIN waits_in wi ON q.Queue_ID = wi.Queue_ID 
                                            WHERE wi.Student_ID = '$student_id' 
                                            AND q.Job_ID = '$current_job_id' 
                                            AND q.Status IN ('Waiting', 'In Progress')";
                                            
                                $res_check = mysqli_query($connect, $check_q);
                                $is_waiting = mysqli_num_rows($res_check) > 0;
                            ?>
                                <div class="col-md-6">
                                    <div class="card p-3 h-100 shadow-sm border-0" style="border-radius: 15px;">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="badge bg-light text-success rounded-pill px-3"><?php echo rand(85, 95); ?>% Match</span>
                                            <i class="bi bi-bookmark text-muted"></i>
                                        </div>
                                        
                                        <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($data['Company_Name']); ?></h6>
                                        
                                        <p class="text-success small mb-1 fw-bold">
                                            <i class="bi bi-briefcase me-1"></i><?php echo htmlspecialchars($data['Job_Title']); ?>
                                        </p>
                                        
                                        <p class="text-muted small mb-3">
                                            <?php echo htmlspecialchars($data['Industry']); ?> • <?php echo htmlspecialchars($data['Location']); ?>
                                        </p>
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-auto">
                                            <small class="text-muted"><i class="bi bi-people me-1"></i> Queue: <?php echo $queue_count; ?></small>
                                            
                                            <?php if ($is_waiting): ?>
                                                <a href="../../Controllers/queueController.php?action=leave" 
                                                class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm">
                                                    <i class="bi bi-box-arrow-left me-1"></i> Leave Queue
                                                </a>
                                            <?php else: ?>
                                                <a href="../../Controllers/queueController.php?action=join&company_id=<?php echo $data['Company_ID']; ?>&job_id=<?php echo $data['Job_ID']; ?>" 
                                                class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm">
                                                    Join Queue
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <!-- Your Schedule -->
                        <div class="card p-4">
                            <h5 class="fw-bold mb-4">Your Schedule</h5>
                            <div class="d-flex align-items-center p-3 border rounded-4 mb-3 bg-light bg-opacity-50">
                                <div class="text-center me-4">
                                    <h4 class="fw-bold mb-0">14:30</h4>
                                    <small class="text-muted">Today</small>
                                </div>
                                <div class="flex-grow-1 border-start ps-4">
                                    <h6 class="fw-bold mb-0">Microsoft Q&A Session</h6>
                                    <p class="small text-muted mb-0">Live Group Technical Session • <span class="text-primary">Confirmed</span></p>
                                </div>
                                <button class="btn btn-dark btn-sm rounded-pill px-3">Join</button>
                            </div>
                            <div class="d-flex align-items-center p-3 border rounded-4 opacity-75">
                                <div class="text-center me-4">
                                    <h4 class="fw-bold mb-0">16:00</h4>
                                    <small class="text-muted">Today</small>
                                </div>
                                <div class="flex-grow-1 border-start ps-4">
                                    <h6 class="fw-bold mb-0">Google 1-on-1 Interview</h6>
                                    <p class="small text-muted mb-0">HR Screening • <span class="text-warning">Pending</span></p>
                                </div>
                                <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" disabled>Wait</button>
                            </div>
                        </div>

                    </div>
                    <!-- ========== END col-lg-8 ========== -->

                    <!-- ========== RIGHT COLUMN (col-lg-4) ========== -->
                    <div class="col-lg-4">

                        <div class="card p-4 mb-4" style="border-top: 4px solid var(--brand-green);">
                            <h6 class="text-uppercase fw-bold small text-muted mb-3">Profile Completion</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="h5 fw-bold mb-0">85%</span>
                                <span class="small text-success fw-bold">Good</span>
                            </div>
                            <div class="progress mb-3" style="height: 7px;">
                                <div class="progress-bar bg-success" style="width: 85%"></div>
                            </div>
                            <p class="small text-muted mb-3">💡 Missing skills for top matches:</p>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <span class="skill-tag">Docker</span>
                                <span class="skill-tag">Kubernetes</span>
                                <span class="skill-tag">AWS</span>
                            </div>
                            <a href="S_profile.php">
                                <button type="button" id="btn-optimize-profile" class="btn btn-outline-dark btn-sm w-100 py-2">Optimize Profile</button>
                            </a>
                        </div>

                        <div class="card p-4 mb-4">
                            <h6 class="text-uppercase fw-bold small text-muted mb-3">Invitations (2)</h6>
                            <div class="pb-3 border-bottom mb-3">
                                <p class="small fw-bold mb-1">Amazon Recruiter</p>
                                <p class="x-small text-muted mb-2">"Your React project looks great, want to chat?"</p>
                                <div class="d-flex gap-2">
                                    <button type="button" id="btn-invitation-accept" class="btn btn-green btn-sm px-3">Accept</button>
                                    <button type="button" id="btn-invitation-decline" class="btn btn-light btn-sm">Decline</button>
                                </div>
                            </div>
                            <div class="">
                                <p class="small fw-bold mb-1">Netflix Engineering</p>
                                <p class="x-small text-muted mb-0">Requested your full portfolio.</p>
                            </div>
                        </div>

                        <div class="card p-4 mb-4">
                            <h6 class="text-uppercase fw-bold small text-muted mb-3">Resources</h6>
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action border-0 px-0 small"><i class="bi bi-map me-2"></i> Interactive Fair Map</a>
                                <a href="#" class="list-group-item list-group-item-action border-0 px-0 small"><i class="bi bi-headset me-2"></i> Technical Support</a>
                                <a href="#" class="list-group-item list-group-item-action border-0 px-0 small"><i class="bi bi-journal-check me-2"></i> Interview Tips PDF</a>
                            </div>
                        </div>

                        <div class="card p-4">
                            <h6 class="text-uppercase fw-bold small text-muted mb-3">Activity Log</h6>
                            <div class="small">
                                <div class="mb-3">
                                    <span class="activity-dot active"></span>
                                    <strong>Joined Queue:</strong> Adobe Inc.
                                    <div class="text-muted x-small ps-3">10 mins ago</div>
                                </div>
                                <div class="mb-3">
                                    <span class="activity-dot"></span>
                                    <strong>Uploaded:</strong> Portfolio_v2.pdf
                                    <div class="text-muted x-small ps-3">1 hour ago</div>
                                </div>
                                <div>
                                    <span class="activity-dot"></span>
                                    <strong>Visited:</strong> Apple Booth
                                    <div class="text-muted x-small ps-3">2 hours ago</div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- ========== END col-lg-4 ========== -->

                </div>
                <!-- ========== END row ========== -->
            </div>
        </div>
    </div>

    <script>
        (function() {
            const stored = sessionStorage.getItem('workportal_user');
            if (!stored) return;
            try {
                const user = JSON.parse(stored);
                if (user.role === 'student' && !document.body.dataset.studentId) {
                    document.body.dataset.studentId = user.id;
                }
                const nameEl = document.getElementById('dashboard-user-name');
                const majorEl = document.getElementById('dashboard-user-major');
                if (nameEl) nameEl.innerText = user.name || 'Student';
                if (majorEl) majorEl.innerText = user.major || 'Student';
            } catch (e) {
                console.error('Failed to load user session', e);
            }
        })();
    </script>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>

    <!-- Upload CV Modal -->
    <div class="modal fade" id="uploadCVModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Update Your Resume</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form action="../../Controllers/studentController.php?action=updateCV" method="POST" enctype="multipart/form-data">
            <div class="modal-body text-center">
                <input type="file" name="resume" class="form-control" accept=".pdf" required>
                <p class="text-muted small mt-2">Only PDF files are allowed.</p>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-dark">Upload Now</button>
            </div>
          </form>
        </div>
      </div>
    </div>

</body>
</html>