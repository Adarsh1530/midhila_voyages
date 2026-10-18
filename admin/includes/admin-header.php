<?php
/**
 * MIDHILA VOYAGES — Admin Header & Navigation Layout
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
  <title><?= e($pageTitle) ?></title>
  
  <link rel="icon" type="image/svg+xml" href="<?= ASSETS_URL ?>/images/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  
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
      --admin-navy: #0A192F;
      --admin-sidebar: #06101E;
      --admin-bg: #F8FAFC;
      --admin-card: #FFFFFF;
      --admin-gold: #C5A059;
      --admin-teal: #00A896;
      --admin-border: #E2E8F0;
      --admin-text: #1E293B;
      --admin-muted: #64748B;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
      background-color: var(--admin-bg);
      color: var(--admin-text);
      display: flex;
      min-height: 100vh;
    }
    a { color: inherit; text-decoration: none; }
    
    /* Sidebar */
    .admin-sidebar {
      width: 260px;
      background-color: var(--admin-sidebar);
      color: #94A3B8;
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
      border-right: 1px solid rgba(197, 160, 89, 0.2);
    }
    .admin-brand {
      padding: 24px 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .admin-brand__title {
      font-size: 1.15rem;
      font-weight: 700;
      color: #FFF;
      letter-spacing: 0.08em;
    }
    .admin-nav {
      padding: 20px 12px;
      display: flex;
      flex-direction: column;
      gap: 4px;
      flex-grow: 1;
      overflow-y: auto;
    }
    .admin-nav-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 14px;
      border-radius: 8px;
      font-size: 0.9rem;
      font-weight: 500;
      transition: all 0.15s ease;
      color: #CBD5E1;
    }
    .admin-nav-item:hover {
      background: rgba(255, 255, 255, 0.08);
      color: #FFF;
    }
    .admin-nav-item.is-active {
      background: var(--admin-gold);
      color: #06101E;
      font-weight: 600;
    }
    .admin-nav-section-title {
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.12em;
      color: #64748B;
      padding: 12px 14px 6px;
      font-weight: 700;
    }

    /* Main Area */
    .admin-main {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      min-width: 0;
      overflow-y: auto;
    }
    .admin-topbar {
      height: 70px;
      background: #FFF;
      border-bottom: 1px solid var(--admin-border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 32px;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .admin-content {
      padding: 32px;
      flex-grow: 1;
    }

    /* UI Components */
    .admin-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 18px;
      font-size: 0.88rem;
      font-weight: 600;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      transition: all 0.2s;
    }
    .admin-btn-primary { background: var(--admin-navy); color: #FFF; }
    .admin-btn-primary:hover { background: #112240; }
    .admin-btn-gold { background: var(--admin-gold); color: #06101E; }
    .admin-btn-gold:hover { background: #DFBA73; }
    .admin-btn-danger { background: #EF4444; color: #FFF; }
    .admin-btn-outline { background: #FFF; border: 1px solid var(--admin-border); color: var(--admin-text); }
    .admin-btn-sm { padding: 5px 12px; font-size: 0.8rem; }

    .admin-card {
      background: #FFF;
      border-radius: 12px;
      border: 1px solid var(--admin-border);
      padding: 24px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      margin-bottom: 24px;
    }
    .admin-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.9rem;
    }
    .admin-table th {
      background: #F8FAFC;
      padding: 12px 16px;
      text-align: left;
      font-weight: 600;
      color: var(--admin-muted);
      border-bottom: 1px solid var(--admin-border);
    }
    .admin-table td {
      padding: 14px 16px;
      border-bottom: 1px solid var(--admin-border);
      vertical-align: middle;
    }
    .admin-table tr:hover td {
      background-color: #F8FAFC;
    }
    .badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: capitalize;
    }
    .badge-success { background: #DCFCE7; color: #166534; }
    .badge-warning { background: #FEF3C7; color: #92400E; }
    .badge-info { background: #E0F2FE; color: #075985; }
    .badge-danger { background: #FEE2E2; color: #991B1B; }
    .badge-neutral { background: #F1F5F9; color: #475569; }

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
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
      font-size: 0.92rem;
      border: 1px solid var(--admin-border);
      border-left: 5px solid var(--admin-gold);
      animation: adminSlideDown 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .flash-toast--success {
      border-left-color: #10B981;
      background: #F0FDF4;
      color: #065F46;
    }
    .flash-toast--error {
      border-left-color: #EF4444;
      background: #FEF2F2;
      color: #991B1B;
    }
    .flash-toast--warning {
      border-left-color: #F59E0B;
      background: #FFFBEB;
      color: #92400E;
    }
    .flash-toast--info {
      border-left-color: #3B82F6;
      background: #EFF6FF;
      color: #1E40AF;
    }
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
    }
    .flash-toast--success .flash-toast__icon { background: #DCFCE7; color: #166534; }
    .flash-toast--error .flash-toast__icon { background: #FEE2E2; color: #991B1B; }
    .flash-toast__body {
      flex-grow: 1;
      font-weight: 500;
    }
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
    .flash-toast__close:hover {
      color: #1E293B;
    }
    @keyframes adminSlideDown {
      from { opacity: 0; transform: translateY(-8px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* KPI Stats Grid */
    .admin-stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 24px;
    }
    .admin-stat-card {
      background: #FFF;
      border-radius: 12px;
      border: 1px solid var(--admin-border);
      padding: 20px 24px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.04);
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .admin-stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0,0,0,0.06);
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
      color: var(--admin-navy);
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
      font-size: 1.35rem;
      flex-shrink: 0;
    }

    /* Modern Filter & Search Bar */
    .admin-filter-bar {
      background: #FFF;
      border-radius: 12px;
      border: 1px solid var(--admin-border);
      padding: 16px 20px;
      margin-bottom: 24px;
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 1px 3px rgba(0,0,0,0.04);
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
      padding: 9px 14px 9px 38px;
      border: 1px solid var(--admin-border);
      border-radius: 8px;
      font-size: 0.88rem;
      font-family: inherit;
      outline: none;
      background: #F8FAFC;
      transition: all 0.2s;
    }
    .admin-search-box input:focus {
      background: #FFF;
      border-color: var(--admin-gold);
      box-shadow: 0 0 0 3px rgba(197, 160, 89, 0.15);
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
      padding: 9px 14px;
      border: 1px solid var(--admin-border);
      border-radius: 8px;
      font-size: 0.88rem;
      font-family: inherit;
      background: #F8FAFC;
      color: var(--admin-text);
      outline: none;
      cursor: pointer;
      transition: all 0.2s;
    }
    .admin-filter-select:focus {
      background: #FFF;
      border-color: var(--admin-gold);
    }

    /* Page Header Section */
    .admin-page-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 24px;
      flex-wrap: wrap;
      gap: 16px;
    }
    .admin-page-title {
      font-size: 1.35rem;
      font-weight: 700;
      color: var(--admin-navy);
      margin-bottom: 4px;
      letter-spacing: -0.01em;
    }
    .admin-page-desc {
      font-size: 0.88rem;
      color: var(--admin-muted);
      line-height: 1.5;
    }

    /* Enhanced Table Alignment & Container */
    .admin-table-container {
      background: #FFF;
      border-radius: 12px;
      border: 1px solid var(--admin-border);
      box-shadow: 0 1px 4px rgba(0,0,0,0.05);
      overflow: hidden;
      margin-bottom: 28px;
    }
    .admin-table th {
      background: #F8FAFC;
      padding: 14px 18px;
      font-size: 0.76rem;
      font-weight: 700;
      color: #475569;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      border-bottom: 2px solid var(--admin-border);
      white-space: nowrap;
    }
    .admin-table td {
      padding: 16px 18px;
      border-bottom: 1px solid var(--admin-border);
      vertical-align: middle;
      font-size: 0.9rem;
    }
    .admin-table tr:hover td {
      background-color: #F8FAFC;
    }
    .admin-table tr:last-child td {
      border-bottom: none;
    }

    /* Code Pills & Copy Buttons */
    .code-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #F8FAFC;
      border: 1px solid #E2E8F0;
      padding: 3px 8px;
      border-radius: 6px;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
      font-size: 0.8rem;
      color: #0F172A;
    }
    .code-copy-btn {
      background: none;
      border: none;
      cursor: pointer;
      color: #64748B;
      font-size: 0.85rem;
      line-height: 1;
      padding: 0;
      transition: color 0.15s;
    }
    .code-copy-btn:hover {
      color: var(--admin-navy);
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="admin-brand">
      <img src="<?= ASSETS_URL ?>/images/logo/symbol.svg" alt="Emblem" width="34" height="34">
      <div>
        <div class="admin-brand__title">MIDHILA</div>
        <div style="font-size:0.65rem;letter-spacing:0.2em;color:var(--admin-gold);">AGENCY PORTAL</div>
      </div>
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

    <div style="padding:16px 20px;border-top:1px solid rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:space-between;">
      <a href="<?= BASE_URL ?>/index.php" target="_blank" style="font-size:0.8rem;color:var(--admin-gold);display:flex;align-items:center;gap:6px;">
        <span>Visit Site</span> &nearr;
      </a>
      <a href="<?= ADMIN_URL ?>/logout.php" style="font-size:0.8rem;color:#EF4444;">Logout</a>
    </div>
  </aside>

  <!-- Main Container -->
  <div class="admin-main">
    
    <!-- Topbar -->
    <header class="admin-topbar">
      <div>
        <h2 style="font-size:1.25rem;color:var(--admin-navy);"><?= e($adminHeading ?? 'Dashboard') ?></h2>
      </div>

      <div style="display:flex;align-items:center;gap:18px;">
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:36px;height:36px;border-radius:50%;background:var(--admin-navy);color:#FFF;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.9rem;">
            <?= strtoupper(substr($currentAdmin['full_name'] ?? 'A', 0, 1)) ?>
          </div>
          <div>
            <div style="font-size:0.88rem;font-weight:600;color:var(--admin-navy);"><?= e($currentAdmin['full_name']) ?></div>
            <div style="font-size:0.75rem;color:var(--admin-muted);"><?= strtoupper(str_replace('_', ' ', $currentAdmin['role'])) ?></div>
          </div>
        </div>
      </div>
    </header>

    <!-- Content Area -->
    <main class="admin-content">
      <?= render_flash_messages() ?>
