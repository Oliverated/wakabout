<?php
session_start();
require_once __DIR__ . '/../env/env.php';
Env::load(__DIR__ . '/../env/.env');
require_once __DIR__ . '/../includes/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

if ($email === Env::get('ADMIN_EMAIL') && $password === Env::get('ADMIN_PASSWORD')) {
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
  <title>Login - Wakabout</title>
  <link rel="stylesheet" href="auth.css">
</head>
<body>
  <div class="auth-container">
    <div class="auth-brand">
      <a href="../index.php" style="text-decoration:none;"><h2>Waka<span>bout</span></h2></a>
    </div>
    <h3 class="auth-form-title">Welcome Back</h3>
    
    <?php if ($error): ?>
      <div class="auth-alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="auth-input-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="auth-input-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="auth-btn">Login</button>
    </form>

    <div class="auth-links">
      <p>Don't have an account? <a href="signup.php">Sign up</a></p>
      <p><a href="forgot_password.php">Forgot Password?</a></p>
    </div>
  </div>
</body>
</html>
