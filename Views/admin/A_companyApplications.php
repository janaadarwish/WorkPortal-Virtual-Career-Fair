<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>WorkPortal Admin | Company & App Management</title>
  <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    :root {
      --brand-green: #28a745;
      --brand-slate: #1a1d21;
      --brand-light: #f4f7f6;
      --gold: #ffc107;
      --silver: #9e9e9e;
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

    .nav-tabs {
      border-bottom: 2px solid #eee;
    }

    .nav-tabs .nav-link {
      border: none;
      color: #666;
      font-weight: 600;
    }

    .nav-tabs .nav-link.active {
      color: var(--brand-green);
      border-bottom: 3px solid var(--brand-green);
      background: none;
    }

    .status-pill {
      font-size: 0.7rem;
      font-weight: 700;
      padding: 0.3rem 0.8rem;
      border-radius: 20px;
      text-transform: uppercase;
    }

    .pill-active {
      background: #e6f4ea;
      color: #1e7e34;
    }

    .pill-pending {
      background: #fff3cd;
      color: #856404;
    }

    .company-row:hover {
      background-color: #f8f9fa;
      cursor: pointer;
    }
  </style>
</head>

<body>
  <div class="dashboard-wrapper">
    <nav class="sidebar">
      <h4 class="fw-bold mb-5">
        <span class="text-white">WorkPortal</span> <br />
        <span class="text-success">Admin</span>
      </h4>
      <a href="A_adminDashboard.php" class="nav-link">System Health</a>
      <a href="A_companyApplications.php" class="nav-link active">Manage Companies</a>
      <a href="A_userManagement.php" class="nav-link">Users management</a>
      <a href="A_fairManagement.php" class="nav-link">Fair Traffic</a>
      <a href="A_reports.php" class="nav-link">Reporting</a>
      <a href="../../index.php" class="nav-link text-danger">Sign Out</a>
    </nav>

    <main class="main-content">
      <header class="d-flex justify-content-between align-items-center mb-5">
        <div>
          <h2 class="fw-bold mb-1">Company Directory</h2>
          <p class="text-muted small">
            Add new partners and monitor active booths.
          </p>
        </div>
        <button
          class="btn btn-success px-4 fw-bold shadow-sm"
          data-bs-toggle="modal"
          data-bs-target="#addCompanyModal">
          + Add New Company
        </button>
      </header>
      <div
        class="modal fade"
        id="addCompanyModal"
        tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content border-0 shadow">
            <div class="modal-header py-3">
              <h5 class="modal-title fw-bold">Register New Company</h5>
              <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"
                aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
              <form id="newCompanyForm">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Company Name</label>
                    <input
                      type="text"
                      class="form-control"
                      id="compName"
                      placeholder="e.g. Microsoft"
                      required />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Industry</label>
                    <select class="form-select" id="compIndustry">
                      <option value="Tech">Technology</option>
                      <option value="Finance">Finance</option>
                      <option value="Healthcare">Healthcare</option>
                      <option value="Engineering">Engineering</option>
                    </select>
                  </div>

                  <div class="col-md-12">
                    <label class="form-label">Company Website</label>
                    <div class="input-group">
                      <span class="input-group-text">https://</span>
                      <input
                        type="text"
                        class="form-control"
                        id="compWeb"
                        placeholder="www.company.com" />
                    </div>
                  </div>

                  <hr class="my-3 opacity-10" />

                  <div class="col-md-4">
                    <label class="form-label">Sponsorship Tier</label>
                    <select class="form-select" id="compTier">
                      <option value="Gold">Gold (Featured)</option>
                      <option value="Silver">Silver</option>
                      <option value="Bronze">Bronze</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Assign Booth</label>
                    <input
                      type="text"
                      class="form-control"
                      id="compBooth"
                      placeholder="e.g. B-202" />
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Initial Recruiter Slot</label>
                    <input
                      type="number"
                      class="form-control"
                      id="compSlots"
                      value="1" />
                  </div>

                  <div class="col-md-12">
                    <label class="form-label">Company Description / Bio</label>
                    <textarea
                      class="form-control"
                      rows="3"
                      placeholder="Brief overview for students..."></textarea>
                  </div>
                </div>
              </form>
            </div>
            <div class="modal-footer bg-light p-3">
              <button
                type="button"
                class="btn btn-outline-secondary btn-sm px-4"
                data-bs-dismiss="modal">
                Cancel
              </button>
              <button
                type="button"
                class="btn btn-success btn-sm px-4 fw-bold"
                onclick="addCompanyToTable()">
                Save & Add Company
              </button>
            </div>
          </div>
        </div>
      </div>
      <ul class="nav nav-tabs mb-4" id="companyTabs" role="tablist">
        <li class="nav-item">
          <button
            class="nav-link active"
            id="active-tab"
            data-bs-toggle="tab"
            data-bs-target="#active-content">
            Active Companies (52)
          </button>
        </li>
        <li class="nav-item">
          <button
            class="nav-link"
            id="pending-tab"
            data-bs-toggle="tab"
            data-bs-target="#pending-content">
            Pending Applications (3)
          </button>
        </li>
      </ul>

      <div class="tab-content">
        <div class="tab-pane fade show active" id="active-content">
          <div class="card overflow-hidden">
            <table class="table align-middle mb-0">
              <thead class="table-light small text-uppercase">
                <tr>
                  <th class="ps-4">Company</th>
                  <th>Tier</th>
                  <th>Booth</th>
                  <th>Live Recruiter</th>
                  <th class="text-end pe-4">Actions</th>
                </tr>
              </thead>
              <tbody>
<tbody>
                <tr class="company-row">
                  <td class="ps-4">
                    <div class="fw-bold">Adobe</div>
                    <div class="small text-muted">Software</div>
                  </td>
                  <td>April 25, 2026</td>
                  <td>
                    <span class="text-danger small">⚠️ Missing Business License</span>
                  </td>
                  <td><span class="badge bg-secondary">Medium</span></td>
                  <td class="text-end pe-4">
                    <button
                      onclick="deleteRow(this)"
                      class="btn btn-success btn-sm px-3"
                      onclick="alert('successful process')">
                      Approve
                    </button>
                    <button
                      onclick="deleteRow(this)"
                      class="btn btn-danger btn-sm px-3"
                      onclick="alert('Rejected')">
                      Reject
                    </button>
                  </td>
                </tr>
                <tr cass="company-row">
                  <td class="ps-4">
                    <div class="fw-bold">Tesla INC.</div>
                    <div class="small text-muted">Automotive</div>
                  </td>
                  <td>April 26, 2026</td>
                  <td>
                    <span class="text-warning small">⏳ Payment Verification</span>
                  </td>
                  <td>
                    <span class="badge bg-warning text-dark">High</span>
                  </td>
                  <td class="text-end pe-4">
                    <button onclick="deleteRow(this)" class="btn btn-success btn-sm px-3" ondblclick="alert('Successful process')">
                      Approve
                    </button>
                <button onclick="deleteRow(this)" class="btn btn-danger" ondblclick="('Rejected')">Reject</button>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="row mt-4">
        <div class="col-md-4">
          <div
            class="card p-4 text-center border-start border-success border-4">
            <h4 class="fw-bold">52</h4>
            <p class="small text-muted mb-0">Total Active Booths</p>
          </div>
        </div>
        <div class="col-md-4">
          <div
            class="card p-4 text-center border-start border-warning border-4">
            <h4 class="fw-bold">03</h4>
            <p class="small text-muted mb-0">Pending Reviews</p>
          </div>
        </div>
        <div class="col-md-4">
          <div
            class="card p-4 text-center border-start border-danger border-4">
            <h4 class="fw-bold">01</h4>
            <p class="small text-muted mb-0">Reported Issues</p>
          </div>
        </div>
      </div>
    </main>
  </div>


  <script>
  function deleteRow(btn) {
    let row = btn.parentNode.parentNode;
    row.remove();
}
</script>