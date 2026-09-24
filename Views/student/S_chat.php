<?php
session_start();
require_once('../../DataBase/DataBase/db_connect.php'); 

if (!isset($_SESSION['User_ID'])) {
    header("Location: ../login.php");
    exit();
}

$current_user_id = $_SESSION['User_ID'];

// Query to get recruiters
$sql = "SELECT u.User_ID, u.F_Name, u.L_Name, c.Company_Name 
        FROM recruiter r
        JOIN user u ON r.Recruiter_ID = u.User_ID
        JOIN company c ON r.Company_ID = c.Company_ID";

$result = mysqli_query($connect, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorkPortal | Live Chat</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        :root {
            --brand-green: #28a745;
            --brand-slate: #001d3f;
            --bg-light: #f4f7f6;
        }
        body { background-color: var(--bg-light); font-family: 'Inter', sans-serif; height: 100vh; overflow: hidden; margin: 0; }
        .sidebar { width: 260px; background: var(--brand-slate); color: white; padding: 2rem 1.5rem; position: fixed; height: 100vh; z-index: 1000; }
        .nav-link { color: #adb5bd; padding: 0.8rem 1rem; border-radius: 8px; text-decoration: none; display: block; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: rgba(40, 167, 69, 0.15); color: var(--brand-green) !important; }
        .chat-list-pane { width: 320px; background: white; border-right: 1px solid #dee2e6; margin-left: 260px; display: flex; flex-direction: column; }
        .chat-item { padding: 1.25rem; border-bottom: 1px solid #eee; cursor: pointer; }
        .chat-item.active { background: #f0f7f1; border-left: 4px solid var(--brand-green); }
        .main-console { flex-grow: 1; display: flex; flex-direction: column; background: white; }
        .console-header { padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; }
        #chat-window { flex-grow: 1; padding: 2rem; overflow-y: auto; background: #fdfdfd; display: flex; flex-direction: column; gap: 1rem; }
        .msg { max-width: 75%; padding: 10px 16px; border-radius: 18px; font-size: 0.95rem; }
        .msg-recruiter { background: var(--brand-slate); color: white; align-self: flex-start; border-bottom-left-radius: 4px; }
        .msg-candidate { background: #f1f3f5; color: #1a202c; align-self: flex-end; border-bottom-right-radius: 4px; }
        .timer-badge { font-size: 0.85rem; padding: 5px 12px; border-radius: 20px; }
    </style>
</head>
<body>

<div class="d-flex h-100 w-100">
    <!-- Sidebar -->
     <nav class="sidebar">
        <h4 class="fw-bold mb-5">
            <span class="text-white">WorkPortal</span> <br />
            <span class="text-success" style="font-size: 1.2rem;">Student</span>
        </h4>
        <div class="nav flex-column">
            <a href="S_studentDashboard.php" class="nav-link "> Dashboard</a>
            <a href="S_fair.php" class="nav-link"> Browse Fairs</a>
            <a href="S_liveSessions.php" class="nav-link active"> Live Sessions</a>
            <a href="S_chat.php" class="nav-link"> Chat</a>
            <a href="S_profile.php" class="nav-link"> Profile</a>
            <a href="../../index.php" class="nav-link text-danger mt-auto"> Logout</a>
        </div>
    </nav>

    <!-- Chat List -->
    <aside class="chat-list-pane">
        <div class="p-3 border-bottom">
            <input type="text" class="form-control form-control-sm" placeholder="Search recruiters...">
        </div>
        <div class="overflow-auto">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="chat-item" onclick="switchUser(event, '<?= addslashes($row['F_Name'].' '.$row['L_Name']) ?>', '<?= $row['User_ID'] ?>', '<?= addslashes($row['Company_Name']) ?>')">
                    <span class="fw-bold d-block"><?= htmlspecialchars($row['F_Name'].' '.$row['L_Name']) ?></span>
                    <small class="text-muted"><?= htmlspecialchars($row['Company_Name']) ?></small>
                </div>
            <?php endwhile; ?>
        </div>
    </aside>

    <!-- Main Chat Console -->
    <main class="main-console">
        <header class="console-header">
            <div class="d-flex align-items-center">
                <div id="user-avatar" class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">?</div>
                <div>
                    <h6 id="header-name" class="mb-0 fw-bold">Select a Recruiter</h6>
                    <small id="header-status" class="text-muted">No active session</small>
                </div>
            </div>
            <!-- Requirement 15: Timer Display -->
            <div id="timer-display" class="badge bg-danger timer-badge d-none">05:00</div>
        </header>

        <div id="chat-window">
            <div class="text-center text-muted p-5">Select a contact from the left to begin your timed interview.</div>
        </div>

        <!-- Requirement 16 & 18: Interaction Controls -->
        <form id="chat-form" class="p-3 border-top d-none">
            <div class="input-group">
                <label class="btn btn-light border" title="Flash-Share Document">
                    📎 <input type="file" id="file-drop" style="display:none" onchange="flashShare(this)">
                </label>
                <input type="text" id="msg-input" class="form-control border-0 bg-light" placeholder="Type a message...">
                <button type="submit" class="btn btn-success px-4" style="background: var(--brand-slate);">Send</button>
            </div>
        </form>
    </main>
</div>

<script>
    // 1. Initialize global variables (CRITICAL: These were missing)
    const chatWindow = document.getElementById('chat-window');
    let selectedUserId = null;
    let timeLeft = 300; // 5 Minutes
    let timerInterval;
    let pollInterval;

    // 2. Function to switch between recruiters
    function switchUser(event, name, userId, company) {
        selectedUserId = userId;
        
        // UI Updates
        document.getElementById('header-name').innerText = name;
        document.getElementById('header-status').innerText = company;
        document.getElementById('user-avatar').innerText = name.charAt(0);
        document.getElementById('chat-form').classList.remove('d-none');
        document.getElementById('timer-display').classList.remove('d-none');
        
        // Reset and Start Timer
        timeLeft = 300;
        startTimer();

        // Clear and show start message
        chatWindow.innerHTML = `<div class="text-center my-3"><small class="badge bg-light text-muted">Interview started with ${name}</small></div>`;

        // Highlight the selected person in the list
        document.querySelectorAll('.chat-item').forEach(el => el.classList.remove('active'));
        event.currentTarget.classList.add('active');
        
        // --- LIVE FETCH LOGIC ---
        clearInterval(pollInterval); // Stop checking previous user
        loadMessages();              // Load history immediately
        pollInterval = setInterval(loadMessages, 2000); // Check for new messages every 2 seconds
    }

    // 3. Receive Logic: Fetch messages from the database
   // Inside your loadMessages() function
data.forEach(msg => {
    const msgDiv = document.createElement('div');
    
    if (msg.Sender_ID == <?= $_SESSION['User_ID'] ?>) {
        msgDiv.className = 'msg msg-candidate';
    } else {
        msgDiv.className = 'msg msg-recruiter';
    }
    
    // Change msg.message to msg.Content
    msgDiv.innerText = msg.Content; 
    chatWindow.appendChild(msgDiv);
});

    // 4. Send Logic: Save message to database
    document.getElementById('chat-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const input = document.getElementById('msg-input');
        const message = input.value.trim();

        if (message && selectedUserId) {
            // Send to server
            fetch('../Models/save_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `receiver_id=${selectedUserId}&message=${encodeURIComponent(message)}`
            })
            .then(() => {
                input.value = ''; // Clear input field
                loadMessages();   // Refresh chat window immediately
            });
        }
    });

    // 5. Timer Logic
    function startTimer() {
        clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            timeLeft--;
            let mins = Math.floor(timeLeft / 60);
            let secs = timeLeft % 60;
            document.getElementById('timer-display').innerText = `${mins}:${secs < 10 ? '0' : ''}${secs}`;

            if (timeLeft === 60) alert("Warning: 1 minute remaining!");
            if (timeLeft <= 0) endSession();
        }, 1000);
    }

    function endSession() {
        clearInterval(timerInterval);
        clearInterval(pollInterval); // Stop fetching messages
        chatWindow.innerHTML = `
            <div class="text-center p-5">
                <h4>Session Expired</h4>
                <p>Please complete the feedback form to finish your interview application.</p>
                <a href="S_feedback.php" class="btn btn-success">Go to Feedback Form</a>
            </div>`;
        document.getElementById('chat-form').classList.add('d-none');
    }

    function flashShare(input) {
        if (input.files && input.files[0]) {
            alert("File upload logic would go to ../Models/upload.php");
        }
    }
</script>
</body>
</html>