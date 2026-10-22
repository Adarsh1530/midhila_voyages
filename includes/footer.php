<?php
/**
 * MIDHILA VOYAGES — Global Footer Component
 * Pure-White Cinematic Editorial Departure
 */
require_once __DIR__ . '/functions.php';

$agencyPhone = get_setting('primary_phone', '+91 8075021427');
$agencySecondaryPhone = get_setting('secondary_phone', '+91 9446154048');
$agencyHotlinePhone = get_setting('hotline_phone', '+91 8943669027');
$agencyWhatsApp = get_setting('whatsapp_number', '+918075021427');
$agencyEmail = get_setting('primary_email', get_setting('agency_email', 'contact@midhilavoyages.com'));
$officeAddress = get_setting('office_address', 'Kerala, India');
$footerDesc = get_setting('footer_description', 'A premier travel and pilgrimage management house dedicated to curating bespoke domestic journeys, sacred temple yatras, and luxury escapes with uncompromised integrity.');
$footerTagline = get_setting('footer_tagline', 'Beyond Destinations. Into Experiences.');
$siteName = get_setting('site_name', 'MIDHILA VOYAGES');

$defaultWaMsg = "Hello MIDHILA VOYAGES, I would like to enquire about bespoke journeys.";
$footerWaUrl = build_whatsapp_url($agencyWhatsApp, $defaultWaMsg);
?>

  <!-- Pure White Editorial Footer -->
  <footer class="site-footer">
    <div class="container">
      
      <div class="footer-grid">
        
        <!-- Column 1: Brand & Craft -->
        <div class="footer-col">
          <div class="brand-logo" style="margin-bottom: 20px;">
            <img src="<?= ASSETS_URL ?>/images/logo/midhila-voyages-logo.png" 
                 srcset="<?= ASSETS_URL ?>/images/logo/midhila-voyages-logo@2x.png 2x" 
                 alt="Midhila Voyages" 
                 class="brand-logo__img" 
                 width="186" 
                 height="50">
          </div>
          <p style="font-size:0.92rem;line-height:1.75;margin-bottom:20px;color:var(--color-text-secondary);max-width:380px;">
            <?= e($footerDesc) ?>
          </p>
          <div style="display:flex;flex-direction:column;gap:8px;font-size:0.86rem;color:var(--color-text-body);">
            <div><strong style="color:var(--color-text-main);">Hotline:</strong> <?= e($agencyPhone) ?>, <?= e($agencySecondaryPhone) ?>, <?= e($agencyHotlinePhone) ?></div>
            <div><strong style="color:var(--color-text-main);">Email:</strong> <?= e($agencyEmail) ?></div>
            <div><strong style="color:var(--color-text-main);">Headquarters:</strong> <?= e($officeAddress) ?></div>
          </div>
        </div>

        <!-- Column 2: Geographic Portfolios -->
        <div class="footer-col">
          <h4 class="footer-col__title">Destinations</h4>
          <div class="footer-links">
            <a href="<?= BASE_URL ?>/destination.php?slug=kerala" class="footer-link">Kerala Backwaters</a>
            <a href="<?= BASE_URL ?>/destination.php?slug=tamil-nadu" class="footer-link">Tamil Nadu Temples</a>
            <a href="<?= BASE_URL ?>/destination.php?slug=varanasi" class="footer-link">Varanasi Spiritual</a>
            <a href="<?= BASE_URL ?>/destination.php?slug=himalayas" class="footer-link">Himalayan Valleys</a>
            <a href="<?= BASE_URL ?>/destination.php?slug=lakshadweep" class="footer-link">Lakshadweep Atolls</a>
            <a href="<?= BASE_URL ?>/destination.php?slug=andaman" class="footer-link">Andaman Islands</a>
            <a href="<?= BASE_URL ?>/destinations.php" class="footer-link" style="color:var(--color-gold-deep);font-weight:700;">Explore All Portfolios &rarr;</a>
          </div>
        </div>

        <!-- Column 3: The Sanctuary & Experiences -->
        <div class="footer-col">
          <h4 class="footer-col__title">Experiences</h4>
          <div class="footer-links">
            <a href="<?= BASE_URL ?>/pilgrimage.php" class="footer-link">Sacred Vedic Yatras</a>
            <a href="<?= BASE_URL ?>/packages.php?category=family" class="footer-link">Curated Family Holidays</a>
            <a href="<?= BASE_URL ?>/packages.php?category=honeymoon" class="footer-link">Romantic Escapes</a>
            <a href="<?= BASE_URL ?>/packages.php?category=adventure" class="footer-link">High Altitude Trekking</a>
            <a href="<?= BASE_URL ?>/services.php" class="footer-link">Concierge Services</a>
            <a href="<?= BASE_URL ?>/gallery.php" class="footer-link">Travel Memoir Gallery</a>
          </div>
        </div>

        <!-- Column 4: Trust, Safety & Legal -->
        <div class="footer-col">
          <h4 class="footer-col__title">Client Assurance</h4>
          <p style="font-size:0.86rem;line-height:1.7;color:var(--color-text-muted);margin-bottom:16px;">
            Recognized hospitality curation, 24/7 dedicated on-ground transport coordination, verified accommodations, and transparent booking protection.
          </p>
          <div class="footer-links">
            <a href="<?= BASE_URL ?>/about.php" class="footer-link">The Midhila Philosophy</a>
            <a href="<?= BASE_URL ?>/faq.php" class="footer-link">Frequently Asked Questions</a>
            <a href="<?= BASE_URL ?>/terms.php" class="footer-link">Terms of Service</a>
            <a href="<?= BASE_URL ?>/privacy.php" class="footer-link">Privacy Policy</a>
            <a href="<?= BASE_URL ?>/refund-policy.php" class="footer-link">Booking & Refund Policy</a>
          </div>
        </div>

      </div>

      <!-- Bottom Bar -->
      <div class="footer-bottom">
        <div>
          &copy; <?= date('Y') ?> <?= e($siteName) ?>. All rights reserved.
        </div>
        <div>
          <?= e($footerTagline) ?>
        </div>
      </div>

    </div>
  </footer>

  <!-- Floating WhatsApp Action Button (Bottom-Right) -->
  <a href="<?= e($footerWaUrl) ?>" target="_blank" rel="noopener noreferrer" 
     class="fab-whatsapp"
     style="position:fixed;bottom:28px;right:28px;z-index:900;display:inline-flex;align-items:center;justify-content:center;width:54px;height:54px;background:#25D366;color:#FFF;border-radius:50%;box-shadow:0 8px 24px rgba(37,211,102,0.4);text-decoration:none;transition:transform 0.25s cubic-bezier(0.16,1,0.3,1),box-shadow 0.25s ease;"
     onmouseover="this.style.transform='scale(1.1) translateY(-3px)';this.style.boxShadow='0 12px 30px rgba(37,211,102,0.55)';"
     onmouseout="this.style.transform='scale(1) translateY(0)';this.style.boxShadow='0 8px 24px rgba(37,211,102,0.4)';"
     aria-label="Enquire directly on WhatsApp">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
      <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M8.53 7.33C8.37 7.33 8.1 7.39 7.87 7.64C7.65 7.89 7 8.5 7 9.71C7 10.93 7.89 12.1 8 12.27C8.14 12.44 9.76 14.94 12.25 16C12.84 16.27 13.3 16.42 13.66 16.53C14.25 16.72 14.79 16.69 15.22 16.63C15.7 16.56 16.68 16.03 16.89 15.45C17.1 14.87 17.1 14.38 17.04 14.27C16.97 14.17 16.81 14.11 16.56 13.99C16.32 13.86 15.12 13.27 14.9 13.19C14.67 13.1 14.5 13.06 14.34 13.31C14.18 13.56 13.71 14.11 13.56 14.27C13.42 14.44 13.27 14.46 13.03 14.34C12.78 14.21 11.74 13.87 10.51 12.77C9.56 11.91 8.91 10.86 8.79 10.65C8.66 10.45 8.77 10.33 8.89 10.21C9 10.1 9.15 9.91 9.28 9.76C9.41 9.62 9.45 9.5 9.53 9.35C9.62 9.2 9.58 9.06 9.52 8.94C9.45 8.82 9 7.72 8.81 7.25C8.63 6.79 8.44 6.86 8.3 6.85C8.17 6.85 8.01 6.85 7.85 6.85"/>
    </svg>
  </a>

</body>
</html>
