<?php
// 1. Connection (Keep your existing connection block)
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db   = 'virtual_career_fair';
$port = 3306; 

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. THE MISSING PIECE: Run the queries to define the variables
// Total Students
$res = $conn->query("SELECT COUNT(*) as total FROM student");
$total_students = ($res) ? $res->fetch_assoc()['total'] : 0;

// Average GPA
$res = $conn->query("SELECT AVG(GPA) as avg FROM student");
$avg_gpa = ($res) ? $res->fetch_assoc()['avg'] : 0;

// Total Interviews
$res = $conn->query("SELECT COUNT(*) as total FROM interview");
$total_interviews = ($res) ? $res->fetch_assoc()['total'] : 0;

// Hiring Rate calculation
$hiring_rate = ($total_students > 0) ? ($total_interviews / $total_students) * 100 : 0;

// GPA Distribution for the Chart
$gpa_query = "SELECT 
    SUM(CASE WHEN GPA BETWEEN 0 AND 2.5 THEN 1 ELSE 0 END) as low,
    SUM(CASE WHEN GPA BETWEEN 2.6 AND 3.0 THEN 1 ELSE 0 END) as mid,
    SUM(CASE WHEN GPA BETWEEN 3.1 AND 3.5 THEN 1 ELSE 0 END) as high,
    SUM(CASE WHEN GPA BETWEEN 3.6 AND 4.0 THEN 1 ELSE 0 END) as elite
    FROM student";
$gpa_data = $conn->query($gpa_query)->fetch_assoc();

// Major Breakdown
$majors = []; $major_counts = [];
$major_res = $conn->query("SELECT Major, COUNT(*) as count FROM student GROUP BY Major ORDER BY count DESC LIMIT 5");
while($row = $major_res->fetch_assoc()){
    $majors[] = $row['Major'];
    $major_counts[] = $row['count'];
}

