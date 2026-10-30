<?php
/**
 * MIDHILA VOYAGES — Admin Portal Authentication
 * Editorial Luxury Pure-White Portal Inspired by Brand Motion
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
  <title>User Sign In | MIDHILA VOYAGES</title>
  
  <link rel="icon" type="image/svg+xml" href="<?= ASSETS_URL ?>/images/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/animations.css">

  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
      background-color: #FFFFFF;
      color: #0F172A;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      position: relative;
      overflow-x: hidden;
    }
    
    /* Subtle 20% watermark destination underlayer */
    .login-watermark {
      position: absolute;
      inset: 0;
      background-image: url('<?= ASSETS_URL ?>/images/hero/kerala-backwaters.jpg');
      background-size: cover;
      background-position: center;
      opacity: 0.14;
      filter: saturate(110%);
      pointer-events: none;
      z-index: 1;
      mask-image: radial-gradient(circle at center, rgba(0,0,0,1) 30%, rgba(0,0,0,0) 80%);
      -webkit-mask-image: radial-gradient(circle at center, rgba(0,0,0,1) 30%, rgba(0,0,0,0) 80%);
    }

    .login-box {
      width: 100%;
      max-width: 440px;
      background: #FFFFFF;
      border: 1px solid #E2E8F0;
      border-radius: 16px;
      padding: 44px 38px;
      box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.08), 0 1px 3px rgba(0, 0, 0, 0.04);
      position: relative;
      z-index: 5;
    }
    .brand-logo {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 24px;
    }
    .brand-logo img {
      max-width: 220px;
      height: 52px;
      width: auto;
      object-fit: contain;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      font-size: 0.82rem;
      font-weight: 600;
      color: #334155;
      margin-bottom: 8px;
      letter-spacing: 0.02em;
    }
    .form-group input {
      width: 100%;
      padding: 12px 16px;
      background: #FFFFFF;
      border: 1.5px solid #CBD5E1;
      border-radius: 8px;
      color: #0F172A;
      font-size: 0.95rem;
      outline: none;
      transition: all 0.2s ease;
    }
    .form-group input:focus {
      background: #FFFFFF;
      border-color: #0F172A;
      box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08);
    }
    .btn-login {
      width: 100%;
      padding: 13px;
      background: #0F172A;
      color: #FFFFFF;
      font-weight: 600;
      font-size: 0.95rem;
      letter-spacing: 0.02em;
      border: 1px solid #0F172A;
      border-radius: 8px;
      cursor: pointer;
      box-shadow: 0 4px 14px rgba(15, 23, 42, 0.15);
      transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
      margin-top: 8px;
    }
    .btn-login:hover {
      background: #1E293B;
      border-color: #1E293B;
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(15, 23, 42, 0.2);
    }
    .error-alert {
      background: #FEF2F2;
      border: 1px solid #FECACA;
      color: #991B1B;
      padding: 12px 14px;
      border-radius: 8px;
      font-size: 0.86rem;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

  <!-- Subtle Watermark Underlayer -->
  <div class="login-watermark" aria-hidden="true"></div>

  <div class="login-box">
    
    <div class="brand-logo">
      <img src="<?= ASSETS_URL ?>/images/logo/midhila-voyages-logo.png" 
           srcset="<?= ASSETS_URL ?>/images/logo/midhila-voyages-logo@2x.png 2x" 
           alt="Midhila Voyages">
    </div>

    <div style="text-align:center;margin-bottom:24px;">
      <h2 style="font-size:1.3rem;color:#0F172A;font-weight:700;margin-bottom:4px;letter-spacing:-0.01em;">User Portal Sign In</h2>
      <p style="font-size:0.84rem;color:#64748B;">Enter your authorized Username &amp; Password</p>
      
      <div style="display:flex;justify-content:center;gap:6px;margin-top:10px;flex-wrap:wrap;">
        <span style="font-size:0.68rem;font-weight:700;background:#0F172A;color:#FFFFFF;padding:2px 8px;border-radius:4px;">SUPER ADMIN</span>
        <span style="font-size:0.68rem;font-weight:700;background:#EFF6FF;color:#1D4ED8;padding:2px 8px;border-radius:4px;border:1px solid #BFDBFE;">TRAVEL MANAGE</span>
        <span style="font-size:0.68rem;font-weight:700;background:#ECFDF5;color:#047857;padding:2px 8px;border-radius:4px;border:1px solid #A7F3D0;">PAYMENT HANDLE</span>
      </div>
    </div>

    <?php if (!empty($errorMessage)): ?>
      <div class="error-alert">
        ⚠ <?= e($errorMessage) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= ADMIN_URL ?>/login.php">
      <?= csrf_field() ?>

      <div class="form-group">
        <label for="username">Username (Login ID)</label>
        <input type="text" name="username" id="username" required autocomplete="username" placeholder="e.g. admin, travel, payment" value="<?= e($_POST['username'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required autocomplete="current-password" placeholder="••••••••">
      </div>

      <button type="submit" class="btn-login">Sign In &rarr;</button>
    </form>

    <div style="margin-top:28px;text-align:center;border-top:1px solid #F1F5F9;padding-top:18px;">
      <a href="<?= BASE_URL ?>/index.php" style="font-size:0.84rem;color:#64748B;text-decoration:none;font-weight:500;">&larr; Back to Public Website</a>
    </div>

  </div>

</body>
</html>