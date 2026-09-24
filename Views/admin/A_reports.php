<?php
session_start();
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>WorkPortal Admin | Analytics & Reports</title>
  <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    :root {
      --brand-green: #28a745;
      --brand-slate: #1a1d21;
      --brand-light: #f4f7f6;
      --accent-blue: #007bff;
    }

    body {
      background-color: var(--brand-light);
      font-family: "Inter", sans-serif;
      color: #333;
    }

    .dashboard-wrapper {
      display: flex;
      min-height: 100vh;
    }

    .sidebar {
      width: 260px;
      background: var(--brand-slate);
      --brand-slate: #1a1d21;
      color: white;
      padding: 2rem 1.5rem;
      position: sticky;
      top: 0;
      height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .sidebar h4 {
      font-weight: 800;
      letter-spacing: -1px;
      margin-bottom: 2.5rem;
      color: white;
    }

    .nav-link {
      color: #adb5bd;
      padding: 0.8rem 1rem;
      border-radius: 8px;
      margin-bottom: 0.4rem;
      font-weight: 500;
      transition: 0.3s;
      text-decoration: none;
    }

    .nav-link:hover,
    .nav-link.active {
      background: rgba(40, 167, 69, 0.15);
      color: var(--brand-green) !important;
    }

    .main-content {
      flex: 1;
      padding: 2.5rem;
    }

    .card {
      border: none;
      border-radius: 14px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      background: white;
      margin-bottom: 1.5rem;
    }

    .metric-card {
      border-top: 4px solid var(--brand-green);
      transition: transform 0.2s;
    }

    .metric-card:hover {
      transform: translateY(-5px);
    }

    .chart-placeholder {
      width: 100%;
      height: 200px;
      background: #f8f9fa;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #adb5bd;
      font-weight: 600;
    }

    .insight-pill {
      background: #fff3cd;
      color: #856404;
      padding: 1rem;
      border-radius: 10px;
      margin-bottom: 1rem;
      border-left: 5px solid #ffc107;
    }

    .export-btn {
      border: 1px solid #dee2e6;
      transition: 0.3s;
    }

    .export-btn:hover {
      background: #f8f9fa;
      border-color: var(--brand-green);
    }
  </style>
</head>

<body data-admin-id="<?php echo (int) $_SESSION['Admin_ID']; ?>" data-fair-id="1">
  <div class="dashboard-wrapper">
    <nav class="sidebar">
      <h4 class="fw-bold mb-5">
        <span class="text-white">WorkPortal</span> <br />
        <span class="text-success">Admin</span>
      </h4>
      <a href="A_adminDashboard.php" class="nav-link">System Health</a>
      <a href="A_companyApplications.php" class="nav-link">Manage Companies</a>
      <a href="A_userManagement.php" class="nav-link">Users management</a>
      <a href="A_fairManagement.php" class="nav-link">Fair Traffic</a>
      <a href="A_reports.php" class="nav-link active">Reporting</a>
      <a href="../../index.php" class="nav-link text-danger">Sign Out</a>
    </nav>

    <main class="main-content">
      <header class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="fw-bold mb-1">Fair Analytics</h2>
          <p class="text-muted small">
            Post-event performance and student engagement insights.
          </p>
        </div>
        <div class="dropdown">
          <button
            class="btn btn-dark dropdown-toggle px-4 fw-bold shadow-sm"
            type="button"
            data-bs-toggle="dropdown">
            📥 Export Report
          </button>
          <ul class="dropdown-menu shadow border-0">
            <li><a class="dropdown-item" href="#">📄 Export Full PDF</a></li>
            <li><a class="dropdown-item" href="#">📊 Raw Data (CSV)</a></li>
            <li>
              <a class="dropdown-item" href="#">🏢 Company-wise Report</a>
            </li>
          </ul>
        </div>
      </header>

      <div class="row g-3 mb-4">
        <div class="col-md-2">
          <div class="card p-3 metric-card text-center">
            <h3 id="admin-report-finished-students" class="fw-bold mb-0">1,248</h3>
            <p class="x-small text-muted mb-0" style="font-size: 0.75rem">
              Students
            </p>
          </div>
        </div>
        <div class="col-md-2">
          <div
            class="card p-3 metric-card text-center"
            style="border-top-color: #007bff">
            <h3 class="fw-bold mb-0">56</h3>
            <p class="x-small text-muted mb-0" style="font-size: 0.75rem">
              Companies
            </p>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card p-3 metric-card text-center">
            <h3 id="admin-report-interviews" class="fw-bold mb-0">842</h3>
            <p class="x-small text-muted mb-0" style="font-size: 0.75rem">
              Interviews
            </p>
          </div>
        </div>
        <div class="col-md-2">
          <div
            class="card p-3 metric-card text-center"
            style="border-top-color: #dc3545">
            <h3 class="fw-bold mb-0">14m</h3>
            <p class="x-small text-muted mb-0" style="font-size: 0.75rem">
              Avg. Queue
            </p>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card p-3 metric-card text-center">
            <h3 class="fw-bold mb-0">4.8</h3>
            <p class="x-small text-muted mb-0" style="font-size: 0.75rem">
              Rating
            </p>
          </div>
        </div>
        <div class="col-md-2">
          <div
            class="card p-3 metric-card text-center"
            style="border-top-color: #6f42c1">
            <h3 class="fw-bold mb-0">78%</h3>
            <p class="x-small text-muted mb-0" style="font-size: 0.75rem">
              Engagement
            </p>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-8">
          <div class="card p-4">
            <h6 class="fw-bold mb-4">
              📈 Participation Trend & Queue Congestion
            </h6>
            <div class="chart-placeholder">
              [Line Chart: Student Attendance vs. Time of Day]
            </div>
          </div>

          <div class="card p-4">
            <div
              class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold">🏆 Performance Rankings</h6>
              <select class="form-select form-select-sm w-auto">
                <option>Top Companies</option>
                <option>Top Students</option>
              </select>
            </div>
            <table class="table table-sm align-middle small">
              <thead class="table-light">
                <tr>
                  <th>Entity</th>
                  <th>Interactions</th>
                  <th>Avg. Duration</th>
                  <th>Rating</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>Google</strong></td>
                  <td>142</td>
                  <td>8.5m</td>
                  <td>⭐⭐⭐⭐⭐ 4.9</td>
                </tr>
                <tr>
                  <td><strong>Tesla</strong></td>
                  <td>110</td>
                  <td>12m</td>
                  <td>⭐⭐⭐⭐ 4.2</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card p-4">
            <h6 class="fw-bold mb-3">💡 Smart Recommendations</h6>
            <div class="insight-pill">
              <small class="fw-bold d-block">Bottleneck Detected</small>
              <span class="small">Tesla queue wait time (28m) exceeded optimal threshold.
                Increase booth slots for next event.</span>
            </div>
            <div
              class="insight-pill"
              style="
                  border-left-color: var(--brand-green);
                  background: #e6f4ea;
                ">
              <small class="fw-bold d-block text-success">Demand Insight</small>
              <span class="small">Students prefer Frontend roles over Backend by
                <strong>2.3x</strong> this season.</span>
            </div>
          </div>

          <div class="card p-4">
            <h6 class="fw-bold mb-3">📦 Booth Utilization</h6>
            <div class="mb-3">
              <div class="d-flex justify-content-between small mb-1">
                <span>High Demand (A-101)</span>
                <span>98%</span>
              </div>
              <div class="progress" style="height: 6px">
                <div class="progress-bar bg-danger" style="width: 98%"></div>
              </div>
            </div>
            <div class="mb-3">
              <div class="d-flex justify-content-between small mb-1">
                <span>Standard (B-202)</span>
                <span>45%</span>
              </div>
              <div class="progress" style="height: 6px">
                <div class="progress-bar bg-success" style="width: 45%"></div>
              </div>
            </div>
          </div>

          <div class="card p-4">
            <h6 class="fw-bold mb-3">Quick Export</h6>
            <div class="d-grid gap-2">
              <button class="btn btn-sm export-btn text-start">
                📄 Download PDF Summary
              </button>
              <button class="btn btn-sm export-btn text-start">
                📊 Raw Attendance (CSV)
              </button>
              <button class="btn btn-sm export-btn text-start">
                💬 Chat Transcripts (Excel)
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- <script src="../../assets/js/admin_module.js"></script> -->
</body>

</html>