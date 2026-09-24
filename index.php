<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WorkPortal | Welcome</title>

    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />

    <style>
      :root {
        --brand-green: #28a745;
        --brand-green-dark: #1e7e34;
      }

      body,
      html {
        height: 100%;
        margin: 0;
        font-family:
          -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial,
          sans-serif;
        overflow: hidden;
      }

      .split-container {
        display: flex;
        height: 100vh;
        width: 100%;
      }

      .left-side {
        flex: 1;
        background:
          linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)),
          url("assets/images/background.jpg");
        background-size: cover;
        background-position: center;
        display: none;
      }

      .right-side {
        flex: 1;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 3rem;
        text-align: center;
      }

      @media (min-width: 992px) {
        .left-side {
          display: block;
        }
      }

      @keyframes fadeInRight {
        from {
          opacity: 0;
          transform: translateX(30px);
        }
        to {
          opacity: 1;
          transform: translateX(0);
        }
      }

      .welcome-box {
        max-width: 400px;
        animation: fadeInRight 0.8s ease-out;
      }

      .btn-portal {
        padding: 1rem 2rem;
        font-weight: 700;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
        width: 100%;
        margin-bottom: 1rem;
      }

      .btn-login {
        background-color: var(--brand-green);
        color: white;
        border: none;
      }

      .btn-login:hover {
        background-color: var(--brand-green-dark);
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(40, 167, 69, 0.2);
        color: white;
      }

      .btn-signup {
        background-color: transparent;
        color: var(--brand-green);
        border: 2px solid var(--brand-green);
      }

      .btn-signup:hover {
        background-color: #f0fdf4;
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        color: var(--brand-green-dark);
      }

      .brand-logo {
        color: var(--brand-green);
        font-weight: 800;
        font-size: 2.5rem;
        margin-bottom: 1rem;
      }
    </style>
  </head>
  <body>
    <div class="split-container">
      <div class="left-side"></div>

      <div class="right-side">
        <div class="welcome-box">
          <div class="brand-logo">WorkPortal</div>
          <h1 class="fw-bold mb-3">Professional Success Starts Here.</h1>
          <p class="text-muted mb-5 lead">
            The unified platform for students to find careers and recruiters to
            find talent.
          </p>

          <div class="d-grid gap-2">
            <a
              href="Views/login.php"
              class="btn btn-portal btn-login text-decoration-none"
            >
              Sign In
            </a>
          </div>

          <div class="mt-5 text-muted small">
            &copy; 2026 WorkPortal <br />
            Designed for high-performance teams.
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
