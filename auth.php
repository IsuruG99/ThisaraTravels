<?php
session_start(); ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In & Sign Up</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
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
          <input type="submit" value="Log In" class="btn solid">
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
          <input type="submit" value="Sign Up" class="btn solid">
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
      </div>
      <div class="panel right-panel">
        <div class="content">
          <h3>One of Us?</h3>
          <p>Welcome back! Please enter your username and password to access your account. If you don't have an account
            yet, you can sign up for free.</p>
          <button class="btn transparent" id="sign-in-btn">Sign in</button>
        </div>
      </div>
    </div>
  </div>
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap");

    * {
      padding: 0;
      margin: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
      text-decoration: none;
    }

    .forgot-password {
      margin-top: 1rem;
      color: #0ef;
      font-size: 0.9rem;
      cursor: pointer;
    }

    .forgot-password:hover {
      text-decoration: underline;
    }

    .error-message {
      color: red;
      font-size: 0.875rem;
      margin-left: 10px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 100%;
    }

    .container {
      position: relative;
      width: 100%;
      min-height: 100vh;
      background-color: #fff;
      overflow: hidden;
    }

    .container::before {
      content: "";
      position: absolute;
      width: 50%;
      height: 100%;
      background: linear-gradient(-45deg, #0ef, #4481ed);
      top: 0;
      left: 0;
      border-radius: 0;
      z-index: 6;
    }

    .forms-container {
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
    }

    .signIn-signUp {
      position: absolute;
      top: 50%;
      left: 75%;
      transform: translate(-50%, -50%);
      width: 50%;
      display: grid;
      grid-template-columns: 1fr;
      z-index: 5;
    }

    form {
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      padding: 0 5rem;
      overflow: hidden;
      grid-column: 1 / 2;
      grid-row: 1 / 2;
    }

    form.sign-in-form {
      z-index: 2;
    }

    form.sign-up-form {
      z-index: 1;
      opacity: 0;
    }

    .title {
      font-size: 2.2rem;
      color: #444;
      margin-bottom: 10px;
    }

    .input-field {
      max-width: 380px;
      width: 100%;
      height: 55px;
      background-color: #f0f0f0;
      margin: 10px 0;
      border-radius: 55px;
      display: flex;
      grid-template-columns: 15% 85%;
      padding: 0.4rem;
      align-items: center;
      flex-grow: 1;
    }

    .input-field i {
      text-align: center;
      line-height: 40px;
      color: #acacac;
      font-size: 1.5rem;
    }

    .input-field input {
      background: none;
      outline: none;
      border: none;
      line-height: 1;
      font-weight: 600;
      font-size: 1.1rem;
      color: #333;
      flex: 1;
    }

    .input-field input::placeholder {
      color: #807f7f;
      font-weight: 400;
    }

    .btn {
      width: 150px;
      height: 49px;
      border: none;
      outline: none;
      border-radius: 49px;
      cursor: pointer;
      background-color: #FBDC5C;
      color: #000;
      text-transform: uppercase;
      font-weight: 600;
      margin: 10px 0;
    }

    .btn:hover {
      background-color: #20DF0A;
      color: #fff;
    }

    .social-text {
      padding: 0.7rem 0;
      font-size: 1rem;
    }

    .social-media {
      display: flex;
      justify-content: center;
    }

    .social-icon {
      height: 46px;
      width: 46px;
      border: 1px solid #333;
      margin: 0 0.45rem;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #333;
      font-size: 1.1rem;
      border-radius: 50%;
    }

    .social-icon:hover {
      background: #D81324;
      color: #fff;
      border: none;
    }

    .panels-container {
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      display: grid;
      grid-template-columns: repeat(2, 1fr);
    }

    .panel {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      justify-content: space-around;
      text-align: center;
      z-index: 7;
    }

    .left-panel {
      pointer-events: all;
      padding: 3rem 17% 2rem 12%;
    }

    .right-panel {
      pointer-events: none;
      padding: 3rem 12% 2rem 17%;
    }

    .panel .content {
      color: #fff;
    }

    .panel h3 {
      font-weight: 600;
      line-height: 1;
      font-size: 1.5rem;
    }

    .panel p {
      font-size: 0.95rem;
      padding: 0.7rem 0;
    }

    .btn.transparent {
      margin: 0;
      background: none;
      border: 2px solid #fff;
      width: 130px;
      height: 41px;
      font-weight: 600;
      font-size: 0.8rem;
    }

    .img {
      width: 100%;
      transition: 0.3s 0.3s ease-in-out;
    }

    .right-panel .content,
    .right-panel .img {
      transform: translateX(800px);
    }

    .container.sign-up-mode::before {
      left: auto;
      right: 0;
    }

    .container.sign-up-mode .left-panel .img,
    .container.sign-up-mode .left-panel .content {
      transform: translateX(-800px);
    }

    .container.sign-up-mode .right-panel .content,
    .container.sign-up-mode .right-panel .img {
      transform: translateX(0px);
    }

    .container.sign-up-mode .left-panel {
      pointer-events: none;
    }

    .container.sign-up-mode .right-panel {
      pointer-events: all;
    }

    .container.sign-up-mode .signIn-signUp {
      left: 25%;
    }

    .container.sign-up-mode form.sign-in-form {
      z-index: 1;
      opacity: 0;
    }

    .container.sign-up-mode form.sign-up-form {
      z-index: 2;
      opacity: 1;
    }
  </style>
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
    });  </script>
</body>

</html>