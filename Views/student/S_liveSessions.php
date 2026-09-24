<?php
session_start();
include "../../DataBase/DataBase/db_connect.php";
include "../../Models/messages.php";

$student_id = $_SESSION['User_ID'];

$query = "

SELECT
q.*,
c.Company_Name,
j.Job_Title,
r.Recruiter_ID

FROM queue q

JOIN company c
ON q.Company_ID = c.Company_ID

JOIN job j
ON q.Job_ID = j.Job_ID

LEFT JOIN recruiter r
ON c.Company_ID = r.Company_ID

WHERE q.Student_ID = '$student_id'

AND q.Status IN ('Waiting', 'In Progress')

ORDER BY q.Entry_Time DESC

LIMIT 1

";

$result = mysqli_query($connect, $query);


$session = mysqli_fetch_assoc($result);

$end_time = null;

if(!empty($session['Session_End'])){

    $end_time =
    strtotime($session['Session_End']);

}

$total_candidates = 12;

$candidates_ahead =
$session['Position_No'] > 0
? $session['Position_No'] - 1
: 0;

$progress =
(($total_candidates - $candidates_ahead)
/ $total_candidates) * 100;


$messageModel = new messages($connect);

$chat_messages =
$messageModel->getMessages(

    $_SESSION['User_ID'],
    $session['Recruiter_ID']

);


