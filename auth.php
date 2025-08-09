<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In & Sign Up</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="css/auth/auth.css">
</head>

<body>
  <?php if (isset($_SESSION['error'])): ?>
    <p class="error-message"><?php echo $_SESSION['error']; ?></p>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>
  <div class="container <?php echo (
    isset($_SESSION['error_username']) ||
    isset($_SESSION['error_email']) ||
    isset($_SESSION['error_password'])
  ) ? 'sign-up-mode' : ''; ?>">
    <div class="forms-container">
      <div class="signIn-signUp">
        <!-- Sign In Form -->
        <form action="auth-backend.php" method="POST" class="sign-in-form">
          <input type="hidden" name="login" value="1">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
          <h2 class="title">Sign In</h2>
          <div class="input-field-container">
            <div class="input-field">
              <i class='bx bx-user'></i>
              <input type="text" name="username" placeholder="User Name" required>
            </div>
            <?php if (isset($_SESSION['error_login_username'])): ?>
              <p class="error-message"><?php echo $_SESSION['error_login_username']; ?></p>
              <?php unset($_SESSION['error_login_username']); endif; ?>
          </div>
          <div class="input-field-container">
            <div class="input-field">
              <i class='bx bx-lock-alt'></i>
              <input type="password" name="password" placeholder="Password" required>
            </div>
            <?php if (isset($_SESSION['error_login_password'])): ?>
              <p class="error-message"><?php echo $_SESSION['error_login_password']; ?></p>
              <?php unset($_SESSION['error_login_password']); endif; ?>
          </div>
          <input type="submit" value="Log In" class="btn solid btn-form">
          <a href="auth-forgot-pw.php" class="forgot-password">Forgot Password?</a>
          <p class="social-text">Or Sign in with social platforms</p>
          <div class="social-media">
            <a href="auth-google-start.php" class="social-icon">
              <i class="bx bxl-google"></i>
            </a>
          </div>
        </form>
        <!-- Sign Up Form -->
        <form action="auth-backend.php" method="POST" class="sign-up-form">
          <input type="hidden" name="signup" value="1">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
          <h2 class="title">Sign Up</h2>
          <div class="input-field-container">
            <div class="input-field">
              <i class='bx bx-user'></i>
              <input type="text" name="username" placeholder="User Name" required>
            </div>
            <?php if (isset($_SESSION['error_username'])): ?>
              <p class="error-message"><?php echo $_SESSION['error_username']; ?></p>
              <?php unset($_SESSION['error_username']); endif; ?>
          </div>
          <div class="input-field-container">
            <div class="input-field">
              <i class='bx bx-envelope'></i>
              <input type="email" name="email" placeholder="Email" required>
            </div>
            <?php if (isset($_SESSION['error_email'])): ?>
              <p class="error-message"><?php echo $_SESSION['error_email']; ?></p>
              <?php unset($_SESSION['error_email']); endif; ?>
          </div>
          <div class="input-field-container">
            <div class="input-field">
              <i class='bx bx-lock-alt'></i>
              <input type="password" name="password" placeholder="Password" required>
            </div>
            <?php if (isset($_SESSION['error_password'])): ?>
              <p class="error-message"><?php echo $_SESSION['error_password']; ?></p>
              <?php unset($_SESSION['error_password']); endif; ?>
          </div>
          <input type="submit" value="Sign Up" class="btn solid btn-form">
          <p class="social-text">Or Sign up with social platforms</p>
          <div class="social-media">
            <a href="auth-google-start.php" class="social-icon">
              <i class="bx bxl-google"></i>
            </a>
          </div>
        </form>
      </div>
    </div>
    <!-- Panels for switching between sign in and sign up -->
    <div class="panels-container">
      <div class="panel left-panel">
        <div class="content">
          <h3>New Here?</h3>
          <p>Welcome to Thisara Travels & Tours. Please enter your username and password to access your account. If you
            don't have an account yet, you can sign up for free.</p>
          <button class="btn transparent" id="sign-up-btn">Sign up</button>
        </div>
        <img src="img/sl-tour.png" alt="" class="img">
      </div>
      <div class="panel right-panel">
        <div class="content">
          <h3>One of Us?</h3>
          <p>Welcome back! Please enter your username and password to access your account. If you don't have an account
            yet, you can sign up for free.</p>
          <button class="btn transparent" id="sign-in-btn">Sign in</button>
        </div>
        <img src="img/sl-map.png" alt="" class="img">
      </div>
    </div>
  </div>
  <script>
    // Toggle between sign-in and sign-up modes
    const sign_in = document.querySelector("#sign-in-btn");
    const sign_up = document.querySelector("#sign-up-btn");
    const container = document.querySelector(".container");
    sign_up.addEventListener('click', () => { container.classList.add('sign-up-mode'); });
    sign_in.addEventListener('click', () => { container.classList.remove('sign-up-mode'); });
  </script>
  <script>
    // Hide error messages as user types in input fields
    document.addEventListener('DOMContentLoaded', function () {
      const inputs = document.querySelectorAll('.input-field-container input');
      inputs.forEach(input => {
        input.addEventListener('input', function () {
          const parentField = input.closest('.input-field-container');
          const errorMessage = parentField.querySelector('.error-message');
          if (errorMessage) errorMessage.style.display = 'none';
        });
      });
    });  
  </script>
</body>

</html>