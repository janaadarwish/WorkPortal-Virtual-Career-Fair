<?php
session_start();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WorkPortal Admin | User Management</title>
  <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" />
  
    <style>
      :root {
        --brand-green: #28a745;
        --brand-slate: #1a1d21;
        --brand-light: #f4f7f6;
        --danger: #dc3545;
      }

      body {
        background-color: var(--brand-light);
        font-family: "Inter", sans-serif;
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

      .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
      }
      .bg-active {
        background: var(--brand-green);
      }
      .bg-suspended {
        background: var(--danger);
      }
      .bg-pending {
        background: #ffc107;
      }

      .skill-tag {
        font-size: 0.7rem;
        background: #eee;
        padding: 2px 8px;
        border-radius: 4px;
        margin-right: 2px;
      }
      .nav-pills .nav-link.active {
        background-color: var(--brand-slate);
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
        <a href="A_userManagement.php" class="nav-link active">Users management</a>
        <a href="A_fairManagement.php" class="nav-link">Fair Traffic</a>
        <a href="A_reports.php" class="nav-link">Reporting</a>
        <a href="../../index.php" class="nav-link text-danger">Sign Out</a>
      </nav>

      <main class="main-content">
        <div class="row g-3 mb-4">
          <div class="col-md">
            <div class="card p-3 shadow-sm border-0">
              <small class="text-muted fw-bold">TOTAL STUDENTS</small>
              <h3 class="fw-bold mb-0">4,120</h3>
            </div>
          </div>
          <div class="col-md">
            <div class="card p-3 shadow-sm border-0">
              <small class="text-muted fw-bold">RECRUITERS</small>
              <h3 class="fw-bold mb-0">184</h3>
            </div>
          </div>
          <div class="col-md">
            <div class="card p-3 shadow-sm border-0">
              <small class="text-muted fw-bold">ACTIVE NOW</small>
              <h3 class="fw-bold mb-0 text-success">842</h3>
            </div>
          </div>
          <div class="col-md">
            <div class="card p-3 shadow-sm border-0">
              <small class="text-muted fw-bold">SUSPENDED</small>
              <h3 class="fw-bold mb-0 text-danger">12</h3>
            </div>
          </div>
        </div>

        <div class="card p-3 mb-4">
          <div class="row g-2">
            <div class="col-md-4">
              <input
                type="text"
                class="form-control form-control-sm"
                placeholder="Search by name, email, or company..."
              />
            </div>
            <div class="col-md-2">
              <select class="form-select form-select-sm">
                <option>Status: All</option>
                <option>Active</option>
                <option>Suspended</option>
              </select>
            </div>
            <div class="col-md-2">
              <select class="form-select form-select-sm">
                <option>Role: All</option>
                <option>Student</option>
                <option>Recruiter</option>
              </select>
            </div>
            <div class="col-md-4 text-end">
              <button class="btn btn-sm btn-outline-dark me-2">
                📥 Export CSV
              </button>
              <button class="btn btn-sm btn-dark px-3">Apply Filters</button>
            </div>
          </div>
        </div>

        <div class="card overflow-hidden">
          <div
            class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center"
          >
            <h6 class="fw-bold mb-0">User Directory</h6>
            <div class="btn-group">
              <button class="btn btn-xs btn-outline-secondary active">
                All
              </button>
              <button class="btn btn-xs btn-outline-secondary">Students</button>
              <button class="btn btn-xs btn-outline-secondary">
                Recruiters
              </button>
            </div>
          </div>
          <table class="table align-middle mb-0">
            <thead class="table-light small text-uppercase">
              <tr>
                <th class="ps-4">User Details</th>
                <th>Role Info</th>
                <th>Activity Level</th>
                <th>Status</th>
                <th class="text-end pe-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="ps-4">
                  <div class="fw-bold">Jana Darwish</div>
                  <div class="small text-muted">jana@university.edu</div>
                  <div class="mt-1">
                    <span class="skill-tag">Python</span>
                    <span class="skill-tag">UI/UX</span>
                  </div>
                </td>
                <td>
                  <span class="badge bg-info text-dark mb-1">Student</span>
                  <div class="small">Profile: 85% Complete</div>
                </td>
                <td>
                  <div class="small fw-bold">Joined 12 Booths</div>
                  <div class="text-muted" style="font-size: 0.75rem">
                    Last Login: 5m ago
                  </div>
                </td>
                <td><span class="status-dot bg-active"></span>Active</td>
                <td class="text-end pe-4">
                  <div class="dropdown">
                    <button
                      class="btn btn-sm btn-light border dropdown-toggle"
                      data-bs-toggle="dropdown"
                    >
                      Manage
                    </button>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="#">View Resume</a></li>
                      <li>
                        <a class="dropdown-item" href="#">View Activity Log</a>
                      </li>
                      <li><hr class="dropdown-divider" /></li>
                      <li>
                        <a class="dropdown-item text-danger" href="#"
                          >Suspend Account</a
                        >
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>

              <tr>
                <td class="ps-4">
                  <div class="fw-bold">Mark Thompson</div>
                  <div class="small text-muted">m.thompson@google.com</div>
                </td>
                <td>
                  <span class="badge bg-dark mb-1">Recruiter</span>
                  <div class="small">Google | Booth A-101</div>
                </td>
                <td>
                  <div class="small fw-bold">84 Interviews Done</div>
                  <div class="text-muted" style="font-size: 0.75rem">
                    Avg Response: 2.4m
                  </div>
                </td>
                <td><span class="status-dot bg-active"></span>Active</td>
                <td class="text-end pe-4">
                  <button class="btn btn-sm btn-outline-dark">Edit</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          class="mt-4 p-3 bg-white border rounded-3 d-flex align-items-center justify-content-between shadow-sm"
        >
          <span class="small text-muted fw-bold">BULK ACTIONS:</span>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-danger">
              Suspend Selected
            </button>
            <button class="btn btn-sm btn-outline-success">
              Approve Selected
            </button>
            <button class="btn btn-sm btn-outline-dark">Assign Booths</button>
          </div>
        </div>
      </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- <script src="../../assets/js/admin_module.js"></script> -->
  </body>
</html>
