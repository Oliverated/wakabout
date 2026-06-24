<?php
require_once __DIR__ . '/../includes/session_config.php';
require_once __DIR__ . '/../includes/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

if ($email === "admin@gmail.com" && $password === "admin123") {
    $_SESSION['user_id'] = 0;
    $_SESSION['username'] = 'Admin';
    $_SESSION['is_admin'] = true;
    header('Location: ../admin&384/dashboard.php');
    exit;
}

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($user = $result->fetch_assoc()) {
                if (password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    header('Location: ../index.php');
                    exit;
                }  else {
                    $error = 'Invalid email or password.';
                }
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Wakaabout</title>
  <link rel="stylesheet" href="../assets/required.css" />
  <link rel="stylesheet" href="auth.css">
</head>
<body>




  <!-- <div class="auth-card"> -->


    <form method="POST" class="auth-form" id="loginForm">
            <div class="brand-logo">
    <a href="../index.php"><img src="../assets/public/footerlogo.png" alt=""></a>
      </div>
    <?php if ($error): ?>
      <div class="auth-alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
      <div class="auth-ctn">
        
      <div class="auth-inp-block">
        <!-- <label for="email">Email Address</label> -->
        <input type="email" id="email" name="email" placeholder="Email Address" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none"  viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
        </svg>
      </div>

      <div class="auth-inp-block password-wrapper">
        <!-- <label for="password">Password</label> -->
        <!-- <div class=""> -->
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
          <button type="button" class="password-toggle" data-target="password" aria-label="Toggle password visibility">
            <svg class="eye-on" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            <svg class="eye-off" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
            </svg>
          </button>
        <!-- </div> -->
      </div>

      <button type="submit" class="auth-btn">Sign In</button>
    </div>

     <div class="auth-links">
      <p>Don't have an account? <a href="signup.php">Sign up</a></p>
      <p><a href="forgot_password.php">Forgot Password?</a></p>
    </div>   
    </form>


  <!-- </div> -->

<script>
// Password toggle
document.querySelectorAll('.password-toggle').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = btn.closest('.password-wrapper').querySelector('input');
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    btn.classList.toggle('active', isPassword);
  });
});
</script>

</body>
</html>
