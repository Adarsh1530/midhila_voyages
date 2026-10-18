<?php
/**
 * MIDHILA VOYAGES — Admin Portal Authentication
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

if (is_admin_logged_in()) {
    header('Location: ' . ADMIN_URL . '/dashboard.php');
    exit;
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        $errorMessage = 'Security validation failed. Please reload the page.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $errorMessage = 'Please enter both username and password.';
        } else {
            $result = admin_login($username, $password);
            if ($result['success']) {
                $redirectTo = $_SESSION['admin_redirect_to'] ?? (ADMIN_URL . '/dashboard.php');
                unset($_SESSION['admin_redirect_to']);
                header('Location: ' . $redirectTo);
                exit;
            } else {
                $errorMessage = $result['message'];
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
  <title>Admin Portal Login | MIDHILA VOYAGES</title>
  
  <link rel="icon" type="image/svg+xml" href="<?= ASSETS_URL ?>/images/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
      background-color: #06101E;
      color: #F8FAFC;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      position: relative;
    }
    .login-box {
      width: 100%;
      max-width: 440px;
      background: #0A192F;
      border: 1px solid rgba(197, 160, 89, 0.3);
      border-radius: 16px;
      padding: 40px 36px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
      position: relative;
      z-index: 2;
    }
    .brand-logo {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      margin-bottom: 28px;
    }
    .brand-title {
      font-family: 'Cinzel', serif;
      font-size: 1.35rem;
      letter-spacing: 0.12em;
      color: #FFF;
      line-height: 1.1;
    }
    .brand-sub {
      font-size: 0.65rem;
      letter-spacing: 0.28em;
      color: #C5A059;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      font-size: 0.82rem;
      font-weight: 600;
      color: #CBD5E1;
      margin-bottom: 6px;
      letter-spacing: 0.04em;
    }
    .form-group input {
      width: 100%;
      padding: 12px 16px;
      background: rgba(6, 16, 30, 0.8);
      border: 1px solid #1E3A5F;
      border-radius: 8px;
      color: #FFF;
      font-size: 0.95rem;
      outline: none;
      transition: border-color 0.2s;
    }
    .form-group input:focus {
      border-color: #C5A059;
    }
    .btn-login {
      width: 100%;
      padding: 13px;
      background: linear-gradient(135deg, #C5A059 0%, #D4AF37 100%);
      color: #06101E;
      font-weight: 700;
      font-size: 0.95rem;
      letter-spacing: 0.05em;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
      margin-top: 8px;
    }
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(197, 160, 89, 0.35);
    }
    .error-alert {
      background: rgba(239, 68, 68, 0.15);
      border: 1px solid rgba(239, 68, 68, 0.4);
      color: #FCA5A5;
      padding: 12px 14px;
      border-radius: 8px;
      font-size: 0.86rem;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

  <div class="login-box">
    
    <div class="brand-logo">
      <img src="<?= ASSETS_URL ?>/images/logo/symbol.svg" alt="Midhila Voyages Emblem" width="44" height="44">
      <div>
        <div class="brand-title">MIDHILA</div>
        <div class="brand-sub">VOYAGES</div>
      </div>
    </div>

    <div style="text-align:center;margin-bottom:24px;">
      <h2 style="font-size:1.25rem;color:#FFF;margin-bottom:4px;">Agency Portal Sign In</h2>
      <p style="font-size:0.82rem;color:#94A3B8;">Authorized travel managers and staff only</p>
    </div>

    <?php if (!empty($errorMessage)): ?>
      <div class="error-alert">
        ⚠ <?= e($errorMessage) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= ADMIN_URL ?>/login.php">
      <?= csrf_field() ?>

      <div class="form-group">
        <label for="username">Username or Email</label>
        <input type="text" name="username" id="username" required autocomplete="username" placeholder="e.g. admin" value="<?= e($_POST['username'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required autocomplete="current-password" placeholder="••••••••">
      </div>

      <button type="submit" class="btn-login">Sign In to Dashboard &rarr;</button>
    </form>

    <div style="margin-top:24px;text-align:center;">
      <a href="<?= BASE_URL ?>/index.php" style="font-size:0.8rem;color:#94A3B8;text-decoration:none;">&larr; Back to Public Website</a>
    </div>

  </div>

</body>
</html>
