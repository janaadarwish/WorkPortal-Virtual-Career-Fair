<?php
session_start();

// Security: Ensure the user is logged in AND is a Recruiter
if (!isset($_SESSION['User_ID']) || $_SESSION['User_Role'] !== 'Recruiter') {
    header("Location: ../login.php");
    exit();
}

// Data is pulled from session (populated during login)
$recruiterName = $_SESSION['User_Name'];
$company = $_SESSION['Company_Name'] ?? 'Independent Recruiter';
$title = $_SESSION['Job_Title'] ?? 'Hiring Manager';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WorkPortal | Recruiter Profile</title>
  <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="../../assets/css/rec.css" rel="stylesheet" />

  </head>
  <body>
    <nav class="sidebar">
        <h4 class="mb-5 fw-bold">
          WorkPortal<br /><span style="color: var(--brand-green)"
            >Recruiter</span
          >
        </h4>
        <div class="nav flex-column h-100">
          <a href="R_recruiterDashboard.php" class="nav-link">Dashboard</a>
            <a href="R_candidatePipeline.php" class="nav-link">Candidates</a>
            <a href="R_schedule.php" class="nav-link">Schedule</a>
            <a href="R_analytics.php" class="nav-link active">Analytics</a>
            <a href="R_messages.php" class="nav-link">Messages</a>
            <a href="R_profile.php" class="nav-link ">Profile</a>
            <a href="../../index.php" class="nav-link text-danger">Logout</a>
        </div>
      </nav>

    <main class="main-content">
      <div class="profile-card">
        <div class="profile-banner"></div>
        <div class="profile-header">
    <div class="row align-items-center">
        <div class="col-md-auto">
            <!-- Recruiter Icon or Company Logo -->
            <div class="profile-pic d-flex align-items-center justify-content-center">
                <i class="bi bi-building fs-1 text-muted"></i>
            </div>
        </div>
        <div class="col">
            <!-- Dynamic Name -->
            <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($recruiterName); ?></h2>
            
            <!-- Dynamic Company & Title -->
            <p class="text-muted mb-2">
                <?php echo htmlspecialchars($title); ?> at 
                <span class="text-primary fw-bold"><?php echo htmlspecialchars($company); ?></span>
            </p>
            
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#editRecruiterModal">
                    <i class="bi bi-pencil me-1"></i> Edit Business Profile
                </button>
            </div>
        </div>
    </div>
</div>
              </div>
            </div>
          </div>

          <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div
              id="liveToast"
              class="toast align-items-center text-white bg-success border-0"
              role="alert"
              aria-live="assertive"
              aria-atomic="true"
            >
              <div class="d-flex">
                <div class="toast-body">
                  <i class="bi bi-check-circle me-2"></i> Job posted
                  successfully!
                </div>
                <button
                  type="button"
                  class="btn-close btn-close-white me-2 m-auto"
                  data-bs-dismiss="offcanvas"
                  aria-label="Close"
                ></button>
              </div>
            </div>
          </div>

          <script>
            document
              .getElementById("jobPostingForm")
              .addEventListener("submit", function (e) {
                e.preventDefault();

                const title = document.getElementById("jobTitle").value;
                const location = document.getElementById("jobLocation").value;

                const jobContainer = document.querySelector(
                  ".profile-card .fw-bold.mb-4",
                ).parentElement; // Finds the "Current Openings" card

                const newJobHtml = `
            <div class="job-card d-flex justify-content-between align-items-center animate__animated animate__fadeIn">
                <div>
                    <h6 class="fw-bold mb-1">${title}</h6>
                    <small class="text-muted">${location} • Just now</small>
                </div>
                <button class="btn btn-sm btn-outline-success">Manage Apps</button>
            </div>
        `;

                const openingsHeader = jobContainer.querySelector("h5");
                openingsHeader.insertAdjacentHTML("afterend", newJobHtml);

                const modal = bootstrap.Modal.getInstance(
                  document.getElementById("postJobModal"),
                );
                modal.hide();
                this.reset();

                const toastBootstrap = bootstrap.Toast.getOrCreateInstance(
                  document.getElementById("liveToast"),
                );
                toastBootstrap.show();
              });
          </script>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-8">
          <div class="profile-card p-4">
            <h5 class="fw-bold mb-3">About Me</h5>
            <p class="text-secondary">
              Dedicated to finding the next generation of software engineering
              talent. With over 8 years of experience in technical recruiting, I
              specialize in connecting university students with internship and
              entry-level opportunities that kickstart their careers.
            </p>
            <hr class="my-4" />
            <h5 class="fw-bold mb-3">Recruitment Focus</h5>
            <div class="d-flex gap-2 flex-wrap">
              <span class="badge bg-light text-dark border p-2 px-3"
                >Backend Engineering</span
              >
              <span class="badge bg-light text-dark border p-2 px-3"
                >Frontend / React</span
              >
              <span class="badge bg-light text-dark border p-2 px-3"
                >Cybersecurity</span
              >
              <span class="badge bg-light text-dark border p-2 px-3"
                >Information Systems</span
              >
            </div>
          </div>

          <div class="profile-card p-4">
            <h5 class="fw-bold mb-4">Current Openings</h5>

            <div
              class="job-card d-flex justify-content-between align-items-center"
            >
              <div>
                <h6 class="fw-bold mb-1">
                  Software Engineer Intern (Summer 2026)
                </h6>
                <small class="text-muted">Remote • Published 2 days ago</small>
              </div>
              <button class="btn btn-sm btn-outline-success">
                Manage Apps
              </button>
            </div>

            <div
              class="job-card d-flex justify-content-between align-items-center"
            >
              <div>
                <h6 class="fw-bold mb-1">Junior Data Analyst</h6>
                <small class="text-muted"
                  >New York, NY • Published 1 week ago</small
                >
              </div>
              <button class="btn btn-sm btn-outline-success">
                Manage Apps
              </button>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="row g-3 mb-4">
            <div class="col-6">
              <div class="stat-box">
                <h3 class="fw-bold text-success">142</h3>
                <small class="text-muted">Interviews Held</small>
              </div>
            </div>
            <div class="col-6">
              <div class="stat-box">
                <h3 class="fw-bold text-primary">28</h3>
                <small class="text-muted">Hires Made</small>
              </div>
            </div>
          </div>

          <div class="profile-card p-4">
            <h6 class="fw-bold text-muted small mb-3">COMPANY INFO</h6>
            <div class="text-center mb-3">
              <div class="bg-light rounded p-4 mb-2 d-inline-block">
                <i class="bi bi-google fs-1"></i>
              </div>
              <h6 class="fw-bold">Google</h6>
            </div>
            <ul class="list-unstyled small text-secondary">
              <li class="mb-2">
                <i class="bi bi-geo-alt me-2"></i>Mountain View, CA
              </li>
              <li class="mb-2">
                <i class="bi bi-link-45deg me-2"></i>google.com/careers
              </li>
              <li><i class="bi bi-people me-2"></i>10,000+ Employees</li>
            </ul>
          </div>
        </div>
      </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
