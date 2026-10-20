<?php
/**
 * MIDHILA VOYAGES — Admin Header & Navigation Layout
 * Pure-White Editorial Administration Design Inspired by Brand Motion
 */

require_once __DIR__ . '/admin-init.php';

$adminScript = basename($_SERVER['PHP_SELF']);
$pageTitle = $adminTitle ?? 'Admin Dashboard | MIDHILA VOYAGES';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
  <title><?= e($pageTitle) ?></title>
  
  <link rel="icon" type="image/svg+xml" href="<?= ASSETS_URL ?>/images/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/animations.css">
  
  <script>
    window.MIDHILA_ASSETS_URL = '<?= ASSETS_URL ?>';
    window.MIDHILA_BASE_URL = '<?= BASE_URL ?>';
    window.addEventListener('error', function(e) {
      if (e.target && e.target.tagName === 'IMG' && !e.target.dataset.fallbackApplied) {
        e.target.dataset.fallbackApplied = 'true';
        e.target.src = window.MIDHILA_ASSETS_URL + '/images/placeholder.svg';
      }
    }, true);
  </script>
  
  <style>
    :root {
      --admin-navy: #0F172A;
      --admin-navy-deep: #020617;
      --admin-sidebar: #FFFFFF;
      --admin-bg: #F8FAFC;
      --admin-card: #FFFFFF;
      --admin-card-border: #E2E8F0;
      --admin-gold: #C5A059;
      --admin-border: #E2E8F0;
      --admin-text: #0F172A;
      --admin-muted: #64748B;
      --radius-sm: 6px;
      --radius-md: 10px;
      --radius-lg: 16px;
      --radius-pill: 9999px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    
    body {
      font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
      background-color: var(--admin-bg);
      color: var(--admin-text);
      display: flex;
      min-height: 100vh;
      overflow-x: hidden;
      position: relative;
    }
    
    a { color: inherit; text-decoration: none; }
    
    /* Pure White Luxury Sidebar */
    .admin-sidebar {
      width: 260px;
      background: #FFFFFF;
      color: #334155;
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
      border-right: 1px solid var(--admin-border);
      position: relative;
      z-index: 10;
      box-shadow: 2px 0 12px rgba(15, 23, 42, 0.03);
    }
    
    .admin-brand {
      padding: 22px 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      border-bottom: 1px solid var(--admin-border);
    }
    
    .admin-nav {
      padding: 20px 12px;
      display: flex;
      flex-direction: column;
      gap: 3px;
      flex-grow: 1;
      overflow-y: auto;
    }
    
    .admin-nav-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 14px;
      border-radius: 8px;
      font-size: 0.88rem;
      font-weight: 500;
      color: #475569;
      transition: all 0.2s ease;
      border: 1px solid transparent;
    }
    
    .admin-nav-item:hover {
      background: #F1F5F9;
      color: #0F172A;
    }
    
    .admin-nav-item.is-active {
      background: #0F172A;
      color: #FFFFFF;
      font-weight: 600;
      box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
    }
    
    .admin-nav-section-title {
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: #94A3B8;
      padding: 16px 14px 6px;
      font-weight: 700;
    }

    /* Main Content Layout */
    .admin-main {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      min-width: 0;
      overflow-y: auto;
      position: relative;
      z-index: 5;
      background: #F8FAFC;
    }
    
    .admin-topbar {
      height: 70px;
      background: #FFFFFF;
      border-bottom: 1px solid var(--admin-border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 32px;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }
    
    .admin-content {
      padding: 32px;
      flex-grow: 1;
      position: relative;
      z-index: 5;
    }

    /* Editorial Action Buttons */
    .admin-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 9px 18px;
      font-size: 0.88rem;
      font-weight: 600;
      border-radius: 8px;
      border: 1px solid transparent;
      cursor: pointer;
      transition: all 0.2s ease;
      text-decoration: none;
    }
    
    .admin-btn:active {
      transform: scale(0.98);
    }
    
    .admin-btn-primary {
      background: #0F172A;
      color: #FFFFFF !important;
      border-color: #0F172A;
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.12);
    }
    
    .admin-btn-primary:hover {
      background: #1E293B;
      border-color: #1E293B;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(15, 23, 42, 0.18);
      color: #FFFFFF !important;
    }
    
    .admin-btn-gold {
      background: #C5A059;
      color: #FFFFFF !important;
      border-color: #C5A059;
      box-shadow: 0 2px 8px rgba(197, 160, 89, 0.2);
    }
    
    .admin-btn-gold:hover {
      background: #A07D38;
      border-color: #A07D38;
      transform: translateY(-1px);
      box-shadow: 0 4px 14px rgba(197, 160, 89, 0.3);
      color: #FFFFFF !important;
    }
    
    .admin-btn-danger {
      background: #FEF2F2;
      border: 1px solid #FECACA;
      color: #DC2626 !important;
    }
    
    .admin-btn-danger:hover {
      background: #DC2626;
      border-color: #DC2626;
      color: #FFFFFF !important;
      transform: translateY(-1px);
    }
    
    .admin-btn-outline {
      background: #FFFFFF;
      border: 1px solid #CBD5E1;
      color: #334155 !important;
    }
    
    .admin-btn-outline:hover {
      background: #F8FAFC;
      border-color: #94A3B8;
      color: #0F172A !important;
      transform: translateY(-1px);
    }
    
    .admin-btn-sm {
      padding: 6px 12px;
      font-size: 0.8rem;
    }

    /* Pure White Admin Cards */
    .admin-card {
      background: #FFFFFF;
      border-radius: 14px;
      border: 1px solid #E2E8F0;
      padding: 24px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
      margin-bottom: 24px;
      position: relative;
      color: #0F172A;
    }

    /* Clean Pure White Data Tables */
    .admin-table-container {
      background: #FFFFFF;
      border-radius: 14px;
      border: 1px solid #E2E8F0;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
      overflow: hidden;
      margin-bottom: 28px;
    }
    
    .admin-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.9rem;
    }
    
    .admin-table th {
      background: #F8FAFC;
      padding: 14px 18px;
      font-size: 0.76rem;
      font-weight: 700;
      color: #475569;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      border-bottom: 1px solid #E2E8F0;
      white-space: nowrap;
      text-align: left;
    }
    
    .admin-table td {
      padding: 16px 18px;
      border-bottom: 1px solid #F1F5F9;
      vertical-align: middle;
      font-size: 0.9rem;
      color: #0F172A;
    }
    
    .admin-table tr:hover td {
      background-color: #F8FAFC;
    }
    
    .admin-table tr:last-child td {
      border-bottom: none;
    }

    /* Status Badges with High-Contrast Typography */
    .badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: capitalize;
      letter-spacing: 0.02em;
    }
    
    .badge-success {
      background: #ECFDF5;
      border: 1px solid #A7F3D0;
      color: #065F46;
    }
    
    .badge-warning {
      background: #FFFBEB;
      border: 1px solid #FDE68A;
      color: #92400E;
    }
    
    .badge-info {
      background: #EFF6FF;
      border: 1px solid #BFDBFE;
      color: #1E40AF;
    }
    
    .badge-danger {
      background: #FEF2F2;
      border: 1px solid #FECACA;
      color: #991B1B;
    }
    
    .badge-neutral {
      background: #F1F5F9;
      border: 1px solid #E2E8F0;
      color: #475569;
    }

    /* Stat Cards */
    .admin-stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 24px;
    }
    
    .admin-stat-card {
      background: #FFFFFF;
      border-radius: 14px;
      border: 1px solid #E2E8F0;
      padding: 20px 24px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .admin-stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    }
    
    .admin-stat-label {
      font-size: 0.78rem;
      font-weight: 700;
      color: var(--admin-muted);
      text-transform: uppercase;
      letter-spacing: 0.06em;
      margin-bottom: 6px;
    }
    
    .admin-stat-value {
      font-size: 1.65rem;
      font-weight: 700;
      color: #0F172A;
      line-height: 1.2;
    }
    
    .admin-stat-sub {
      font-size: 0.78rem;
      color: var(--admin-muted);
      margin-top: 4px;
    }
    
    .admin-stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
      flex-shrink: 0;
      background: #F8FAFC;
      border: 1px solid #E2E8F0;
      color: #0F172A;
    }

    /* Filter & Search Bar */
    .admin-filter-bar {
      background: #FFFFFF;
      border-radius: 14px;
      border: 1px solid #E2E8F0;
      padding: 16px 20px;
      margin-bottom: 24px;
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    
    .admin-filter-form {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
      flex-grow: 1;
    }
    
    .admin-search-box {
      position: relative;
      flex-grow: 1;
      min-width: 240px;
      max-width: 380px;
    }
    
    .admin-search-box input {
      width: 100%;
      padding: 10px 14px 10px 38px;
      border: 1.5px solid #CBD5E1;
      border-radius: 8px;
      font-size: 0.88rem;
      font-family: inherit;
      outline: none;
      background: #FFFFFF;
      color: #0F172A;
      transition: all 0.2s;
    }
    
    .admin-search-box input:focus {
      border-color: #0F172A;
      box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08);
    }
    
    .admin-search-box svg {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #94A3B8;
      pointer-events: none;
    }
    
    .admin-filter-select {
      padding: 10px 14px;
      border: 1.5px solid #CBD5E1;
      border-radius: 8px;
      font-size: 0.88rem;
      font-family: inherit;
      background: #FFFFFF;
      color: #0F172A;
      outline: none;
      cursor: pointer;
      transition: all 0.2s;
    }
    
    .admin-filter-select:focus {
      border-color: #0F172A;
      box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08);
    }

    /* Page Header */
    .admin-page-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 24px;
      flex-wrap: wrap;
      gap: 16px;
    }
    
    .admin-page-title {
      font-size: 1.45rem;
      font-weight: 700;
      color: #0F172A;
      margin-bottom: 4px;
      letter-spacing: -0.01em;
    }
    
    .admin-page-desc {
      font-size: 0.88rem;
      color: #64748B;
      line-height: 1.5;
    }

    /* Code Pills */
    .code-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #F1F5F9;
      border: 1px solid #E2E8F0;
      padding: 3px 8px;
      border-radius: 6px;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
      font-size: 0.8rem;
      color: #334155;
    }
    
    .code-copy-btn {
      background: none;
      border: none;
      cursor: pointer;
      color: #C5A059;
      font-size: 0.85rem;
      line-height: 1;
      padding: 0;
      transition: color 0.15s;
    }
    
    .code-copy-btn:hover {
      color: #A07D38;
    }

    /* Flash Toast Notifications */
    .flash-messages-container {
      margin-bottom: 24px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    
    .flash-toast {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 14px 20px;
      border-radius: 10px;
      background: #FFFFFF;
      box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
      font-size: 0.92rem;
      border: 1px solid #E2E8F0;
      border-left: 5px solid #0F172A;
      color: #0F172A;
      animation: adminSlideDown 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    
    .flash-toast--success { border-left-color: #10B981; }
    .flash-toast--error { border-left-color: #EF4444; }
    .flash-toast--warning { border-left-color: #F59E0B; }
    .flash-toast--info { border-left-color: #3B82F6; }
    
    .flash-toast__icon {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.95rem;
      font-weight: 700;
      flex-shrink: 0;
      background: #F1F5F9;
    }
    
    .flash-toast__body { flex-grow: 1; font-weight: 500; }
    .flash-toast__close {
      background: none;
      border: none;
      font-size: 1.4rem;
      cursor: pointer;
      color: #94A3B8;
      padding: 0 6px;
      line-height: 1;
      transition: color 0.15s;
    }
    .flash-toast__close:hover { color: #0F172A; }
    
    @keyframes adminSlideDown {
      from { opacity: 0; transform: translateY(-8px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Universal Form Controls on Pure White */
    .admin-form-group {
      margin-bottom: 20px;
    }
    
    .admin-form-group label,
    .admin-card label {
      display: block;
      font-size: 0.86rem;
      font-weight: 600;
      color: #334155 !important;
      margin-bottom: 8px;
    }
    
    .admin-card input[type="text"],
    .admin-card input[type="email"],
    .admin-card input[type="password"],
    .admin-card input[type="number"],
    .admin-card input[type="tel"],
    .admin-card input[type="date"],
    .admin-card input[type="file"],
    .admin-card select,
    .admin-card textarea,
    .form-control {
      width: 100%;
      background: #FFFFFF !important;
      border: 1.5px solid #CBD5E1 !important;
      border-radius: 8px !important;
      padding: 10px 14px !important;
      color: #0F172A !important;
      font-size: 0.9rem !important;
      font-family: inherit !important;
      outline: none !important;
      transition: all 0.2s ease !important;
    }
    
    .admin-card input:focus,
    .admin-card select:focus,
    .admin-card textarea:focus,
    .form-control:focus {
      background: #FFFFFF !important;
      border-color: #0F172A !important;
      box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08) !important;
    }

    .admin-card [style*="background:#FFF"],
    .admin-card [style*="background: #FFF"],
    .admin-card [style*="background:#fff"],
    .admin-card [style*="background: #fff"],
    .admin-card [style*="background: #ffffff"],
    .admin-card [style*="background:#ffffff"] {
      background: #FFFFFF !important;
      border-color: #E2E8F0 !important;
      color: #0F172A !important;
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="admin-brand">
      <a href="<?= ADMIN_URL ?>/dashboard.php" style="display:flex;align-items:center;text-decoration:none;">
        <img src="<?= ASSETS_URL ?>/images/logo/midhila-voyages-logo.png" 
             srcset="<?= ASSETS_URL ?>/images/logo/midhila-voyages-logo@2x.png 2x" 
             alt="Midhila Voyages Admin" 
             style="max-width:180px;height:42px;width:auto;object-fit:contain;">
      </a>
    </div>

    <nav class="admin-nav">
      <a href="<?= ADMIN_URL ?>/dashboard.php" class="admin-nav-item <?= ($adminScript === 'dashboard.php') ? 'is-active' : '' ?>">
        <span>📊</span> Dashboard
      </a>

      <div class="admin-nav-section-title">Customer Journeys</div>
      <a href="<?= ADMIN_URL ?>/enquiries.php" class="admin-nav-item <?= in_array($adminScript, ['enquiries.php', 'enquiry-view.php']) ? 'is-active' : '' ?>">
        <span>📩</span> Enquiries
      </a>
      <a href="<?= ADMIN_URL ?>/bookings.php" class="admin-nav-item <?= ($adminScript === 'bookings.php') ? 'is-active' : '' ?>">
        <span>🎫</span> Bookings
      </a>
      <a href="<?= ADMIN_URL ?>/payments.php" class="admin-nav-item <?= ($adminScript === 'payments.php') ? 'is-active' : '' ?>">
        <span>💳</span> Payments Audit
      </a>

      <div class="admin-nav-section-title">Travel Catalog</div>
      <a href="<?= ADMIN_URL ?>/destinations.php" class="admin-nav-item <?= in_array($adminScript, ['destinations.php', 'destination-edit.php']) ? 'is-active' : '' ?>">
        <span>📍</span> Destinations
      </a>
      <a href="<?= ADMIN_URL ?>/packages.php" class="admin-nav-item <?= in_array($adminScript, ['packages.php', 'package-edit.php']) ? 'is-active' : '' ?>">
        <span>🧭</span> Tour Packages
      </a>
      <a href="<?= ADMIN_URL ?>/categories.php" class="admin-nav-item <?= ($adminScript === 'categories.php') ? 'is-active' : '' ?>">
        <span>🏷️</span> Categories
      </a>

      <div class="admin-nav-section-title">Media & Content</div>
      <a href="<?= ADMIN_URL ?>/gallery.php" class="admin-nav-item <?= ($adminScript === 'gallery.php') ? 'is-active' : '' ?>">
        <span>🖼️</span> Photo Gallery
      </a>
      <a href="<?= ADMIN_URL ?>/testimonials.php" class="admin-nav-item <?= ($adminScript === 'testimonials.php') ? 'is-active' : '' ?>">
        <span>⭐</span> Testimonials
      </a>
      <a href="<?= ADMIN_URL ?>/faqs.php" class="admin-nav-item <?= ($adminScript === 'faqs.php') ? 'is-active' : '' ?>">
        <span>❓</span> FAQs
      </a>

      <div class="admin-nav-section-title">Administration</div>
      <a href="<?= ADMIN_URL ?>/settings.php" class="admin-nav-item <?= ($adminScript === 'settings.php') ? 'is-active' : '' ?>">
        <span>⚙️</span> Site Settings
      </a>
      <a href="<?= ADMIN_URL ?>/users.php" class="admin-nav-item <?= ($adminScript === 'users.php') ? 'is-active' : '' ?>">
        <span>👥</span> Staff Accounts
      </a>
    </nav>

    <div style="padding:16px 20px;border-top:1px solid #E2E8F0;display:flex;align-items:center;justify-content:space-between;">
      <a href="<?= BASE_URL ?>/index.php" target="_blank" style="font-size:0.82rem;color:#64748B;display:flex;align-items:center;gap:6px;font-weight:500;">
        <span>Visit Site</span> &nearr;
      </a>
      <a href="<?= ADMIN_URL ?>/logout.php" style="font-size:0.82rem;color:#DC2626;font-weight:600;">Logout</a>
    </div>
  </aside>

  <!-- Main Container -->
  <div class="admin-main">
    
    <!-- Topbar -->
    <header class="admin-topbar">
      <div>
        <h2 style="font-size:1.25rem;color:#0F172A;font-weight:700;letter-spacing:-0.01em;"><?= e($adminHeading ?? 'Dashboard') ?></h2>
      </div>

      <div style="display:flex;align-items:center;gap:18px;">
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:38px;height:38px;border-radius:50%;background:#0F172A;color:#FFFFFF;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.9rem;border:1.5px solid #C5A059;box-shadow:0 2px 6px rgba(15,23,42,0.15);">
            <?= strtoupper(substr($currentAdmin['full_name'] ?? 'A', 0, 1)) ?>
          </div>
          <div>
            <div style="font-size:0.88rem;font-weight:600;color:#0F172A;"><?= e($currentAdmin['full_name']) ?></div>
            <div style="font-size:0.75rem;color:#64748B;font-weight:500;"><?= strtoupper(str_replace('_', ' ', $currentAdmin['role'])) ?></div>
          </div>
        </div>
      </div>
    </header>

    <!-- Content Area -->
    <main class="admin-content">
      <?= render_flash_messages() ?>