// Skill Density
$skills = []; $skill_counts = [];
$skill_res = $conn->query("SELECT Skill_Name, COUNT(*) as count FROM student_skills GROUP BY Skill_Name ORDER BY count DESC LIMIT 6");
while($row = $skill_res->fetch_assoc()){
    $skills[] = $row['Skill_Name'];
    $skill_counts[] = $row['count'];
}
?>
<!-- NOW start your <!doctype html> here -->
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WorkPortal | Hire-ability Analytics</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="../../assets/css/rec.css" rel="stylesheet" />
    <style>
        :root {
            --brand-green: #45d667;
            --brand-slate: #022b01;
        }
        .ai-card { background: var(--brand-slate); color: white; border: none; }
        .insight-pill { background: #f0fdf4; border: 1px solid #dcfce7; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem; color: var(--brand-slate); }
        .kpi-card { border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .stat-value { font-size: 1.8rem; font-weight: 700; color: var(--brand-slate); }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <nav class="sidebar">
            <h4 class="mb-5 fw-bold">WorkPortal<br><span style="color: var(--brand-green)">Recruiter</span></h4>
            <div class="nav flex-column h-100">
                <a href="R_recruiterDashboard.php" class="nav-link">Dashboard</a>
                <a href="R_candidatePipeline.php" class="nav-link">Candidates</a>
                <a href="R_schedule.php" class="nav-link">Schedule</a>
                <a href="R_analytics.php" class="nav-link active">Analytics</a>
                <a href="R_messages.php" class="nav-link">Messages</a>
                <a href="R_profile.php" class="nav-link">Profile</a>
                <a href="../../index.php" class="nav-link text-danger">Logout</a>
            </div>
        </nav>

        <main class="p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold" style="color: var(--brand-slate)">Hire-ability Overview</h2>
                    <p class="text-muted">Analyzing the talent pool composition and academic performance.</p>
                </div>
                <button class="btn btn-green px-4 py-2 shadow-sm" onclick="window.print()">
                    <i class="fa-solid fa-file-export me-2"></i>Export Report
                </button>
            </div>

            <!-- KPI Row -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card p-3 kpi-card">
                        <span class="stat-label">Total Talent Pool</span>
                        <div class="stat-value"><?php echo $total_students; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 kpi-card">
                        <span class="stat-label">Avg. Pool GPA</span>
                        <div class="stat-value text-success"><?php echo number_format($avg_gpa, 2); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 kpi-card">
                        <span class="stat-label">Interviews Conducted</span>
                        <div class="stat-value"><?php echo $total_interviews; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 kpi-card">
                        <span class="stat-label">Pool Hiring Rate</span>
                        <div class="stat-value"><?php echo number_format($hiring_rate, 1); ?>%</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- GPA Distribution Chart -->
                <div class="col-lg-5">
                    <div class="card p-4 h-100">
                        <h5 class="fw-bold mb-4">Academic Distribution (GPA)</h5>
                        <canvas id="gpaChart"></canvas>
                    </div>
                </div>

                <!-- Major Breakdown -->
                <div class="col-lg-4">
                    <div class="card p-4 h-100">
                        <h5 class="fw-bold mb-4">Talent by Major</h5>
                        <canvas id="majorChart"></canvas>
                    </div>
                </div>

                <!-- AI Insights -->
                <div class="col-lg-3">
                    <div class="card p-4 h-100 ai-card shadow-lg">
                        <h5 class="fw-bold mb-4" style="color: var(--brand-green)">
                            <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Live Insights
                        </h5>
                        <div class="d-grid gap-3">
                            <div class="p-3 rounded bg-white bg-opacity-10 border-start border-4" style="border-color: var(--brand-green) !important">
                                <small class="text-uppercase fw-bold" style="color: var(--brand-green); font-size: 0.7rem">Top Skill</small>
                                <p class="small mb-0 mt-1">
                                    <strong><?php echo $skills[0] ?? 'N/A'; ?></strong> is the most common skill among current attendees.
                                </p>
                            </div>
                            <div class="p-3 rounded bg-white bg-opacity-10 border-start border-4 border-warning">
                                <small class="text-uppercase fw-bold text-warning" style="font-size: 0.7rem">Major Spotlight</small>
                                <p class="small mb-0 mt-1">
                                    <?php echo $majors[0] ?? 'No data'; ?> students represent the largest segment of your pool.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Skill Radar -->
                <div class="col-lg-8">
                    <div class="card p-4">
                        <h5 class="fw-bold mb-4">Skill Density Heatmap</h5>
                        <div style="height: 300px;"><canvas id="skillRadarChart"></canvas></div>
                    </div>
                </div>

                <!-- Feedback Pills -->
                <div class="col-lg-4">
                    <div class="card p-4 h-100">
                        <h5 class="fw-bold mb-4">Trending Skills</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach($skills as $s): ?>
                                <span class="insight-pill"><?php echo $s; ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // 1. GPA Chart
        new Chart(document.getElementById("gpaChart"), {
            type: 'bar',
            data: {
                labels: ['0.0-2.5', '2.6-3.0', '3.1-3.5', '3.6-4.0'],
                datasets: [{
                    data: [<?php echo "{$gpa_data['low']}, {$gpa_data['mid']}, {$gpa_data['high']}, {$gpa_data['elite']}"; ?>],
                    backgroundColor: '#45d667',
                    borderRadius: 6
                }]
            },
            options: { plugins: { legend: { display: false } } }
        });

        // 2. Major Chart
        new Chart(document.getElementById("majorChart"), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($majors); ?>,
                datasets: [{
                    data: <?php echo json_encode($major_counts); ?>,
                    backgroundColor: ['#022b01', '#38c158', '#45d667', '#a2f0b5', '#e2e2e2']
                }]
            },
            options: { cutout: '70%' }
        });

        // 3. Skill Radar
        new Chart(document.getElementById("skillRadarChart"), {
            type: 'radar',
            data: {
                labels: <?php echo json_encode($skills); ?>,
                datasets: [{
                    label: 'Prevalence',
                    data: <?php echo json_encode($skill_counts); ?>,
                    backgroundColor: 'rgba(69, 214, 103, 0.2)',
                    borderColor: '#45d667',
                    borderWidth: 2
                }]
            },
            options: {
                maintainAspectRatio: false,
                scales: { r: { beginAtZero: true, grid: { color: '#eee' } } }
            }
        });
    </script>
</body>
</html>