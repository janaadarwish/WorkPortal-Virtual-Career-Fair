<?php
session_start();
require_once('../../DataBase/DataBase/db_connect.php');
global $connect;

// 1. التأكد من تسجيل الدخول
if (!isset($_SESSION['User_ID'])) {
    header("Location: ../login.php");
    exit();
}

$recruiter_id = $_SESSION['User_ID'];

// 2. جلب قائمة كل الطلاب المتاحين في النظام (الـ Pipeline)
$sql = "SELECT u.User_ID, u.F_Name, u.L_Name, u.Email, s.Major 
        FROM user u
        INNER JOIN student s ON u.User_ID = s.Student_ID
        WHERE u.Role = 'Student'
        ORDER BY u.L_Name ASC";

$candidates = mysqli_query($connect, $sql);
if (!$candidates) {
    die("Error fetching candidates: " . mysqli_error($connect));
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WorkPortal | Candidate Pipeline</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --brand-slate: #022b01;
            --brand-green: #45d667;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #f8faf9;
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 260px;
            background: var(--brand-slate);
            color: white;
            padding: 1.5rem 1rem;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1000;
        }

        .sidebar h4 {
            font-weight: 800;
            line-height: 1.2;
        }

        .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.3rem;
            font-weight: 500;
            transition: 0.2s;
            text-decoration: none;
            display: block;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(69, 214, 103, 0.1);
            color: var(--brand-green) !important;
        }

        /* ===== MAIN CONTENT ===== */
        main {
            margin-left: 260px;
            padding: 2rem 3rem;
            width: calc(100% - 260px);
        }

        /* ===== STAT CARDS ===== */
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            background: white;
            padding: 1.2rem;
        }

        .table-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid #eef2f0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .metric-value {
            font-size: 1.7rem;
            font-weight: 700;
            color: #1a1d21;
        }

        /* ===== STATUS PILL ===== */
        .status-pill {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 50px;
            padding: 5px 15px;
            font-size: 0.85rem;
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        /* ===== TABLE ===== */
        .pipeline-table thead th {
            background: #f8f9fa;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6c757d;
            padding: 1rem;
            border: none;
        }

        /* ===== BADGES ===== */
        .badge-custom {
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-rated {
            background: #e3f2fd;
            color: #0d6efd;
        }

        .badge-interview {
            background: #fff3cd;
            color: #856404;
        }

        /* ===== BUTTONS ===== */
        .btn-invite {
            background-color: var(--brand-green);
            color: var(--brand-slate);
            border: none;
            transition: 0.3s;
        }

        .btn-invite:hover {
            background-color: #38c158;
            transform: scale(1.05);
        }

        .btn-action {
            font-size: 0.8rem;
            padding: 5px 12px;
            border-radius: 6px;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <!-- ===== SIDEBAR ===== -->
    <nav class="sidebar">
        <h4 class="mb-5 text-white">
            WorkPortal<br /><span style="color: var(--brand-green)">Recruiter</span>
        </h4>
        <div class="nav flex-column">
            <a href="R_recruiterDashboard.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a href="R_candidatePipeline.php" class="nav-link active"><i class="bi bi-people me-2"></i> Candidates</a>
            <a href="R_schedule.php" class="nav-link">Schedule</a>
            <a href="R_analytics.php" class="nav-link">Analytics</a>
            <a href="R_messages.php" class="nav-link">Messages</a>
            <a href="R_profile.php" class="nav-link">Profile</a>
            <a href="../../index.php" class="nav-link text-danger">Logout</a>
        </div>
    </nav>

    <!-- ===== MAIN CONTENT ===== -->
    <main>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h2 class="fw-bold mb-1">Candidate Pipeline</h2>
                <p class="text-muted mb-0">Browse students and invite high-potential candidates to your queue.</p>
                <small class="text-muted">Spring Career Expo 2026</small>
            </div>
            <div class="status-pill shadow-sm">
                <span class="status-indicator bg-success"></span>
                <strong>System Status:</strong> Operational
            </div>
        </div>

        <!-- Candidates Table Card -->
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-white py-3 px-4 border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Active Candidates</h5>
                <button class="btn btn-primary btn-sm fw-bold" onclick="sendBulkEmails()">
                    <i class="bi bi-envelope-fill me-2"></i> Send Bulk "Thank You"
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 pipeline-table">
                    <thead>
                        <tr>
                            <th class="ps-4">Candidate</th>
                            <th>Major</th>
                            <th>Email</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($candidates)): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold"><?= htmlspecialchars($row['F_Name'] . ' ' . $row['L_Name']) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark fw-normal px-3"><?= htmlspecialchars($row['Major']) ?></span>
                            </td>
                            <td class="text-muted small"><?= htmlspecialchars($row['Email']) ?></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-invite rounded-pill px-3 fw-bold"
                                        onclick="inviteToQueue('<?= $row['User_ID'] ?>', '<?= htmlspecialchars($row['F_Name']) ?>')">
                                    <i class="bi bi-lightning-charge-fill me-1"></i> Invite to Queue
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>

    <script>
    // ========== INVITE TO QUEUE ==========
    function inviteToQueue(studentId, studentName) {
        const data = new URLSearchParams();
        data.append('action', 'fast_pass');
        data.append('student_id', studentId);

        fetch('../../Controllers/recruiterController.php', {
            method: 'POST',
            body: data
        })
        .then(res => res.text())
        .then(resp => {
            console.log("Server Response:", resp);

            if (resp.trim() === "success") {
                alert(studentName + " has been added to the queue!");
            } else if (resp.trim() === "no_queue_error") {
                alert("Error: No active queue found for your booth.");
            } else {
                alert("Added to the queue!");
            }
        })
        .catch(err => {
            console.error("Error:", err);
            alert("Connection error.");
        });
    }

    // ========== SEND BULK EMAILS ==========
    function sendBulkEmails() {
        if (confirm("Send personalized 'Thank You' emails to all listed students?")) {
            const btn = document.querySelector('button[onclick="sendBulkEmails()"]');
            const originalText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';

            fetch('send_bulk_emails.php', { method: 'POST' })
            .then(response => response.text())
            .then(data => {
                alert("Success: Follow-up emails have been dispatched.");
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Failed to send emails. Check console for details.");
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }
    }
    </script>
</body>
</html>
