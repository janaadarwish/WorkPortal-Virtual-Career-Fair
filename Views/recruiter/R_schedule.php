<?php
session_start();
require_once('../../DataBase/DataBase/db_connect.php'); 

// Ensure only recruiters can access
if (!isset($_SESSION['User_ID'])) {
    header("Location: ../login.php");
    exit();
}

$recruiter_id = $_SESSION['User_ID'];

// Fetch students from the 'user' table
$sql = "SELECT DISTINCT u.User_ID, u.F_Name, u.L_Name 
        FROM user u
        WHERE u.Role = 'Student' 
        AND u.User_ID != '$recruiter_id'
        ORDER BY u.L_Name ASC";

// Using $connect as defined in your db_connect.php
$candidates = mysqli_query($connect, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorkPortal | Schedule Manager</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        :root {
            --brand-slate: #022b01;
            --brand-green: #45d667;
        }

        body {
            background-color: #f8faf9;
            font-family: 'Inter', sans-serif;
        }

        .sidebar-nav {
            width: 240px;
            background: var(--brand-slate);
            height: 100vh;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 1.5rem 1rem;
            position: fixed;
        }

        .nav-link {
            color: #adb5bd;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.2rem;
            font-weight: 500;
            transition: 0.2s;
            text-decoration: none;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(69, 214, 103, 0.1);
            color: var(--brand-green) !important;
        }

        .main-content {
            margin-left: 240px;
            padding: 2rem;
            width: calc(100% - 240px);
        }

        .calendar-container {
            background: white;
            border-radius: 16px;
            border: 1px solid #eaeef0;
            overflow: hidden;
        }

        .calendar-header {
            background: #f8faf9;
            padding: 1rem;
            border-bottom: 1px solid #eaeef0;
        }

        .time-slot {
            height: 80px; 
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            padding: 0 1rem;
            transition: 0.2s;
        }

        .event-booked {
            background: var(--brand-slate);
            color: white;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.85rem;
            margin-left: 10px;
            width: 90%;
            animation: fadeIn 0.4s ease;
        }

        .event-available {
            color: var(--brand-green);
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
        }

        .status-pill {
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 50px;
            font-weight: 700;
        }

        .status-confirmed { background: #e6fcf5; color: #0ca678; }
        .status-pending { background: #fff9db; color: #f08c00; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- NAVIGATION -->
    <nav class="sidebar-nav">
        <h4 class="mb-5">WorkPortal<br><span style="color: var(--brand-green)">Recruiter</span></h4>
        <div class="nav flex-column h-100">
            <a href="R_recruiterDashboard.php" class="nav-link">Dashboard</a>
            <a href="R_candidatePipeline.php" class="nav-link">Candidates</a>
            <a href="R_schedule.php" class="nav-link active">Schedule</a>
            <a href="R_analytics.php" class="nav-link">Analytics</a>
            <a href="R_messages.php" class="nav-link">Messages</a>
            <a href="R_profile.php" class="nav-link">Profile</a>
            <a href="../../index.php" class="nav-link text-danger mt-auto">Logout</a>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="card border-0 shadow-sm p-3"><small class="text-muted fw-bold">Scheduled</small><h3 class="mb-0" id="stat-scheduled">12</h3></div></div>
            <div class="col-md-3"><div class="card border-0 shadow-sm p-3"><small class="text-muted fw-bold">Pending</small><h3 class="mb-0 text-warning">4</h3></div></div>
            <div class="col-md-3"><div class="card border-0 shadow-sm p-3"><small class="text-muted fw-bold">Completed</small><h3 class="mb-0 text-success">28</h3></div></div>
            <div class="col-md-3"><div class="card border-0 shadow-sm p-3"><small class="text-muted fw-bold">Canceled</small><h3 class="mb-0 text-danger">2</h3></div></div>
        </div>

        <div class="row">
            <!-- CALENDAR -->
            <div class="col-lg-8">
                <div class="calendar-container shadow-sm">
                    <div class="calendar-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Monday, April 27</h5>
                        <button class="btn btn-sm btn-outline-secondary active">Day View</button>
                    </div>
                    
                    <div class="calendar-body">
                        <div class="time-slot" data-time="09:00 AM">
                            <span class="text-muted small w-25">09:00 AM</span>
                            <span class="event-available">+ Open for booking</span>
                        </div>
                        <div class="time-slot" data-time="10:00 AM">
                            <span class="text-muted small w-25">10:00 AM</span>
                            <div class="event-booked shadow-sm"><strong>Sarah Jenkins</strong> • Technical Interview</div>
                        </div>
                        <div class="time-slot" data-time="11:00 AM">
                            <span class="text-muted small w-25">11:00 AM</span>
                            <span class="event-available">+ Open for booking</span>
                        </div>
                        <div class="time-slot" data-time="01:00 PM">
                            <span class="text-muted small w-25">01:00 PM</span>
                            <span class="event-available">+ Open for booking</span>
                        </div>
                        <div class="time-slot" data-time="02:00 PM">
                            <span class="text-muted small w-25">02:00 PM</span>
                            <span class="event-available">+ Open for booking</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOOKING FORM -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm p-4 mb-4">
                    <h6 class="fw-bold mb-3">Book New Interview</h6>
                    <div class="mb-3">
                        <label class="small fw-bold">Select Candidate</label>
                        <select class="form-select bg-light border-0" id="candidateName">
                            <?php if($candidates && mysqli_num_rows($candidates) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($candidates)): ?>
                                    <option value="<?= htmlspecialchars($row['F_Name'].' '.$row['L_Name']) ?>">
                                        <?= htmlspecialchars($row['F_Name'].' '.$row['L_Name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option disabled>No students found</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Select Time</label>
                        <select class="form-select bg-light border-0" id="interviewTime">
                            <option value="09:00 AM">09:00 AM</option>
                            <option value="11:00 AM">11:00 AM</option>
                            <option value="01:00 PM">01:00 PM</option>
                            <option value="02:00 PM">02:00 PM</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Interview Type</label>
                        <div class="d-flex gap-2">
                            <input type="radio" class="btn-check" name="type" id="t1" value="Tech" checked>
                            <label class="btn btn-sm btn-outline-dark" for="t1">Tech</label>
                            <input type="radio" class="btn-check" name="type" id="t2" value="HR">
                            <label class="btn btn-sm btn-outline-dark" for="t2">HR</label>
                        </div>
                    </div>
                    <button class="btn btn-success w-100 fw-bold py-2" id="addEventBtn" style="background: #45d667; color: #022b01; border:none;">Schedule Interview</button>
                </div>

                <h6 class="fw-bold mb-3">Live Status</h6>
                <div id="side-list">
                    <!-- Dynamic updates appear here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('addEventBtn').addEventListener('click', function() {
    const nameInput = document.getElementById('candidateName').value;
    const timeInput = document.getElementById('interviewTime').value;
    const typeInput = document.querySelector('input[name="type"]:checked').value;
    const targetSlot = document.querySelector(`.time-slot[data-time="${timeInput}"]`);
    
    if (targetSlot) {
        const isAvailable = targetSlot.querySelector('.event-available');
        
        if (isAvailable) {
            // Update Calendar
            targetSlot.innerHTML = `
                <span class="text-muted small w-25">${timeInput}</span>
                <div class="event-booked shadow-sm">
                    <strong>${nameInput}</strong> • ${typeInput} Interview
                </div>
            `;

            // Update Side List
            const sideList = document.getElementById('side-list');
            const entry = document.createElement('div');
            entry.className = "card border-0 shadow-sm p-3 mb-2";
            entry.innerHTML = `
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-bold">${nameInput}</div>
                        <small class="text-muted">${timeInput} • ${typeInput}</small>
                    </div>
                    <span class="status-pill status-pending">Pending</span>
                </div>
            `;
            sideList.prepend(entry);

            // Update Stat
            const countEl = document.getElementById('stat-scheduled');
            countEl.innerText = parseInt(countEl.innerText) + 1;

            alert("Interview Scheduled Successfully!");
        } else {
            alert("This slot is already occupied.");
        }
    }
});
</script>
</body>
</html>