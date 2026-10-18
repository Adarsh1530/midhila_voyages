<?php
/**
 * MIDHILA VOYAGES — Admin Header & Navigation Layout
 * Futuristic Glassmorphism Design with Lavender/Periwinkle Palette & Ambient Motion
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
      --admin-navy: #F8FAFC;
      --admin-navy-deep: #060B18;
      --admin-sidebar: rgba(10, 17, 40, 0.76);
      --admin-bg: #070D1E;
      --admin-card: rgba(14, 23, 46, 0.65);
      --admin-card-border: rgba(196, 181, 253, 0.2);
      --admin-gold: #C5A059;
      --admin-lavender: #C4B5FD;
      --admin-periwinkle: #8DA4EF;
      --admin-violet: #8B5CF6;
      --admin-icy: #BAE6FD;
      --admin-border: rgba(196, 181, 253, 0.16);
      --admin-text: #F8FAFC;
      --admin-muted: #94A3B8;
    }

    /* Universal Glass Form Controls & Containers */
    .admin-card input[type="text"],
    .admin-card input[type="email"],
    .admin-card input[type="password"],
    .admin-card input[type="number"],
    .admin-card input[type="date"],
    .admin-card input[type="file"],
    .admin-card select,
    .admin-card textarea,
    .form-control {
      background: rgba(10, 17, 40, 0.75) !important;
      color: #F8FAFC !important;
      border: 1.5px solid rgba(196, 181, 253, 0.25) !important;
      border-radius: 10px !important;
      font-family: inherit !important;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }

    .admin-card input:focus,
    .admin-card select:focus,
    .admin-card textarea:focus,
    .form-control:focus {
      background: rgba(10, 17, 40, 0.95) !important;
      border-color: var(--admin-periwinkle) !important;
      box-shadow: 0 0 0 3px rgba(141, 164, 239, 0.25), 0 0 16px rgba(196, 181, 253, 0.2) !important;
      outline: none !important;
    }

    .admin-card label {
      color: #E2E8F0 !important;
    }

    .admin-card [style*="background:#FFF"],
    .admin-card [style*="background: #FFF"],
    .admin-card [style*="background:#fff"],
    .admin-card [style*="background: #fff"],
    .admin-card [style*="background: #ffffff"],
    .admin-card [style*="background:#ffffff"] {
      background: rgba(10, 17, 40, 0.6) !important;
      border-color: rgba(196, 181, 253, 0.2) !important;
      color: #F8FAFC !important;
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
    
    /* Frosted Glass Sidebar */
    .admin-sidebar {
      width: 260px;
      background: var(--admin-sidebar);
      backdrop-filter: blur(28px) saturate(180%);
      -webkit-backdrop-filter: blur(28px) saturate(180%);
      color: #94A3B8;
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
      border-right: 1px solid var(--admin-border);
      position: relative;
      z-index: 10;
      box-shadow: 4px 0 24px rgba(0, 0, 0, 0.35);
    }
    
    .admin-brand {
      padding: 24px 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      border-bottom: 1px solid rgba(196, 181, 253, 0.12);
    }
    
    .admin-brand img {
      filter: drop-shadow(0 0 8px rgba(141, 164, 239, 0.5));
    }
    
    .admin-brand__title {
      font-size: 1.15rem;
      font-weight: 700;
      color: #FFF;
      letter-spacing: 0.08em;
      text-shadow: 0 0 12px rgba(196, 181, 253, 0.3);
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
      border-radius: 10px;
      font-size: 0.9rem;
      font-weight: 500;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      color: #CBD5E1;
      border: 1px solid transparent;
    }
    
    .admin-nav-item:hover {
      background: rgba(196, 181, 253, 0.1);
      border-color: rgba(196, 181, 253, 0.2);
      color: #FFF;
      transform: translateX(3px);
    }
    
    .admin-nav-item.is-active {
      background: linear-gradient(135deg, rgba(139, 92, 246, 0.35) 0%, rgba(99, 102, 241, 0.45) 100%);
      border: 1px solid rgba(196, 181, 253, 0.45);
      color: #FFF;
      font-weight: 600;
      box-shadow: 0 4px 16px rgba(139, 92, 246, 0.25), inset 0 1px 1px rgba(255, 255, 255, 0.4);
    }
    
    .admin-nav-section-title {
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.12em;
      color: var(--admin-lavender);
      padding: 14px 14px 6px;
      font-weight: 700;
      opacity: 0.85;
    }

    /* Main Area */
    .admin-main {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      min-width: 0;
      overflow-y: auto;
      position: relative;
      z-index: 5;
    }
    
    .admin-topbar {
      height: 70px;
      background: rgba(10, 17, 40, 0.65);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border-bottom: 1px solid var(--admin-border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 32px;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }
    
    .admin-content {
      padding: 32px;
      flex-grow: 1;
      position: relative;
      z-index: 5;
    }

    /* Glass UI Buttons */
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
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      text-decoration: none;
    }
    
    .admin-btn:active {
      transform: scale(0.97);
    }
    
    .admin-btn-primary {
      background: linear-gradient(135deg, #7C3AED 0%, #4F46E5 100%);
      color: #FFF;
      border-color: rgba(255, 255, 255, 0.3);
      box-shadow: 0 4px 16px rgba(124, 58, 237, 0.35), inset 0 1px 1px rgba(255, 255, 255, 0.4);
    }
    
    .admin-btn-primary:hover {
      background: linear-gradient(135deg, #8B5CF6 0%, #6366F1 100%);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(139, 92, 246, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.6);
      color: #FFF;
    }
    
    .admin-btn-gold {
      background: linear-gradient(135deg, #C5A059 0%, #DFBA73 100%);
      color: #06101E;
      box-shadow: 0 4px 14px rgba(197, 160, 89, 0.35);
    }
    
    .admin-btn-gold:hover {
      background: linear-gradient(135deg, #DFBA73 0%, #EBD49B 100%);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(197, 160, 89, 0.5);
    }
    
    .admin-btn-danger {
      background: rgba(239, 68, 68, 0.2);
      border: 1px solid rgba(239, 68, 68, 0.4);
      color: #FCA5A5;
      backdrop-filter: blur(8px);
    }
    
    .admin-btn-danger:hover {
      background: #EF4444;
      color: #FFF;
      transform: translateY(-2px);
      box-shadow: 0 4px 16px rgba(239, 68, 68, 0.4);
    }
    
    .admin-btn-outline {
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(196, 181, 253, 0.25);
      color: #E2E8F0;
      backdrop-filter: blur(8px);
    }
    
    .admin-btn-outline:hover {
      background: rgba(255, 255, 255, 0.16);
      border-color: rgba(196, 181, 253, 0.5);
      color: #FFF;
      transform: translateY(-2px);
    }
    
    .admin-btn-sm {
      padding: 5px 12px;
      font-size: 0.8rem;
    }

    /* Frosted Glass Cards */
    .admin-card {
      background: var(--admin-card);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border-radius: 16px;
      border: 1px solid var(--admin-card-border);
      padding: 24px;
      box-shadow: inset 0 1px 1px 0 rgba(255, 255, 255, 0.12), 0 20px 48px -10px rgba(0, 0, 0, 0.4);
      margin-bottom: 24px;
      position: relative;
    }

    /* Frosted Glass Data Tables */
    .admin-table-container {
      background: var(--admin-card);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border-radius: 16px;
      border: 1px solid var(--admin-card-border);
      box-shadow: inset 0 1px 1px 0 rgba(255, 255, 255, 0.12), 0 20px 48px -10px rgba(0, 0, 0, 0.4);
      overflow: hidden;
      margin-bottom: 28px;
    }
    
    .admin-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.9rem;
    }
    
    .admin-table th {
      background: rgba(20, 32, 60, 0.5);
      padding: 14px 18px;
      font-size: 0.76rem;
      font-weight: 700;
      color: var(--admin-icy);
      text-transform: uppercase;
      letter-spacing: 0.08em;
      border-bottom: 2px solid rgba(196, 181, 253, 0.2);
      white-space: nowrap;
      text-align: left;
    }
    
    .admin-table td {
      padding: 16px 18px;
      border-bottom: 1px solid rgba(196, 181, 253, 0.1);
      vertical-align: middle;
      font-size: 0.9rem;
      color: #E2E8F0;
    }
    
    .admin-table tr:hover td {
      background-color: rgba(139, 92, 246, 0.08);
    }
    
    .admin-table tr:last-child td {
      border-bottom: none;
    }

    /* Softly Illuminated Status Badges */
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
      background: rgba(34, 197, 94, 0.18);
      border: 1px solid rgba(34, 197, 94, 0.45);
      color: #86EFAC;
      box-shadow: 0 0 12px rgba(34, 197, 94, 0.25);
    }
    
    .badge-warning {
      background: rgba(245, 158, 11, 0.18);
      border: 1px solid rgba(245, 158, 11, 0.45);
      color: #FDE68A;
      box-shadow: 0 0 12px rgba(245, 158, 11, 0.25);
    }
    
    .badge-info {
      background: rgba(59, 130, 246, 0.18);
      border: 1px solid rgba(59, 130, 246, 0.45);
      color: #BAE6FD;
      box-shadow: 0 0 12px rgba(59, 130, 246, 0.25);
    }
    
    .badge-danger {
      background: rgba(239, 68, 68, 0.18);
      border: 1px solid rgba(239, 68, 68, 0.45);
      color: #FCA5A5;
      box-shadow: 0 0 12px rgba(239, 68, 68, 0.25);
    }
    
    .badge-neutral {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: #CBD5E1;
    }

    /* KPI Stats Grid */
    .admin-stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 24px;
    }
    
    .admin-stat-card {
      background: var(--admin-card);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border-radius: 16px;
      border: 1px solid var(--admin-card-border);
      padding: 20px 24px;
      box-shadow: inset 0 1px 1px 0 rgba(255, 255, 255, 0.12), 0 16px 36px -8px rgba(0, 0, 0, 0.35);
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s;
    }
    
    .admin-stat-card:hover {
      transform: translateY(-4px);
      border-color: rgba(141, 164, 239, 0.5);
      box-shadow: inset 0 1px 1px 0 rgba(255, 255, 255, 0.2), 0 20px 42px -6px rgba(139, 92, 246, 0.2);
    }
    
    .admin-stat-label {
      font-size: 0.78rem;
      font-weight: 700;
      color: var(--admin-muted);
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-bottom: 6px;
    }
    
    .admin-stat-value {
      font-size: 1.65rem;
      font-weight: 700;
      color: #FFF;
      line-height: 1.2;
      text-shadow: 0 0 14px rgba(196, 181, 253, 0.3);
    }
    
    .admin-stat-sub {
      font-size: 0.78rem;
      color: var(--admin-lavender);
      margin-top: 4px;
      opacity: 0.85;
    }
    
    .admin-stat-icon {
      width: 52px;
      height: 52px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      flex-shrink: 0;
      background: rgba(139, 92, 246, 0.15);
      border: 1px solid rgba(196, 181, 253, 0.3);
      box-shadow: 0 0 16px rgba(139, 92, 246, 0.2);
    }

    /* Filter & Search Bar */
    .admin-filter-bar {
      background: var(--admin-card);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border-radius: 16px;
      border: 1px solid var(--admin-card-border);
      padding: 16px 20px;
      margin-bottom: 24px;
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      align-items: center;
      justify-content: space-between;
      box-shadow: inset 0 1px 1px 0 rgba(255, 255, 255, 0.1), 0 12px 30px rgba(0, 0, 0, 0.3);
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
      border: 1.5px solid rgba(196, 181, 253, 0.3);
      border-radius: 10px;
      font-size: 0.88rem;
      font-family: inherit;
      outline: none;
      background: rgba(10, 17, 40, 0.7);
      color: #FFF;
      transition: all 0.2s;
    }
    
    .admin-search-box input:focus {
      background: rgba(10, 17, 40, 0.9);
      border-color: var(--admin-periwinkle);
      box-shadow: 0 0 0 3px rgba(141, 164, 239, 0.25);
    }
    
    .admin-search-box svg {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--admin-muted);
      pointer-events: none;
    }
    
    .admin-filter-select {
      padding: 10px 14px;
      border: 1.5px solid rgba(196, 181, 253, 0.3);
      border-radius: 10px;
      font-size: 0.88rem;
      font-family: inherit;
      background: rgba(10, 17, 40, 0.7);
      color: #FFF;
      outline: none;
      cursor: pointer;
      transition: all 0.2s;
    }
    
    .admin-filter-select:focus {
      background: rgba(10, 17, 40, 0.9);
      border-color: var(--admin-periwinkle);
      box-shadow: 0 0 0 3px rgba(141, 164, 239, 0.25);
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
      color: #FFF;
      margin-bottom: 4px;
      letter-spacing: -0.01em;
      text-shadow: 0 0 14px rgba(196, 181, 253, 0.2);
    }
    
    .admin-page-desc {
      font-size: 0.88rem;
      color: var(--admin-muted);
      line-height: 1.5;
    }

    /* Code Pills */
    .code-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(10, 17, 40, 0.65);
      border: 1px solid rgba(196, 181, 253, 0.25);
      padding: 3px 8px;
      border-radius: 6px;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
      font-size: 0.8rem;
      color: var(--admin-icy);
    }
    
    .code-copy-btn {
      background: none;
      border: none;
      cursor: pointer;
      color: var(--admin-lavender);
      font-size: 0.85rem;
      line-height: 1;
      padding: 0;
      transition: color 0.15s;
    }
    
    .code-copy-btn:hover {
      color: #FFF;
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
      border-radius: 12px;
      background: rgba(14, 23, 46, 0.88);
      backdrop-filter: blur(16px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
      font-size: 0.92rem;
      border: 1px solid rgba(196, 181, 253, 0.2);
      border-left: 5px solid var(--admin-violet);
      color: #FFF;
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
      background: rgba(255, 255, 255, 0.12);
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
    .flash-toast__close:hover { color: #FFF; }
    
    @keyframes adminSlideDown {
      from { opacity: 0; transform: translateY(-8px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Admin Form Controls (Inputs, Selects, Labels) */
    .admin-form-group {
      margin-bottom: 20px;
    }
    
    .admin-form-group label {
      display: block;
      font-size: 0.86rem;
      font-weight: 600;
      color: #E2E8F0;
      margin-bottom: 8px;
    }
    
    .admin-card input[type="text"],
    .admin-card input[type="email"],
    .admin-card input[type="password"],
    .admin-card input[type="number"],
    .admin-card input[type="tel"],
    .admin-card input[type="date"],
    .admin-card select,
    .admin-card textarea {
      width: 100%;
      background: rgba(10, 17, 40, 0.75);
      backdrop-filter: blur(12px);
      border: 1.5px solid rgba(196, 181, 253, 0.3);
      border-radius: 10px;
      padding: 11px 16px;
      color: #FFF;
      font-size: 0.9rem;
      font-family: inherit;
      outline: none;
      transition: all 0.2s;
    }
    
    .admin-card input:focus,
    .admin-card select:focus,
    .admin-card textarea:focus {
      background: rgba(10, 17, 40, 0.95);
      border-color: var(--admin-periwinkle);
      box-shadow: 0 0 0 3px rgba(141, 164, 239, 0.25);
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

  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="admin-brand">
      <img src="<?= ASSETS_URL ?>/images/logo/symbol.svg" alt="Emblem" width="34" height="34">
      <div>
        <div class="admin-brand__title">MIDHILA</div>
        <div style="font-size:0.65rem;letter-spacing:0.2em;color:var(--admin-lavender);">AGENCY PORTAL</div>
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

    <div style="padding:16px 20px;border-top:1px solid rgba(196,181,253,0.12);display:flex;align-items:center;justify-content:space-between;">
      <a href="<?= BASE_URL ?>/index.php" target="_blank" style="font-size:0.8rem;color:var(--admin-lavender);display:flex;align-items:center;gap:6px;">
        <span>Visit Site</span> &nearr;
      </a>
      <a href="<?= ADMIN_URL ?>/logout.php" style="font-size:0.8rem;color:#FCA5A5;">Logout</a>
    </div>
  </aside>

  <!-- Main Container -->
  <div class="admin-main">
    
    <!-- Topbar -->
    <header class="admin-topbar">
      <div>
        <h2 style="font-size:1.25rem;color:#FFF;font-weight:700;letter-spacing:-0.01em;"><?= e($adminHeading ?? 'Dashboard') ?></h2>
      </div>

      <div style="display:flex;align-items:center;gap:18px;">
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg, #7C3AED, #4F46E5);color:#FFF;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.9rem;border:1px solid rgba(255,255,255,0.4);box-shadow:0 0 12px rgba(139,92,246,0.4);">
            <?= strtoupper(substr($currentAdmin['full_name'] ?? 'A', 0, 1)) ?>
          </div>
          <div>
            <div style="font-size:0.88rem;font-weight:600;color:#FFF;"><?= e($currentAdmin['full_name']) ?></div>
            <div style="font-size:0.75rem;color:var(--admin-lavender);"><?= strtoupper(str_replace('_', ' ', $currentAdmin['role'])) ?></div>
          </div>
        </div>
      </div>
    </header>

    <!-- Content Area -->
    <main class="admin-content">
      <?= render_flash_messages() ?>
