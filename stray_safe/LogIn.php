<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>StraySafe — Login & Sign Up</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Bitter:wght@700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="login.css">
</head>
<body>
    <?php if (isset($_GET['error']) && $_GET['error'] == 'username taken.'): ?>
      <p style="color: red; font-size: 0.9rem; margin-bottom: 10px;">That username is already taken. Please choose another.</p>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] == 'passwordchanged'): ?>
      <p style="color: #2e6417; font-size: 0.9rem; margin-bottom: 10px; font-weight: bold;">Password successfully changed! You can now log in.</p>
    <?php endif; ?>

  <div class="bg-paws" aria-hidden="true">
    <img src="paws.png" alt="paw logo" class="paw paw-left" />
    <img src="paws.png" alt="paw logo" class="paw paw-top-right" />
    <img src="paws.png" alt="paw logo" class="paw paw-bottom-right" />
  </div>

  <main class="main-wrapper">
    <section class="auth-card">
      <div class="left-panel">
        <div class="brand-header">
          <img src="paws.png" alt="StraySafe paw logo" class="brand-paw" />
          <h1>StraySafe</h1>
        </div>

        <p class="hero-copy">
          Join our community in helping stray animals find safety, care, and
          loving homes. Discover animals in need, share their locations, and
          connect with people who want to make a difference.
        </p>

        <div class="illustration-footer">
          <img src="dog.png" alt="Dog, cat, and leaves illustration" class="animals-img" />
        </div>
      </div>

      <div class="right-panel">
        <h2 class="welcome-title">WELCOME!</h2>

        <?php if (isset($_GET['error'])): ?>
            <p style="color: #c53030; font-size: 0.85rem; margin-bottom: 0.75rem;"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

        <div class="auth-toggle" role="tablist">
          <div class="teal-slider" id="tealSlider"></div>
          <button type="button" class="toggle-btn active" id="loginTab" role="tab" aria-selected="true">
            Log in
          </button>
          <button type="button" class="toggle-btn" id="signupTab" role="tab" aria-selected="false">
            Sign up
          </button>
        </div>

        <form class="auth-form" action="auth_process.php" method="POST">
          <input type="hidden" id="authAction" name="action" value="login">

          <div class="input-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required />
          </div>

          <div class="input-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required />
          </div>

          <div class="input-group hidden" id="contactGroup">
            <label for="contact">Email/Contact no.:</label>
            <input type="text" id="contact" name="contact" placeholder="e.g. juandelacruz@email.com or 09123456789" />
          </div>

          <div class="button-row" id="buttonRow">
            <button type="submit" class="btn-primary" id="submitBtn">LOG IN</button>
            <a href="forgot_password.php" class="btn-secondary" id="forgotBtn">Forgot Password?</a>
          </div>

          <div class="divider" style="display: none;">or</div>

          <button type="button" class="btn-google" style="display: none;">
            <img src="gcon.png" alt="Google icon" class="google-icon" />
            <span>CONTINUE WITH GOOGLE</span>
          </button>
        </form>
      </div>
    </section>
  </main>

            <footer>
                <div class="footer-box f1">
                    <img src="paw.png" alt="Logo">
                    <p>StraySafe</p>
                </div>
                <div class="footer-box f2">
                    <p>Information and Welfare Tracking System for Community Animals</p>
                </div>
                <div class="footer-box f3">
                    <p>Student project</p>
                </div>
            </footer>

  <script>
    const loginTab = document.getElementById('loginTab');
    const signupTab = document.getElementById('signupTab');
    const submitBtn = document.getElementById('submitBtn');
    const forgotBtn = document.getElementById('forgotBtn');
    const buttonRow = document.getElementById('buttonRow');
    const contactGroup = document.getElementById('contactGroup');
    const contactInput = document.getElementById('contact');
    const authToggle = document.querySelector('.auth-toggle');
    const authAction = document.getElementById('authAction');

    loginTab.addEventListener('click', () => {
      loginTab.classList.add('active');
      loginTab.setAttribute('aria-selected', 'true');
      signupTab.classList.remove('active');
      signupTab.setAttribute('aria-selected', 'false');
      authToggle.classList.remove('signup-mode');

      authAction.value = 'login';
      submitBtn.textContent = 'LOG IN';
      forgotBtn.style.display = 'inline-block';
      buttonRow.classList.remove('center-mode');

      contactGroup.classList.add('hidden');
      contactInput.required = false;
      contactInput.value = '';
    });

    signupTab.addEventListener('click', () => {
      signupTab.classList.add('active');
      signupTab.setAttribute('aria-selected', 'true');
      loginTab.classList.remove('active');
      loginTab.setAttribute('aria-selected', 'false');
      authToggle.classList.add('signup-mode');

      authAction.value = 'signup';
      submitBtn.textContent = 'SIGN UP';
      forgotBtn.style.display = 'none';
      buttonRow.classList.add('center-mode');

      contactGroup.classList.remove('hidden');
      contactInput.required = true;
    });
  </script>
</body>
</html>