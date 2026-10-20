<?php
/**
 * MIDHILA VOYAGES — Global Footer Component
 * Pure-White Cinematic Editorial Departure
 */
require_once __DIR__ . '/functions.php';

$agencyPhone = get_setting('primary_phone', '+91 8075021427');
$agencyWhatsApp = get_setting('whatsapp_number', '+918075021427');
$agencyEmail = get_setting('agency_email', 'contact@midhilavoyages.com');
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
            A premier travel and pilgrimage management house dedicated to curating bespoke domestic journeys, sacred temple yatras, and luxury escapes with uncompromised integrity.
          </p>
          <div style="display:flex;flex-direction:column;gap:8px;font-size:0.86rem;color:var(--color-text-body);margin-bottom:24px;">
            <div><strong style="color:var(--color-text-main);">Hotline:</strong> +91 8075021427, 9446154048, 8943669027</div>
            <div><strong style="color:var(--color-text-main);">Email:</strong> <?= e($agencyEmail) ?></div>
            <div><strong style="color:var(--color-text-main);">Headquarters:</strong> Kerala, India</div>
          </div>
          <a href="<?= e($footerWaUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-solid" style="padding:10px 20px;font-size:0.82rem;">
            <span>Chat with Concierge</span>
          </a>
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
          &copy; <?= date('Y') ?> MIDHILA VOYAGES. All rights reserved. Curating Horizons &bull; Sacred Trails.
        </div>
        <div style="display:flex;gap:20px;">
          <span>Explore</span>
          <span>•</span>
          <span>Experience</span>
          <span>•</span>
          <span>Reconnect</span>
        </div>
      </div>

    </div>
  </footer>

  <!-- Floating WhatsApp Action Pill (Bottom-Right) -->
  <a href="<?= e($footerWaUrl) ?>" target="_blank" rel="noopener noreferrer" 
     style="position:fixed;bottom:28px;right:28px;z-index:900;display:inline-flex;align-items:center;gap:10px;background:#128C7E;color:#FFF;padding:12px 20px;border-radius:9999px;box-shadow:0 8px 30px rgba(18,140,126,0.35);font-size:0.86rem;font-weight:700;letter-spacing:0.04em;text-decoration:none;transition:transform 0.2s cubic-bezier(0.16,1,0.3,1);"
     onmouseover="this.style.transform='scale(1.05) translateY(-3px)';"
     onmouseout="this.style.transform='scale(1) translateY(0)';"
     aria-label="Enquire directly on WhatsApp">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
      <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 2.11.83 3.292.83 3.183 0 5.768-2.587 5.768-5.766.001-3.181-2.585-5.767-5.764-5.767zm7.653 5.766c0 4.225-3.438 7.663-7.654 7.663-1.309 0-2.569-.334-3.674-.925l-4.148 1.088 1.107-4.041c-.696-1.187-1.077-2.55-1.077-3.957 0-4.224 3.438-7.662 7.654-7.662 4.226 0 7.662 3.438 7.662 7.662z"/>
    </svg>
    <span>WhatsApp Us</span>
  </a>

</body>
</html>
