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

// 2. جلب معلومات الريكروتر والبوث
$stmt = mysqli_prepare($connect, "SELECT Booth_No, Company_ID FROM recruiter WHERE Recruiter_ID = ?");
mysqli_stmt_bind_param($stmt, "s", $recruiter_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$rec_info = mysqli_fetch_assoc($res);
$my_booth = $rec_info['Booth_No'] ?? 1;
$my_company = $rec_info['Company_ID'] ?? 0;

// 3. جلب العداد (كم طالب في الطابور حالياً)
// 4. جلب عدد الطلاب في آخر طابور مفعل لهذا البوث
$sql_count = "SELECT COUNT(*) as total FROM waits_in 
              WHERE Queue_ID = (SELECT Queue_ID FROM queue 
                                WHERE Booth_No = '$my_booth' 
                                ORDER BY Queue_ID DESC LIMIT 1)";
$res_count = mysqli_query($connect, $sql_count);
$row_count = mysqli_fetch_assoc($res_count);
$current_waiting = $row_count['total'] ?? 0;

// 4. جلب قائمة الطلاب (التعديل الذي يوحد القائمة مع العداد)
$sql = "SELECT 
            u.F_Name, u.L_Name, u.User_ID,
            s.Student_ID, s.Major, s.Resume_URL, 
            q.Queue_ID, 
            q.Wait_Time,
            w.Is_Priority 
        FROM waits_in w
        JOIN student s ON w.Student_ID = s.Student_ID
        JOIN user u ON s.Student_ID = u.User_ID
        JOIN queue q ON w.Queue_ID = q.Queue_ID
        WHERE q.Booth_No = '$my_booth' 
        ORDER BY w.Is_Priority DESC, w.Student_ID ASC";

$queue_result = mysqli_query($connect, $sql);

if (!$queue_result) {
    die("خطأ في جلب البيانات: " . mysqli_error($connect));
}

// 5. جلب قائمة الطلاب المنتظرين (لأغراض إضافية إن وجدت)
$queue_list = mysqli_query($connect, $sql); 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WorkPortal | Dashboard</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Visual feedback when paused */
        .queue-column.paused {
            background-color: #fceaea;
            opacity: 0.8;
        }

        .paused-overlay {
            display: none;
            position: absolute;
            top: 60px;
            left: 0;
            right: 0;
            background: #dc3545;
            color: white;
            text-align: center;
            font-size: 0.8rem;
            padding: 5px;
            z-index: 10;
        }

        :root {
            --brand-slate: #022b01;
            --brand-green: #45d667;
            --sidebar-width: 260px;
            --queue-width: 350px;
        }

        body {
            background-color: #f8faf9;
            font-family: 'Segoe UI', sans-serif;
            height: 100vh;
            overflow: hidden;
        }

        .dashboard-wrapper {
            display: flex;
            height: 100vh;
            width: 100vw;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--brand-slate);
            color: white;
            padding: 1.5rem 1rem;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 0.8rem 1rem;
            border-radius: 10px;
            margin-bottom: 0.3rem;
            text-decoration: none;
            transition: 0.2s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: var(--brand-green) !important;
        }

        /* Queue Styles */
        .queue-column {
            width: var(--queue-width);
            background: white;
            border-right: 1px solid #dee2e6;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid #eef2f0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .candidate-item {
            padding: 1.25rem;
            border-bottom: 1px solid #f0f2f5;
            cursor: pointer;
            transition: 0.2s;
        }

        .candidate-item.selected {
            background: rgba(69, 214, 103, 0.08);
            border-left: 4px solid var(--brand-green);
        }

        .candidate-item:hover {
            border-color: var(--brand-green);
            transform: translateY(-2px);
        }

        .priority-badge {
            background: #fff8e1;
            color: #ff8f00;
            font-size: 0.7rem;
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            font-weight: 700;
        }

        .btn-archive {
            color: #0d6efd;
            border-color: #0d6efd;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-archive:hover {
            background: #0d6efd;
            color: white;
        }

        /* Main Content */
        main {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .candidate-detail {
            padding: 2rem;
            overflow-y: auto;
            flex-grow: 1;
        }

        .btn-green {
            background-color: var(--brand-green);
            color: #022b01 !important;
            font-weight: bold;
            border: none;
        }

        .resume-card {
            border: 2px dashed #dee2e6;
            border-radius: 15px;
            background-color: #fff;
        }
    </style>
</head>

<body>
    <div class="dashboard-wrapper">

        <!-- ===== SIDEBAR ===== -->
        <nav class="sidebar">
            <h4 class="mb-5 fw-bold text-white">
                WorkPortal <span style="color: var(--brand-green)">Recruiter</span>
            </h4>
            <div class="nav flex-column h-100">
                <a href="R_recruiterDashboard.php" class="nav-link active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a href="R_candidatePipeline.php" class="nav-link"><i class="bi bi-people me-2"></i> Candidates</a>
                <a href="R_schedule.php" class="nav-link">Schedule</a>
                <a href="R_analytics.php" class="nav-link">Analytics</a>
                <a href="R_messages.php" class="nav-link">Messages</a>
                <a href="R_profile.php" class="nav-link">Profile</a>
                <a href="../../index.php" class="nav-link text-danger">Logout</a>
            </div>
        </nav>

        <!-- ===== QUEUE COLUMN ===== -->
        <section class="queue-column">
            <div class="p-4 border-bottom bg-light">
                <h5 class="fw-bold mb-0">Live Queue</h5>
                <!-- Active Queue Stat Card -->
                <div class="stat-card mt-3 mb-2">
                    <div class="text-muted small fw-bold mb-2">ACTIVE QUEUE</div>
                    <div class="d-flex align-items-center justify-content-between">
                        <h2 class="fw-bold m-0" id="queue-count"><?= $current_waiting ?></h2>
                        <span class="badge bg-success-subtle text-success rounded-pill">Live</span>
                    </div>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: <?= min(($current_waiting/10)*100, 100) ?>%"></div>
                    </div>
                </div>
                <!-- Queue Pause Toggle -->
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" id="queuePauseToggle" checked>
                    <label class="form-check-label small fw-bold" for="queuePauseToggle" id="statusLabel">Active</label>
                </div>
            </div>

            <div class="overflow-auto flex-grow-1" id="candidate-list">
                <?php while ($row = mysqli_fetch_assoc($queue_result)):
                    $fullName = htmlspecialchars($row['F_Name'] . ' ' . $row['L_Name']); ?>
                    <div class="candidate-item"
                        data-id="<?= $row['Student_ID']; ?>"
                        data-queue-id="<?= $row['Queue_ID']; ?>"
                        data-name="<?= $fullName ?>"
                        data-role="<?= htmlspecialchars($row['Major']) ?>"
                        data-resume="<?= htmlspecialchars($row['Resume_URL']) ?>"
                        data-priority="<?= $row['Is_Priority']; ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-1">
                                <?= $fullName ?>
                                <span class="priority-star-icon" style="color: gold; display: <?= $row['Is_Priority'] ? 'inline' : 'none' ?>;"> ★</span>
                            </h6>
                            <span class="badge rounded-pill bg-light text-dark border"><?= $row['Wait_Time'] ?>m</span>
                        </div>
                        <p class="small text-muted mb-0"><?= htmlspecialchars($row['Major']) ?></p>
                        <?php if($row['Is_Priority']): ?>
                            <span class="priority-badge">VIP - Fast Pass</span>
                        <?php endif; ?>
                        <!-- Archive Transcript Button -->
                        <div class="d-flex gap-2 mt-2">
                            <button class="btn btn-outline-primary btn-archive" 
                                    onclick="openQuickChat('<?= $row['Queue_ID'] ?>', '<?= $row['F_Name'] ?>')" 
                                    title="Archive Transcript">
                                <i class="bi bi-chat-text"></i>
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>

                <!-- Download All Resumes -->
                <div class="mb-6 p-4 border-top bg-light d-flex justify-content-center">
                    <a href="../../Models/download_all_resumes.php" class="btn btn-dark fw-bold shadow-sm">
                        <i class="bi bi-download"></i> 📥 Download All Resumes (.zip)
                    </a>
                </div>
            </div>
        </section>

        <!-- ===== MAIN CONTENT ===== -->
        <main>
            <header class="bg-white p-3 border-bottom d-flex justify-content-between align-items-center shadow-sm">
                <div>
                    <h2 class="fw-bold mb-1">Welcome Back!</h2>
                    <p class="text-muted mb-0">You are managing Booth #<?= $my_booth ?></p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button id="flag-btn" class="btn btn-outline-danger btn-sm fw-bold">🚩 Flag Candidate</button>
                    <!-- Extension Button (Hidden by default) -->
                    <button id="extend-btn" class="btn btn-warning btn-sm fw-bold" style="display: none;">
                        +2 Min Extension
                    </button>
                    <div id="timer-display" class="badge bg-danger px-3 py-2 fs-6">05:00</div>
                </div>
            </header>

            <div class="candidate-detail">
                <div class="card p-4 shadow-sm border-0 rounded-4">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h1 class="fw-bold mb-1" id="display-name">Select a Candidate</h1>
                            <p class="text-muted fs-5" id="display-role">Waiting...</p>
                        </div>
                        <button id="main-action-btn" class="btn btn-green btn-lg px-4 rounded-3">Start Interview Chat</button>
                    </div>

                    <!-- Resume Preview -->
                    <div class="card p-3 mb-4 border-light bg-light">
                        <div class="resume-card p-4 text-center">
                            <h6 class="fw-bold mt-2" id="resume-filename-display">No Resume Selected</h6>
                            <a href="#" target="_blank" id="dashboard-preview-btn" class="btn btn-sm btn-dark px-4 disabled mt-3">Preview PDF</a>
                        </div>
                    </div>

                    <!-- Evaluation Form -->
                    <div id="evaluation-zone" style="display: none;" class="bg-light p-4 rounded-4 mb-3 border shadow-sm">
                        <h5 class="fw-bold mb-3">Candidate Assessment</h5>

                        <?php
                        $criteria = [
                            'comm_score'    => 'Communication Skills',
                            'tech_score'    => 'Technical Proficiency',
                            'culture_score' => 'Culture Fit'
                        ];
                        foreach ($criteria as $key => $label): ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted"><?= $label ?></label>
                                <div class="d-flex gap-3">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <label class="btn btn-outline-secondary btn-sm">
                                            <input type="radio" name="<?= $key ?>" value="<?= $i ?>"> <?= $i ?>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <h6 class="fw-bold small text-muted text-uppercase mb-2 border-top pt-3">Final Decision</h6>
                        <div class="d-flex gap-3 mb-3">
                            <label><input type="radio" name="rating" value="1"> 🔴 Reject</label>
                            <label><input type="radio" name="rating" value="2"> 🟡 Maybe</label>
                            <label><input type="radio" name="rating" value="3"> 🟢 Shortlist</label>
                        </div>
                    </div>

                    <!-- Quick Eval Notes (from original dashboard) -->
                    <div id="eval-form" style="display:none;" class="mb-3">
                        <h5 class="fw-bold text-success mb-3">Interviewing: <span id="st-name"></span></h5>
                        <button class="btn btn-success w-100 py-3 fw-bold rounded-3 mb-3" onclick="submitEvaluation()">Save & Complete</button>
                    </div>

                    <h6 class="fw-bold small text-muted text-uppercase mb-2">Private Recruiter Notes</h6>
                    <textarea id="eval-notes" class="form-control mb-3 bg-light border-0 p-3" rows="5" placeholder="Add observations or interview notes..."></textarea>
                </div>
            </div>
        </main>
    </div>

    <script>
    // ========== SHARED STATE ==========
    let currentStudentId = null;
    let currentStatus = "IDLE"; // IDLE, CHATTING, EVALUATING
    let timerInterval;
    let timeLeft = 300;
    let extensionUsed = false;

    // ========== UI ELEMENTS ==========
    const candidates    = document.querySelectorAll('.candidate-item');
    const mainBtn       = document.getElementById('main-action-btn');
    const flagBtn       = document.getElementById('flag-btn');
    const extendBtn     = document.getElementById('extend-btn');
    const timerDisplay  = document.getElementById('timer-display');
    const evalZone      = document.getElementById('evaluation-zone');
    const notesArea     = document.getElementById('eval-notes');
    const previewBtn    = document.getElementById('dashboard-preview-btn');
    const resumeDisplay = document.getElementById('resume-filename-display');

    // ========== 1. SELECTION LOGIC ==========
    candidates.forEach(item => {
        item.addEventListener('click', function() {
            if (currentStatus !== "IDLE") {
                alert("Please complete the current evaluation first.");
                return;
            }
            candidates.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');

            currentStudentId = this.getAttribute('data-id');
            document.getElementById('display-name').innerText = this.getAttribute('data-name');
            document.getElementById('display-role').innerText = this.getAttribute('data-role');
            document.getElementById('st-name').innerText = this.getAttribute('data-name');

            const resumePath = this.getAttribute('data-resume');
            if (resumePath) {
                resumeDisplay.innerText = resumePath.split('/').pop();
                previewBtn.href = window.location.origin + "/WorkPortal/uploads/resumes/" + resumePath;
                previewBtn.classList.remove('disabled');
            } else {
                resumeDisplay.innerText = "No Resume Uploaded";
                previewBtn.classList.add('disabled');
            }

            updateFlagButtonUI(this.getAttribute('data-priority') === "1");
            resetActionInterface();
        });
    });

    // ========== 2. ARCHIVE TRANSCRIPT (from original dashboard) ==========
    function openQuickChat(qId, name) {
        let msg = prompt("Archive message for " + name + ":");
        if (msg) {
            const params = new URLSearchParams({ action: 'archive_transcript', queue_id: qId, message: msg });
            fetch('../../Controllers/recruiterController.php', { method: 'POST', body: params })
            .then(res => res.text())
            .then(data => { if(data.trim()==="success") alert("Transcript Archived!"); });
        }
    }

    // ========== 3. TIMER & EXTENSION LOGIC ==========
    function startTimer() {
        timeLeft = 300;
        extensionUsed = false;
        extendBtn.style.display = "none";
        clearInterval(timerInterval);

        timerInterval = setInterval(() => {
            timeLeft--;
            let mins = Math.floor(timeLeft / 60);
            let secs = timeLeft % 60;
            timerDisplay.innerText = `${mins.toString().padStart(2,'0')}:${secs.toString().padStart(2,'0')}`;

            if (timeLeft <= 60 && timeLeft > 0 && !extensionUsed) {
                extendBtn.style.display = "block";
                extendBtn.className = "btn btn-warning btn-sm fw-bold animate-pulse";
            }

            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                timerDisplay.innerText = "00:00";
                alert("Time is up! Please move to the evaluation phase.");
                if (currentStatus === "CHATTING") mainBtn.click();
            }
        }, 1000);
    }

    extendBtn.addEventListener('click', function() {
        const selected = document.querySelector('.candidate-item.selected');
        if (!selected) return;
        const queueId = selected.getAttribute('data-queue-id');

        timeLeft += 120;
        extensionUsed = true;
        extendBtn.style.display = "none";

        timerDisplay.classList.replace('bg-danger', 'bg-success');
        setTimeout(() => timerDisplay.classList.replace('bg-success', 'bg-danger'), 2000);

        fetch('../../Models/extend_session_instant.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ 'queue_id': queueId })
        });

        alert("Session extended by 2 minutes!");
    });

    // ========== 4. FLAG TOGGLE LOGIC ==========
    flagBtn.addEventListener('click', function() {
        const selected = document.querySelector('.candidate-item.selected');
        if (!selected) return;

        fetch('../../Models/toggle_flag.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ 'student_id': selected.getAttribute('data-id') })
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === "Success") {
                const newVal = selected.getAttribute('data-priority') === "1" ? "0" : "1";
                selected.setAttribute('data-priority', newVal);
                selected.querySelector('.priority-star-icon').style.display = (newVal === "1") ? "inline" : "none";
                updateFlagButtonUI(newVal === "1");
            }
        });
    });

    function updateFlagButtonUI(isPriority) {
        flagBtn.className = isPriority ? "btn btn-danger btn-sm fw-bold" : "btn btn-outline-danger btn-sm fw-bold";
        flagBtn.innerText = isPriority ? "🚩 Flagged High Priority" : "🚩 Flag Candidate";
    }

    // ========== 5. MAIN ACTION CONTROLLER ==========
    mainBtn.addEventListener('click', function() {
        if (currentStatus === "IDLE") {
            currentStatus = "CHATTING";
            mainBtn.innerText = "End & Evaluate";
            mainBtn.className = "btn btn-danger btn-lg px-4 rounded-3";
            document.getElementById('eval-form').style.display = 'block';
            startTimer();
        } else if (currentStatus === "CHATTING") {
            currentStatus = "EVALUATING";
            clearInterval(timerInterval);
            mainBtn.innerText = "Submit Final Evaluation";
            mainBtn.className = "btn btn-primary btn-lg px-4 rounded-3";
            evalZone.style.display = "block";
            extendBtn.style.display = "none";
        } else if (currentStatus === "EVALUATING") {
            submitEvaluation();
        }
    });

    // ========== 6. EVALUATION SUBMISSION ==========
    function submitEvaluation() {
        const selected = document.querySelector('.candidate-item.selected');
        if (!selected) { alert("No candidate selected."); return; }

        const studentId = selected.getAttribute('data-id');
        const queueId   = selected.getAttribute('data-queue-id');
        const notes     = document.getElementById('eval-notes').value;

        const rating  = document.querySelector('input[name="rating"]:checked')?.value;
        const comm    = document.querySelector('input[name="comm_score"]:checked')?.value;
        const tech    = document.querySelector('input[name="tech_score"]:checked')?.value;
        const culture = document.querySelector('input[name="culture_score"]:checked')?.value;

        if (!rating || !comm || !tech || !culture) {
            alert("Please complete all assessment scores.");
            return;
        }

        // Save full evaluation
        fetch('../../Models/save_evaluation.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                'student_id':    studentId,
                'queue_id':      queueId,
                'rating':        rating,
                'comm_score':    comm,
                'tech_score':    tech,
                'culture_score': culture,
                'notes':         notes
            })
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === "Success") {
                selected.remove();
                location.reload();
            } else {
                alert("Error: " + data);
            }
        })
        .catch(err => console.error("Fetch Error:", err));

        // Also save via original controller
        const params = new URLSearchParams({ action: 'submit_eval', student_id: studentId, notes: notes });
        fetch('../../Controllers/recruiterController.php', { method: 'POST', body: params })
        .then(res => res.text())
        .then(data => { if(data.trim()==="success") console.log("Eval also saved via recruiterController."); });
    }

    // ========== 7. INTERFACE UTILITIES ==========
    function resetActionInterface() {
        currentStatus = "IDLE";
        clearInterval(timerInterval);
        timerDisplay.innerText = "05:00";
        evalZone.style.display = "none";
        extendBtn.style.display = "none";
        document.getElementById('eval-form').style.display = 'none';
        mainBtn.innerText = "Start Interview Chat";
        mainBtn.className = "btn btn-green btn-lg px-4 rounded-3";
        notesArea.value = "";
        document.querySelectorAll('input[type="radio"]').forEach(r => r.checked = false);
    }

    window.addEventListener('DOMContentLoaded', () => {
        const first = document.querySelector('.candidate-item');
        if (first) first.click();
    });

    // ========== 8. QUEUE PAUSE TOGGLE ==========
    const pauseToggle = document.getElementById('queuePauseToggle');
    const statusLabel = document.getElementById('statusLabel');
    const queueCol    = document.querySelector('.queue-column');

    pauseToggle.addEventListener('change', function() {
        const isPaused = !this.checked;

        if (isPaused) {
            statusLabel.innerText = "Paused";
            statusLabel.classList.replace('text-dark', 'text-danger');
            queueCol.classList.add('paused');
            alert("Queue Paused. You can still see current candidates, but no new alerts will trigger.");
        } else {
            statusLabel.innerText = "Active";
            statusLabel.classList.replace('text-danger', 'text-dark');
            queueCol.classList.remove('paused');
        }

        fetch('../../Models/toggle_queue_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                'recruiter_id': '<?= $recruiter_id ?>',
                'status': isPaused ? 'paused' : 'active'
            })
        });
    });

    // ========== 9. INVITE TO QUEUE ==========
    function inviteToQueue(studentId) {
        const data = new URLSearchParams();
        data.append('action', 'fast_pass');
        data.append('student_id', studentId);

        fetch('../../Controllers/recruiterController.php', {
            method: 'POST',
            body: data
        })
        .then(res => res.text())
        .then(resp => {
            if(resp.trim() === "success") {
                alert("Student invited to the front of your queue!");
            } else {
                alert("Error inviting student.");
            }
        });
    }

    function startInterview(name, id) {
        currentStudentId = id;
        document.getElementById('display-name').innerText = name;
        document.getElementById('st-name').innerText = name;
        document.getElementById('eval-placeholder') && (document.getElementById('eval-placeholder').style.display = 'none');
        document.getElementById('eval-form').style.display = 'block';
    }
    
    </script>
</body>
</html>