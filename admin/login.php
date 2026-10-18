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
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/animations.css">

  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
      background-color: #070D1E;
      color: #F8FAFC;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      position: relative;
      overflow: hidden;
    }
    .login-box {
      width: 100%;
      max-width: 440px;
      background: rgba(14, 23, 46, 0.78);
      backdrop-filter: blur(28px) saturate(180%);
      -webkit-backdrop-filter: blur(28px) saturate(180%);
      border: 1px solid rgba(196, 181, 253, 0.28);
      border-radius: 20px;
      padding: 42px 36px;
      box-shadow: inset 0 1px 1px 0 rgba(255, 255, 255, 0.2), 0 30px 60px -10px rgba(0, 0, 0, 0.6);
      position: relative;
      z-index: 5;
    }
    .brand-logo {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      margin-bottom: 28px;
    }
    .brand-logo img {
      filter: drop-shadow(0 0 12px rgba(141, 164, 239, 0.6));
    }
    .brand-title {
      font-family: 'Cinzel', serif;
      font-size: 1.35rem;
      letter-spacing: 0.12em;
      color: #FFF;
      line-height: 1.1;
      text-shadow: 0 0 12px rgba(196, 181, 253, 0.3);
    }
    .brand-sub {
      font-size: 0.65rem;
      letter-spacing: 0.28em;
      color: #C4B5FD;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      font-size: 0.82rem;
      font-weight: 600;
      color: #CBD5E1;
      margin-bottom: 8px;
      letter-spacing: 0.04em;
    }
    .form-group input {
      width: 100%;
      padding: 12px 16px;
      background: rgba(10, 17, 40, 0.75);
      border: 1.5px solid rgba(196, 181, 253, 0.3);
      border-radius: 10px;
      color: #FFF;
      font-size: 0.95rem;
      outline: none;
      transition: all 0.2s;
    }
    .form-group input:focus {
      background: rgba(10, 17, 40, 0.95);
      border-color: #8DA4EF;
      box-shadow: 0 0 0 3px rgba(141, 164, 239, 0.25);
    }
    .btn-login {
      width: 100%;
      padding: 13px;
      background: linear-gradient(135deg, #7C3AED 0%, #4F46E5 100%);
      color: #FFF;
      font-weight: 700;
      font-size: 0.95rem;
      letter-spacing: 0.05em;
      border: 1px solid rgba(255, 255, 255, 0.35);
      border-radius: 10px;
      cursor: pointer;
      box-shadow: 0 8px 24px -4px rgba(124, 58, 237, 0.45), inset 0 1px 1px rgba(255, 255, 255, 0.4);
      transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
      margin-top: 8px;
    }
    .btn-login:hover {
      background: linear-gradient(135deg, #8B5CF6 0%, #6366F1 100%);
      transform: translateY(-2px);
      box-shadow: 0 12px 30px -4px rgba(139, 92, 246, 0.6), inset 0 1px 1px rgba(255, 255, 255, 0.6);
    }
    .error-alert {
      background: rgba(239, 68, 68, 0.18);
      border: 1px solid rgba(239, 68, 68, 0.45);
      color: #FCA5A5;
      padding: 12px 14px;
      border-radius: 10px;
      font-size: 0.86rem;
      margin-bottom: 20px;
      box-shadow: 0 0 16px rgba(239, 68, 68, 0.2);
    }
  </style>
</head>
<body>

  <!-- Ambient Floating Mesh Orbs -->
  <div class="ambient-mesh" aria-hidden="true">
    <div class="ambient-orb ambient-orb--1"></div>
    <div class="ambient-orb ambient-orb--2"></div>
    <div class="ambient-orb ambient-orb--3"></div>
    <div class="ambient-orb ambient-orb--4"></div>
  </div>

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
