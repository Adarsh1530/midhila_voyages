<?php
/**
 * MIDHILA VOYAGES — Global Header Component
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? get_setting('site_name', 'MIDHILA VOYAGES') . ' | Curated Journeys & Sacred Trails';
$pageDescription = $pageDescription ?? get_setting('site_description', 'Bespoke domestic, international, and sacred pilgrimage journeys crafted with care, integrity, and personalised luxury.');
$currentScript = basename($_SERVER['PHP_SELF']);

$agencyPhone = get_setting('primary_phone', '+91 12345 67890');
$agencyWhatsApp = get_setting('whatsapp_number', '+911234567890');
$defaultWaMsg = "Hello MIDHILA VOYAGES, I would like to explore tour packages and plan my journey.";
$globalWaUrl = build_whatsapp_url($agencyWhatsApp, $defaultWaMsg);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($pageDescription) ?>">
  <link rel="canonical" href="<?= e(BASE_URL . '/' . $currentScript) ?>">
  
  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="<?= ASSETS_URL ?>/images/favicon.svg">
  
  <!-- Google Fonts: Cinzel & Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700;800&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
  
  <!-- Stylesheets -->
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/animations.css">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/responsive.css">

  <!-- Open Graph Metadata -->
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($pageDescription) ?>">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= e(BASE_URL . '/' . $currentScript) ?>">
  <!-- Client Environment Config -->
  <script>
    window.MIDHILA_ASSETS_URL = '<?= ASSETS_URL ?>';
    window.MIDHILA_BASE_URL = '<?= BASE_URL ?>';
  </script>
</head>
<body>

  <!-- Ambient Floating Mesh Orbs -->
  <div class="ambient-mesh" aria-hidden="true">
    <div class="ambient-orb ambient-orb--1"></div>
    <div class="ambient-orb ambient-orb--2"></div>
    <div class="ambient-orb ambient-orb--3"></div>
    <div class="ambient-orb ambient-orb--4"></div>
  </div>

  <!-- Site Navigation Header -->
  <header class="site-header" id="siteHeader">
    <div class="container header-container">
      
      <!-- Brand Logo -->
      <a href="<?= BASE_URL ?>/index.php" class="brand-logo" aria-label="Midhila Voyages Home">
        <img src="<?= ASSETS_URL ?>/images/logo/symbol.svg" alt="Midhila Voyages Emblem" class="brand-logo__symbol" width="44" height="44">
        <div class="brand-logo__text">
          <span class="brand-logo__name">MIDHILA</span>
          <span class="brand-logo__subtitle">VOYAGES</span>
        </div>
      </a>

      <!-- Desktop Navigation Menu -->
      <nav class="nav-menu" aria-label="Main Navigation">
        <a href="<?= BASE_URL ?>/index.php" class="nav-link <?= ($currentScript === 'index.php') ? 'is-active' : '' ?>">Home</a>
        <a href="<?= BASE_URL ?>/about.php" class="nav-link <?= ($currentScript === 'about.php') ? 'is-active' : '' ?>">About Us</a>
        <a href="<?= BASE_URL ?>/destinations.php" class="nav-link <?= in_array($currentScript, ['destinations.php', 'destination.php']) ? 'is-active' : '' ?>">Destinations</a>
        <a href="<?= BASE_URL ?>/packages.php" class="nav-link <?= in_array($currentScript, ['packages.php', 'package.php']) ? 'is-active' : '' ?>">Tour Packages</a>
        <a href="<?= BASE_URL ?>/pilgrimage.php" class="nav-link <?= ($currentScript === 'pilgrimage.php') ? 'is-active' : '' ?>">Pilgrimage Tours</a>
        <a href="<?= BASE_URL ?>/services.php" class="nav-link <?= ($currentScript === 'services.php') ? 'is-active' : '' ?>">Services</a>
        <a href="<?= BASE_URL ?>/gallery.php" class="nav-link <?= ($currentScript === 'gallery.php') ? 'is-active' : '' ?>">Gallery</a>
        <a href="<?= BASE_URL ?>/contact.php" class="nav-link <?= ($currentScript === 'contact.php') ? 'is-active' : '' ?>">Contact Us</a>
      </nav>

      <!-- Header Action Buttons -->
      <div class="header-actions">
        <!-- WhatsApp Direct Chat Button -->
        <a href="<?= e($globalWaUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-icon" title="Chat on WhatsApp" aria-label="Chat with travel specialist on WhatsApp">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 2.11.83 3.292.83 3.183 0 5.768-2.587 5.768-5.766.001-3.181-2.585-5.767-5.764-5.767zm7.653 5.766c0 4.225-3.438 7.663-7.654 7.663-1.309 0-2.569-.334-3.674-.925l-4.148 1.088 1.107-4.041c-.696-1.187-1.077-2.55-1.077-3.957 0-4.224 3.438-7.662 7.654-7.662 4.226 0 7.662 3.438 7.662 7.662z"/>
          </svg>
        </a>

        <!-- Plan My Trip Primary CTA -->
        <a href="<?= BASE_URL ?>/enquiry.php" class="btn btn-primary btn-plan">
          <span>Plan My Trip</span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </a>

        <!-- Mobile Hamburger Toggle -->
        <button class="mobile-nav-toggle" aria-label="Toggle navigation menu" type="button">
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>

    </div>
  </header>

  <!-- Mobile Drawer Menu -->
  <div class="mobile-drawer" id="mobileDrawer" aria-label="Mobile Navigation">
    <div>
      <div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:16px;border-bottom:1px solid rgba(255,255,255,0.1);">
        <div class="brand-logo">
          <img src="<?= ASSETS_URL ?>/images/logo/symbol.svg" alt="Midhila Voyages Emblem" class="brand-logo__symbol" width="36" height="36">
          <div class="brand-logo__text">
            <span class="brand-logo__name" style="font-size:1.1rem;">MIDHILA</span>
            <span class="brand-logo__subtitle">VOYAGES</span>
          </div>
        </div>
        <button class="mobile-drawer__close" style="color:#FFF;font-size:1.8rem;line-height:1;" aria-label="Close menu">&times;</button>
      </div>

      <nav class="mobile-drawer__links">
        <a href="<?= BASE_URL ?>/index.php" class="mobile-drawer__link <?= ($currentScript === 'index.php') ? 'is-active' : '' ?>">Home</a>
        <a href="<?= BASE_URL ?>/about.php" class="mobile-drawer__link <?= ($currentScript === 'about.php') ? 'is-active' : '' ?>">About Us</a>
        <a href="<?= BASE_URL ?>/destinations.php" class="mobile-drawer__link <?= in_array($currentScript, ['destinations.php', 'destination.php']) ? 'is-active' : '' ?>">Destinations</a>
        <a href="<?= BASE_URL ?>/packages.php" class="mobile-drawer__link <?= in_array($currentScript, ['packages.php', 'package.php']) ? 'is-active' : '' ?>">Tour Packages</a>
        <a href="<?= BASE_URL ?>/pilgrimage.php" class="mobile-drawer__link <?= ($currentScript === 'pilgrimage.php') ? 'is-active' : '' ?>">Pilgrimage Tours</a>
        <a href="<?= BASE_URL ?>/services.php" class="mobile-drawer__link <?= ($currentScript === 'services.php') ? 'is-active' : '' ?>">Travel Services</a>
        <a href="<?= BASE_URL ?>/gallery.php" class="mobile-drawer__link <?= ($currentScript === 'gallery.php') ? 'is-active' : '' ?>">Travel Gallery</a>
        <a href="<?= BASE_URL ?>/contact.php" class="mobile-drawer__link <?= ($currentScript === 'contact.php') ? 'is-active' : '' ?>">Contact Us</a>
        <a href="<?= BASE_URL ?>/faq.php" class="mobile-drawer__link <?= ($currentScript === 'faq.php') ? 'is-active' : '' ?>">FAQs</a>
      </nav>
    </div>

    <div style="display:flex;flex-direction:column;gap:12px;padding-top:24px;border-top:1px solid rgba(255,255,255,0.1);">
      <a href="<?= BASE_URL ?>/enquiry.php" class="btn btn-primary" style="width:100%;">
        Plan My Journey
      </a>
      <a href="<?= e($globalWaUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-teal" style="width:100%;">
        <span>WhatsApp Specialist</span>
      </a>
    </div>
  </div>
  <div class="mobile-drawer__overlay" id="mobileDrawerOverlay"></div>

  <!-- Flash Messages Alert (if any) -->
  <?= render_flash_messages() ?>
