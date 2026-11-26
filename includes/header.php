<?php
/**
 * MIDHILA VOYAGES — Global Header Component
 * Cinematic Pure-White Editorial Hospitality
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? get_setting('site_name', 'MIDHILA VOYAGES') . ' | Travelled by Scroll • Sacred & Sublime Horizons';
$pageDescription = $pageDescription ?? get_setting('site_description', 'Bespoke domestic, international, and sacred pilgrimage journeys crafted with care, integrity, and personalised luxury.');
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestPath = trim($requestPath, '/');
$currentScript = !empty($requestPath) ? basename($requestPath) : basename($_SERVER['PHP_SELF'] ?? 'index.php');
if (empty($currentScript) || $currentScript === '/' || $currentScript === 'api') {
    $currentScript = 'index.php';
}
$isRootHomePage = empty($requestPath) || in_array($requestPath, ['index.php', 'index']);

$agencyPhone = get_setting('primary_phone', '+91 62829 48227');
$agencyWhatsApp = get_setting('whatsapp_number', '+916282948227');
$defaultWaMsg = "Hello MIDHILA VOYAGES, I would like to explore tour packages and plan my journey.";
$globalWaUrl = build_whatsapp_url($agencyWhatsApp, $defaultWaMsg);
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>" class="<?= current_lang() !== 'en' ? 'lang-' . e(current_lang()) : '' ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
  
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($pageDescription) ?>">
  <link rel="canonical" href="<?= e(BASE_URL . '/' . $currentScript) ?>">
  
  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="<?= ASSETS_URL ?>/images/favicon.svg">
  
  <!-- Google Fonts: Cinzel, Cormorant Garamond, Plus Jakarta Sans, Noto Sans Malayalam, Noto Sans Tamil -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700;800;900&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Noto+Sans+Malayalam:wght@400;500;600;700;800&family=Noto+Sans+Tamil:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Stylesheets -->
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/animations.css">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/responsive.css">

  <style>
    /* Google Translate Silent Seamless Integration - Complete Banner & Toolbar Suppression */
    .goog-te-banner-frame,
    .goog-te-banner-frame.skiptranslate,
    iframe.goog-te-banner-frame,
    iframe.skiptranslate,
    body > iframe.skiptranslate,
    body > iframe[id*=":1.container"],
    body > .skiptranslate:not(#google_translate_element),
    .VIpgJd-ZVi9I-OR9QNe-HandL,
    .VIpgJd-ZVi9I-OR9QNe-HandL-nohd,
    .VIpgJd-ZVi9I-aZ2wEe-wOHMyf,
    #goog-gt-tt,
    .goog-te-balloon-frame,
    .goog-tooltip,
    .goog-tooltip:hover {
      display: none !important;
      visibility: hidden !important;
      opacity: 0 !important;
      height: 0 !important;
      width: 0 !important;
      max-height: 0 !important;
      border: none !important;
      pointer-events: none !important;
      z-index: -9999 !important;
    }
    html, body {
      top: 0px !important;
      position: static !important;
      margin-top: 0px !important;
    }
    .goog-text-highlight {
      background: transparent !important;
      box-shadow: none !important;
    }
    .goog-te-gadget,
    .goog-te-gadget span,
    .goog-logo-link {
      display: none !important;
    }
    #google_translate_element {
      display: none !important;
      width: 0 !important;
      height: 0 !important;
      overflow: hidden !important;
    }
    .notranslate {
      translate: no;
    }
  </style>

  <!-- Three.js 3D Engine & GSAP ScrollTrigger -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js" defer></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js" defer></script>
  <script src="<?= ASSETS_URL ?>/js/animations.js" defer></script>

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

  <?php if ($isRootHomePage): ?>
  <!-- Minimal Discrete Voyage Loader (< 0.6s non-blocking) - First Entry to Homepage Only -->
  <script>
    (function() {
      try {
        if (sessionStorage.getItem('mv_intro_seen')) {
          document.documentElement.classList.add('hide-voyage-loader');
        }
      } catch (e) {}
    })();
  </script>
  <style>
    html.hide-voyage-loader #voyageLoader {
      display: none !important;
    }
  </style>
  <div class="voyage-loader" id="voyageLoader" aria-hidden="true">
    <div class="voyage-loader__mark">MIDHILA VOYAGES</div>
    <div class="voyage-loader__bar">
      <div class="voyage-loader__progress"></div>
    </div>
  </div>
  <script>
    try {
      sessionStorage.setItem('mv_intro_seen', '1');
    } catch (e) {}
  </script>
  <?php endif; ?>

  <!-- Pure White Floating Header -->
  <header class="site-header" id="siteHeader">
    <div class="container header-container">
      
      <!-- Brand Logo (Authentic Navy & Gold on Pure White) -->
      <a href="<?= BASE_URL ?>/index.php" class="brand-logo" aria-label="Midhila Voyages Home">
        <img src="<?= ASSETS_URL ?>/images/logo/midhila-voyages-logo.png" 
             srcset="<?= ASSETS_URL ?>/images/logo/midhila-voyages-logo@2x.png 2x" 
             alt="Midhila Voyages" 
             class="brand-logo__img" 
             width="176" 
             height="48">
      </a>

      <!-- Desktop Navigation Menu -->
      <nav class="nav-menu" aria-label="Main Navigation">
        <a href="<?= BASE_URL ?>/index.php" class="nav-link <?= ($currentScript === 'index.php') ? 'is-active' : '' ?>"><?= __t('nav_home') ?></a>
        <a href="<?= BASE_URL ?>/about.php" class="nav-link <?= ($currentScript === 'about.php') ? 'is-active' : '' ?>"><?= __t('nav_about') ?></a>
        <a href="<?= BASE_URL ?>/kerala-tourism.php" class="nav-link <?= in_array($currentScript, ['kerala-tourism.php']) ? 'is-active' : '' ?>"><?= __t('nav_kerala_tourism') ?></a>
        <a href="<?= BASE_URL ?>/packages.php" class="nav-link <?= in_array($currentScript, ['packages.php', 'package.php']) ? 'is-active' : '' ?>"><?= __t('nav_packages') ?></a>
        <a href="<?= BASE_URL ?>/contact.php" class="nav-link <?= ($currentScript === 'contact.php') ? 'is-active' : '' ?>"><?= __t('nav_contact') ?></a>
        
        <!-- More Dropdown for Destinations, Custom Tour, Pilgrimage Tourism, and FAQs -->
        <div class="nav-dropdown" id="navMoreDropdown">
          <button type="button" class="nav-dropdown-btn <?= in_array($currentScript, ['destinations.php', 'destination.php', 'custom-tour.php', 'pilgrimage.php', 'faq.php']) ? 'is-active' : '' ?>" aria-haspopup="true" aria-expanded="false">
            <span><?= __t('nav_more', 'More') ?></span>
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
          </button>
          <div class="nav-dropdown-menu" id="navMoreMenu">
            <a href="<?= BASE_URL ?>/destinations.php" class="nav-dropdown-item <?= in_array($currentScript, ['destinations.php', 'destination.php']) ? 'is-active' : '' ?>"><?= __t('nav_destinations') ?></a>
            <a href="<?= BASE_URL ?>/custom-tour.php" class="nav-dropdown-item <?= ($currentScript === 'custom-tour.php') ? 'is-active' : '' ?>"><?= __t('nav_custom_tour') ?></a>
            <a href="<?= BASE_URL ?>/pilgrimage.php" class="nav-dropdown-item <?= ($currentScript === 'pilgrimage.php') ? 'is-active' : '' ?>"><?= __t('nav_pilgrimage') ?></a>
            <a href="<?= BASE_URL ?>/faq.php" class="nav-dropdown-item <?= ($currentScript === 'faq.php') ? 'is-active' : '' ?>"><?= __t('nav_faqs') ?></a>
          </div>
        </div>
      </nav>

      <!-- Header Action Buttons -->
      <div class="header-actions">
        <!-- Language Switcher (English, Malayalam, Tamil) -->
        <?= render_language_switcher() ?>

        <!-- Plan My Journey Primary CTA -->
        <a href="<?= BASE_URL ?>/enquiry.php" class="btn-cinematic-primary btn-header-cta">
          <span><?= __t('btn_plan_trip') ?></span>
        </a>

        <!-- Mobile Hamburger Toggle -->
        <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle navigation menu" type="button">
          <span class="mobile-toggle__bar"></span>
          <span class="mobile-toggle__bar"></span>
          <span class="mobile-toggle__bar"></span>
        </button>
      </div>

    </div>
  </header>

  <!-- Mobile Drawer Menu -->
  <div class="mobile-drawer" id="mobileDrawer" aria-label="Mobile Navigation">
    <div style="overflow-y:auto;flex:1 1 auto;padding-right:4px;margin-bottom:16px;">
      <div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:16px;border-bottom:1px solid var(--color-border);">
        <div class="brand-logo">
          <img src="<?= ASSETS_URL ?>/images/logo/midhila-voyages-logo.png" 
               srcset="<?= ASSETS_URL ?>/images/logo/midhila-voyages-logo@2x.png 2x" 
               alt="Midhila Voyages" 
               class="brand-logo__img" 
               width="154" 
               height="42">
        </div>
        <button class="mobile-drawer__close" style="color:var(--color-text-main);font-size:1.8rem;line-height:1;background:none;border:none;cursor:pointer;" aria-label="Close menu">&times;</button>
      </div>

      <nav class="mobile-drawer__links" style="margin-top:24px;gap:14px;">
        <a href="<?= BASE_URL ?>/index.php" class="mobile-drawer__link <?= ($currentScript === 'index.php') ? 'is-active' : '' ?>"><?= __t('nav_home') ?></a>
        <a href="<?= BASE_URL ?>/about.php" class="mobile-drawer__link <?= ($currentScript === 'about.php') ? 'is-active' : '' ?>"><?= __t('nav_about') ?></a>
        <a href="<?= BASE_URL ?>/kerala-tourism.php" class="mobile-drawer__link <?= ($currentScript === 'kerala-tourism.php') ? 'is-active' : '' ?>" style="color:#0F172A;font-weight:700;"><?= __t('nav_kerala_tourism') ?></a>
        <a href="<?= BASE_URL ?>/packages.php" class="mobile-drawer__link <?= in_array($currentScript, ['packages.php', 'package.php']) ? 'is-active' : '' ?>"><?= __t('nav_packages') ?></a>
        <a href="<?= BASE_URL ?>/contact.php" class="mobile-drawer__link <?= ($currentScript === 'contact.php') ? 'is-active' : '' ?>"><?= __t('nav_contact') ?></a>

        <!-- More Subgroup on Mobile -->
        <div class="mobile-drawer__subgroup" style="margin-top:8px;padding-top:14px;border-top:1px dashed var(--color-border);">
          <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-gold);font-weight:700;margin-bottom:12px;">
            <?= __t('nav_more', 'More') ?>
          </div>
          <div style="display:flex;flex-direction:column;gap:12px;padding-left:6px;">
            <a href="<?= BASE_URL ?>/destinations.php" class="mobile-drawer__link <?= in_array($currentScript, ['destinations.php', 'destination.php']) ? 'is-active' : '' ?>" style="font-size:1.1rem;padding-bottom:6px;"><?= __t('nav_destinations') ?></a>
            <a href="<?= BASE_URL ?>/custom-tour.php" class="mobile-drawer__link <?= ($currentScript === 'custom-tour.php') ? 'is-active' : '' ?>" style="font-size:1.1rem;padding-bottom:6px;"><?= __t('nav_custom_tour') ?></a>
            <a href="<?= BASE_URL ?>/pilgrimage.php" class="mobile-drawer__link <?= ($currentScript === 'pilgrimage.php') ? 'is-active' : '' ?>" style="font-size:1.1rem;padding-bottom:6px;"><?= __t('nav_pilgrimage') ?></a>
            <a href="<?= BASE_URL ?>/faq.php" class="mobile-drawer__link <?= ($currentScript === 'faq.php') ? 'is-active' : '' ?>" style="font-size:1.1rem;padding-bottom:6px;"><?= __t('nav_faqs') ?></a>
          </div>
        </div>
      </nav>
    </div>

    <div style="display:flex;flex-direction:column;gap:12px;padding-top:20px;border-top:1px solid var(--color-border);" class="notranslate">
      <div style="display:flex;gap:6px;justify-content:center;margin-bottom:8px;">
        <a href="<?= htmlspecialchars(lang_url('en')) ?>" onclick="return switchWebsiteLanguage('en', event);" style="padding:6px 12px;border-radius:6px;border:1px solid #CBD5E1;font-size:0.8rem;text-decoration:none;font-weight:700;color:<?= current_lang() === 'en' ? '#FFFFFF;background:#0F172A' : '#334155;background:#FFFFFF' ?>;">English</a>
        <a href="<?= htmlspecialchars(lang_url('ml')) ?>" onclick="return switchWebsiteLanguage('ml', event);" style="padding:6px 12px;border-radius:6px;border:1px solid #CBD5E1;font-size:0.8rem;text-decoration:none;font-weight:700;color:<?= current_lang() === 'ml' ? '#FFFFFF;background:#0F172A' : '#334155;background:#FFFFFF' ?>;">മലയാളം</a>
        <a href="<?= htmlspecialchars(lang_url('ta')) ?>" onclick="return switchWebsiteLanguage('ta', event);" style="padding:6px 12px;border-radius:6px;border:1px solid #CBD5E1;font-size:0.8rem;text-decoration:none;font-weight:700;color:<?= current_lang() === 'ta' ? '#FFFFFF;background:#0F172A' : '#334155;background:#FFFFFF' ?>;">தமிழ்</a>
      </div>
      <a href="<?= BASE_URL ?>/enquiry.php" class="btn-cinematic-primary" style="width:100%;">
        <?= __t('btn_plan_trip') ?>
      </a>
      <a href="<?= e($globalWaUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-solid" style="width:100%;">
        <span><?= __t('btn_whatsapp_us') ?></span>
      </a>
    </div>
  </div>

  <script>
    // Universal Website Language Switcher
    function switchWebsiteLanguage(langCode, event) {
      if (event && event.preventDefault) {
        event.preventDefault();
      }
      var gtVal = (langCode === 'en') ? '/en/en' : ('/en/' + langCode);
      var expires = new Date(Date.now() + 30 * 86400 * 1000).toUTCString();
      
      document.cookie = 'googtrans=' + gtVal + '; path=/; expires=' + expires + '; SameSite=Lax';
      try {
        document.cookie = 'googtrans=' + gtVal + '; domain=' + window.location.hostname + '; path=/; expires=' + expires + '; SameSite=Lax';
        document.cookie = 'googtrans=' + gtVal + '; domain=.' + window.location.hostname + '; path=/; expires=' + expires + '; SameSite=Lax';
      } catch (err) {}
      document.cookie = 'mv_lang=' + langCode + '; path=/; expires=' + expires + '; SameSite=Lax';
      
      var selectEl = document.querySelector('.goog-te-combo');
      if (selectEl) {
        selectEl.value = langCode;
        selectEl.dispatchEvent(new Event('change'));
      }
      
      var url = new URL(window.location.href);
      url.searchParams.set('lang', langCode);
      window.location.href = url.toString();
      return false;
    }

    // Language Switcher Dropdown Click Handler
    document.addEventListener('DOMContentLoaded', function() {
      var switcherBtn = document.getElementById('langSwitcherBtn');
      var switcherMenu = document.getElementById('langSwitcherMenu');
      if (switcherBtn && switcherMenu) {
        switcherBtn.addEventListener('click', function(e) {
          e.stopPropagation();
          var isExpanded = switcherMenu.style.display === 'block';
          switcherMenu.style.display = isExpanded ? 'none' : 'block';
          switcherBtn.setAttribute('aria-expanded', !isExpanded);
        });
        document.addEventListener('click', function(e) {
          if (!switcherBtn.contains(e.target) && !switcherMenu.contains(e.target)) {
            switcherMenu.style.display = 'none';
            switcherBtn.setAttribute('aria-expanded', 'false');
          }
        });
      }
    });
  </script>

  <!-- Flash Messages Alert (if any) -->
  <?= render_flash_messages() ?>