if(!$session){
    echo "<div class='alert alert-warning'>No Active Queue Session</div>";
    exit();
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Queue | WorkPortal</title>

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
            padding: 2rem;
        }

        .queue-header {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border-bottom: 5px solid var(--brand-green);
        }

        .position-display {
            background: var(--brand-slate);
            color: white;
            width: 120px;
            height: 120px;
            border-radius: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 4px solid var(--brand-green);
        }

        .progress-step {
            height: 12px;
            border-radius: 10px;
            background: #e9ecef;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .interview-ready-zone {
            background: linear-gradient(135deg, #fff 0%, #f0fff4 100%);
            border: 2px dashed var(--brand-green);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            transition: 0.3s;
        }

        .btn-interview {
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 700;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .chat-box {
            height: 500px;
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            overflow-y: auto;
            border: 1px solid #eee;
        }

        .typing-indicator {
            font-size: 0.8rem;
            color: #6c757d;
            font-style: italic;
        }

        .sidebar {
            width: 260px;
            background: var(--brand-slate);
            color: white;
            padding: 2rem 1.5rem;
            position: fixed;
            height: 100vh;
        }
    </style>
</head>

<body>

     <nav class="sidebar">
        <h4 class="fw-bold mb-5">
            <span class="text-white">WorkPortal</span> <br />
            <span class="text-success" style="font-size: 1.2rem;">Student</span>
        </h4>
        <div class="nav flex-column">
            <a href="S_studentDashboard.php" class="nav-link "> Dashboard</a>
            <a href="S_fair.php" class="nav-link"> Browse Fairs</a>
            <a href="S_liveSessions.php" class="nav-link active">Live Interview & Chat Session</a>
            <a href="S_profile.php" class="nav-link"> Profile</a>
            <a href="../../index.php" class="nav-link text-danger mt-auto"> Logout</a>
        </div>
    </nav>

    <div class="main-content">

        <div class="queue-header mb-4">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <div class="position-display">
                        <small class="text-uppercase opacity-75" style="font-size: 0.7rem;">Your Rank</small>
                        <h1 class="fw-bold mb-0">#<?php echo $session['Position_No']; ?></h1>
                    </div>
                </div>
                <div class="col-md-6">
                    <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($session['Company_Name']); ?></h2>
                    <p class="text-muted mb-3">Role:<?php echo htmlspecialchars($session['Job_Title']); ?></p>
                    <div class="d-flex gap-3">
                        <span class="badge bg-success bg-opacity-10 text-success p-2 px-3">Status:<?php echo htmlspecialchars($session['Status']); ?></span>
                        <span class="badge bg-dark p-2 px-3"><i class="bi bi-people me-1"></i> 12 Candidates Total</span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end border-start">
                    <p class="text-muted mb-1">Estimated Wait</p>
                    <h2 class="fw-bold text-success mb-0">~<?php echo $session['Wait_Time']; ?> Mins</h2>
                    <small class="text-muted">Queue Speed: <span class="text-primary fw-bold">Moderate</span></small>
                </div>
            </div>
        </div>

        <div class="row g-4">

            <div class="col-lg-8">

                <div class="card p-4 mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <h6 class="fw-bold">Queue Progress</h6>
                        <span class="small text-muted"><?php echo $candidates_ahead; ?>
                        / <?php echo $total_candidates; ?>
                        Candidates Ahead</span>
                    </div>
                    <div class="progress-step">
                        <div class="progress-bar bg-success" style="width: <?php echo $progress; ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-muted"><i class="bi bi-circle-fill text-success small me-1"></i> 🟢 Moving Forward</small>
                        <small class="text-muted">Updated 10s ago</small>
                    </div>
                </div>

                <?php if ($session['Status'] == 'Waiting'): ?>

                <div class="interview-ready-zone mb-4">

                    <div class="mb-4">

                        <i class="bi bi-hourglass-split fs-1 text-warning"></i>

                        <h3 class="fw-bold mt-3">
                            Waiting For Your Turn
                        </h3>

                        <p class="text-muted">
                            Recruiter is currently interviewing another candidate.
                        </p>

                    </div>

                    <button class="btn btn-secondary btn-interview" disabled>

                        Waiting...

                    </button>

                </div>

                <?php endif; ?>

                <?php if ($session['Status'] == 'In Progress'): ?>

                    <div class="card p-4 shadow-sm" id="chatPanel">

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0">Live Interview Chat</h5>
                                <div class="badge bg-danger">Time Remaining:<span id="countdown"></span></div>
                            </div>
                            <div class="chat-box mb-3">
                                <div class="text-center my-3 small text-muted">--- Session Started ---</div>
                                <?php while($msg = mysqli_fetch_assoc($chat_messages)): ?>

                                    <div class="bg-white p-3 rounded-4 shadow-sm mb-3 border">

                                        <strong>

                                            <?php

                                            if($msg['Sender_ID']
                                            == $_SESSION['User_ID']) {

                                                echo "You";

                                            } else {

                                                echo "Recruiter";

                                            }

                                            ?>:

                                        </strong>

                                        <?php echo htmlspecialchars($msg['Content']); ?>

                                    </div>

                                <?php endwhile; ?>

                            </div>
                            <p class="typing-indicator mb-2">Recruiter is typing...</p>
                            <form method="POST"
                              enctype="multipart/form-data"
                              action="../../Controllers/chatController.php">

                                <input
                                type="hidden"
                                name="receiver_id"
                                value="<?php echo $session['Recruiter_ID']; ?>">

                                <div class="input-group">

                                    <input
                                    type="text"
                                    name="message"
                                    class="form-control"
                                    placeholder="Type your message...">

                                    <input
                                    type="file"
                                    name="pdf_file"
                                    accept=".pdf"
                                    class="form-control">

                                    <button class="btn btn-success">

                                        Send

                                    </button>

                                </div>

                            </form>
                        </div>

                    </div>

                <?php endif; ?>


            

            <div class="col-lg-4">

                <div class="card p-4 mb-4">
                    <h6 class="fw-bold mb-3 text-uppercase small text-muted">Live Updates</h6>
                    <div class="list-group list-group-flush small">
                        <div class="list-group-item px-0 border-0 mb-2">
                            <div class="d-flex gap-2">
                                <i class="bi bi-bell-fill text-success"></i>
                                <span>2 candidates ahead of you completed interview.</span>
                            </div>
                        </div>
                        <div class="list-group-item px-0 border-0 mb-2">
                            <div class="d-flex gap-2">
                                <i class="bi bi-person-check text-primary"></i>
                                <span>Recruiter is now available.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="../../Controllers/queueController.php?action=leave"
                        class="btn btn-outline-danger">

                        Leave Queue

                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exitModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="p-4 text-center">
                    <i class="bi bi-exclamation-triangle text-danger display-4"></i>
                    <h4 class="fw-bold mt-3">Are you sure?</h4>
                    <p class="text-muted">If you leave now, you will lose your position (#04) at Google.</p>
                    <div class="d-grid gap-2 mt-4">
                        <button class="btn btn-danger py-2">Yes, Leave Queue</button>
                        <button class="btn btn-light py-2" data-bs-dismiss="modal">Stay in Line</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        const exitModal = new bootstrap.Modal(document.getElementById('exitModal'));

        function showExitModal() {
            exitModal.show();
        }

    </script>


    <?php if(!empty($session['Session_End'])): ?>
    <script>

    const endTime =
    new Date(
    "<?php echo $session['Session_End']; ?>"
    ).getTime();

    const timer =
    setInterval(function() {

        const now = new Date().getTime();

        const distance =
        endTime - now;

        if(distance <= 120000){

            document.getElementById(
            "countdown"
            ).style.color = "yellow";

        }

        if(distance <= 0){

            clearInterval(timer);

            alert("Session Ended");

            window.location.href =
            "../../Controllers/queueController.php?action=finish";

        }

        const minutes =
        Math.floor(
        (distance % (1000 * 60 * 60))
        / (1000 * 60));

        const seconds =
        Math.floor(
        (distance % (1000 * 60))
        / 1000);

        document.getElementById(
        "countdown"
        ).innerHTML =

        minutes + ":" + seconds;

    }, 1000);

    </script>
    <?php endif; ?>


</body>

</html>