<?php
session_start();
require_once('../../DataBase/DataBase/db_connect.php'); 

// Ensure only recruiters can access
if (!isset($_SESSION['User_ID'])) {
    header("Location: ../login.php");
    exit();
}

$recruiter_id = $_SESSION['User_ID'];

$sql = "SELECT DISTINCT u.User_ID, u.F_Name, u.L_Name 
        FROM user u
        LEFT JOIN messages m ON (u.User_ID = m.Sender_ID OR u.User_ID = m.Reciever_ID)
        WHERE u.Role = 'Student' 
        AND u.User_ID != '$recruiter_id'
        ORDER BY u.L_Name ASC";

$candidates = mysqli_query($connect, $sql);

$candidates = mysqli_query($connect, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorkPortal | Interview Management</title>
      <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        :root {
            --brand-slate: #022b01;
            --brand-green: #45d667;
            --bg-light: #f8faf9;
            --border-color: #eaeef0;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        .sidebar-nav {
            width: 240px;
            background: var(--brand-slate);
            color: white;
            display: flex;
            flex-direction: column;
            padding: 1.5rem 1rem;
            flex-shrink: 0;
        }

        .sidebar-nav .nav-link {
            color: #a0aec0;
            padding: 0.8rem 1rem;
            border-radius: 10px;
            margin-bottom: 0.3rem;
            transition: 0.2s;
        }

        .sidebar-nav .nav-link:hover, .sidebar-nav .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: var(--brand-green) !important;
        }

        .chat-list-pane {
            width: 320px;
            background: white;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }

        .chat-item {
            padding: 1.25rem;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background 0.2s;
        }

        .chat-item:hover { background: #f9fbf9; }
        .chat-item.active { 
            background: #f0f7f1; 
            border-left: 4px solid var(--brand-green); 
        }

        .main-console {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .console-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #chat-window {
            flex-grow: 1;
            padding: 2rem;
            overflow-y: auto;
            background: #fdfdfd;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .msg {
            max-width: 75%;
            padding: 10px 16px;
            border-radius: 18px;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .msg-recruiter {
            background: var(--brand-slate);
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }

        .msg-candidate {
            background: #f1f3f5;
            color: #1a202c;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }

        .eval-panel {
            width: 300px;
            background: #fcfdfc;
            border-left: 1px solid var(--border-color);
            padding: 1.5rem;
            flex-shrink: 0;
        }

        .mt-auto { margin-top: auto; }
    </style>
</head>
<body>
<!-- Keep your existing PHP logic at the top -->

<div class="d-flex h-100 w-100">
    
    <!-- 1. NAVIGATION SIDEBAR -->
    <nav class="sidebar-nav">
         <h4 class="mb-5">WorkPortal<br><span style="color: var(--brand-green)">Recruiter</span></h4>
        <div class="nav flex-column h-100">
            <a href="R_recruiterDashboard.php" class="nav-link">Dashboard</a>
            <a href="R_candidatePipeline.php" class="nav-link">Candidates</a>
            <a href="R_schedule.php" class="nav-link">Schedule</a>
            <a href="R_analytics.php" class="nav-link">Analytics</a>
            <a href="R_messages.php" class="nav-link active">Messages</a>
            <a href="R_profile.php" class="nav-link">Profile</a>
            <a href="../../index.php" class="nav-link text-danger">Logout</a>
        </div>
    </nav>

    <!-- 2. DYNAMIC CHAT LIST PANE -->
  <aside class="chat-list-pane">
    <div class="p-3 border-bottom">
        <input type="text" class="form-control form-control-sm" placeholder="Search students...">
    </div>
    <div class="overflow-auto flex-grow-1">
        <?php if($candidates && mysqli_num_rows($candidates) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($candidates)): ?>
                <div class="chat-item" onclick="switchUser('<?= addslashes($row['F_Name'].' '.$row['L_Name']) ?>', '<?= substr($row['F_Name'], 0, 1) ?>', '<?= $row['User_ID'] ?>')">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold d-block"><?= htmlspecialchars($row['F_Name'].' '.$row['L_Name']) ?></span>
                        <small class="text-muted">Student</small>
                    </div>
                    <small class="text-muted d-block">View chat history</small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="p-4 text-center">
                <p class="small text-muted mb-0">No students found.</p>
                <p class="extra-small text-muted">Waiting for messages...</p>
            </div>
        <?php endif; ?>
    </div>
</aside>

    <!-- 3. MAIN CONSOLE -->
    <main class="main-console">
        <header class="console-header">
            <div class="d-flex align-items-center">
                <div id="user-avatar" class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold me-3" style="width: 45px; height: 45px;">?</div>
                <div>
                    <h6 id="header-name" class="mb-0 fw-bold">Select a Candidate</h6>
                    <small id="header-match" class="text-success fw-bold">Interview Session</small>
                </div>
            </div>
            <div class="btn-group">
                <button class="btn btn-outline-secondary btn-sm" onclick="extendMeeting()">Extend</button>
                <button class="btn btn-danger btn-sm">End Session</button>
            </div>
        </header>

        <div id="chat-window">
            <!-- Messages load here via fetchMessages() -->
            <div class="text-center text-muted mt-5">Select a candidate from the left to start messaging.</div>
        </div>

        <form id="chat-form" class="p-3 border-top d-none">
            <div class="input-group">
                <input type="text" id="msg-input" class="form-control border-0 bg-light p-3" placeholder="Write a message..." autocomplete="off">
                <button class="btn btn-success px-4 fw-bold" style="background: var(--brand-slate); color:white;">Send</button>
            </div>
        </form>
    </main>

    <!-- 4. EVALUATION PANEL -->
    <aside class="eval-panel">
        <h6 class="fw-bold text-muted small mb-4">RECRUITER TOOLS</h6>
        <div class="mb-4">
            <label class="small fw-bold">Private Notes</label>
            <textarea class="form-control border-0 bg-white shadow-sm mt-1" rows="12" placeholder="Start typing notes..."></textarea>
        </div>
        <div class="d-grid gap-2">
            <button class="btn btn-success fw-bold" style="background: var(--brand-green); color: var(--brand-slate); border:none;">Shortlist Candidate</button>
        </div>
    </aside>
</div>

<script>
    let selectedCandidateId = null;
    let pollInterval = null;

    function switchUser(name, initials, userId) {
        selectedCandidateId = userId;
        document.getElementById('header-name').innerText = name;
        document.getElementById('user-avatar').innerText = initials;
        document.getElementById('chat-form').classList.remove('d-none');
        
        // Start polling for new messages (Requirement 15/21)
        if(pollInterval) clearInterval(pollInterval);
        fetchMessages(); 
        pollInterval = setInterval(fetchMessages, 3000); // Poll every 3 seconds

        document.querySelectorAll('.chat-item').forEach(el => el.classList.remove('active'));
        event.currentTarget.classList.add('active');
    }

    async function fetchMessages() {
    if (!selectedCandidateId) return;

    try {
        // Corrected URL parameter to match your backend expectations
        const response = await fetch(`get_messages.php?partner_id=${selectedCandidateId}`);
        const data = await response.json();
        const chatWindow = document.getElementById('chat-window');
        
        // Use msg.Content instead of msg.Message_Text
        chatWindow.innerHTML = data.map(msg => `
            <div class="msg ${msg.Sender_ID == <?= $recruiter_id ?> ? 'msg-recruiter' : 'msg-candidate'}">
                ${msg.Content} 
            </div>
        `).join('');
        
        chatWindow.scrollTop = chatWindow.scrollHeight;
    } catch (error) {
        console.error("Error fetching messages:", error);
    }
}

document.getElementById('chat-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const input = document.getElementById('msg-input');
    const message = input.value.trim();

    if (message && selectedCandidateId) {
        const formData = new FormData();
        // Use 'Reciever_ID' to match what your save_message.php likely expects
        formData.append('reciever_id', selectedCandidateId); 
        formData.append('message', message);

        await fetch('save_message.php', { method: 'POST', body: formData });
        input.value = "";
        fetchMessages(); 
    }
});

    function extendMeeting() {
        alert("Meeting extension request sent to candidate.");
    }
</script>

</body>
</html>