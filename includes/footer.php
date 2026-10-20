<?php
/**
 * MIDHILA VOYAGES — Global Footer Component
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$agencyPhone = get_setting('primary_phone', '+91 12345 67890');
$agencySecondaryPhone = get_setting('secondary_phone', '+91 12345 67890');
$agencyWhatsApp = get_setting('whatsapp_number', '+911234567890');
$agencyEmail = get_setting('primary_email', 'journeys@midhilavoyages.com');
$agencyAddress = get_setting('office_address', 'Midhila Voyages, Travel & Tourism Hub, MG Road, Kochi, Kerala - 682016, India');
$workingHours = get_setting('working_hours', 'Monday – Saturday: 9:00 AM – 7:30 PM (IST)');

$waFloatMessage = "Hello MIDHILA VOYAGES, I would like to plan a journey with your travel specialists.";
$waFloatUrl = build_whatsapp_url($agencyWhatsApp, $waFloatMessage);
?>

  <!-- Cinematic Sunset CTA Section with Glowing Route Lines -->
  <section class="sunset-cta-section" style="position:relative;background:radial-gradient(circle at 50% 20%, rgba(255, 122, 0, 0.25) 0%, rgba(247, 37, 133, 0.15) 40%, rgba(6, 16, 30, 0.95) 85%, var(--color-navy-deep) 100%);padding:clamp(70px, 9vw, 120px) 0;overflow:hidden;border-top:1px solid rgba(255, 122, 0, 0.35);">
    
    <!-- Animated SVG Route Horizon -->
    <svg style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;opacity:0.35;" viewBox="0 0 1440 600" fill="none">
      <path class="hero-route-line" d="M-50,300 C300,150 600,450 900,200 C1150,50 1350,400 1500,250" stroke="url(#sunsetGlowGrad)" stroke-width="2.5" />
      <circle cx="900" cy="200" r="7" fill="#00E5FF" class="route-pin-pulse" />
      <circle cx="900" cy="200" r="16" stroke="#00E5FF" stroke-width="1.5" stroke-dasharray="3 3" class="rotate-slow" />
      <defs>
        <linearGradient id="sunsetGlowGrad" x1="0%" y1="0%" x2="100%" y2="0%">
          <stop offset="0%" stop-color="#FF7A00"/>
          <stop offset="50%" stop-color="#FF6B6B"/>
          <stop offset="80%" stop-color="#00C4B4"/>
          <stop offset="100%" stop-color="#00E5FF"/>
        </linearGradient>
      </defs>
    </svg>

    <div class="container" style="position:relative;z-index:2;text-align:center;max-width:880px;">
      <div class="reveal-fade-up" style="display:inline-flex;align-items:center;gap:10px;background:rgba(255, 122, 0, 0.15);border:1px solid rgba(255, 122, 0, 0.4);padding:6px 20px;border-radius:var(--radius-pill);margin-bottom:24px;">
        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#FF7A00;box-shadow:0 0 10px #FF7A00;"></span>
        <span style="font-size:0.8rem;letter-spacing:0.2em;text-transform:uppercase;color:#FED7AA;font-weight:700;">Your Journey Begins Here</span>
      </div>

      <h2 class="reveal-fade-up stagger-1 editorial-giant-title" style="color:#FFFFFF;margin-bottom:20px;text-shadow:0 4px 25px rgba(0,0,0,0.4);">
        Ready to Experience Horizons Crafted with Heart &amp; Soul?
      </h2>

      <p class="reveal-fade-up stagger-2 lead-text" style="color:#CBD5E0;max-width:680px;margin:0 auto 36px;">
        Whether it is the tranquil backwaters of Kerala, sacred sanctums of Varanasi, or turquoise lagoons of Lakshadweep — our travel architects craft seamless, memorable voyages.
      </p>

      <div class="reveal-fade-up stagger-3" style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>/enquiry.php" class="btn btn-vibrant-coral btn-lg">
          <span>Plan My Custom Journey</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
        <a href="<?= e($waFloatUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-vibrant-teal btn-lg">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 2.11.83 3.292.83 3.183 0 5.768-2.587 5.768-5.766.001-3.181-2.585-5.767-5.764-5.767zm7.653 5.766c0 4.225-3.438 7.663-7.654 7.663-1.309 0-2.569-.334-3.674-.925l-4.148 1.088 1.107-4.041c-.696-1.187-1.077-2.55-1.077-3.957 0-4.224 3.438-7.662 7.654-7.662 4.226 0 7.662 3.438 7.662 7.662z"/></svg>
          <span>WhatsApp Consultation</span>
        </a>
      </div>
    </div>
  </section>

  <!-- Global Footer -->
  <footer class="site-footer">
    <div class="container">
      
      <div class="footer-top">
        
        <!-- Column 1: Brand Intro -->
        <div class="footer-col">
          <div class="brand-logo" style="margin-bottom: 20px;">
            <img src="<?= ASSETS_URL ?>/images/logo/midhila-voyages-logo-white.png" 
                 srcset="<?= ASSETS_URL ?>/images/logo/midhila-voyages-logo-white@2x.png 2x" 
                 alt="Midhila Voyages" 
                 class="brand-logo__img" 
                 width="186" 
                 height="50">
          </div>
          <p style="font-size:0.92rem;line-height:1.7;margin-bottom:20px;color:#A0AEC0;">
            A premier travel and pilgrimage management agency dedicated to curating bespoke domestic holidays, inspiring international escapes, and deeply reverent spiritual yatras with transparent planning.
          </p>
          <div style="display:flex;gap:12px;">
            <a href="<?= e(get_setting('facebook_url', '#')) ?>" target="_blank" rel="noopener noreferrer" style="color:var(--color-turquoise);width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.06);display:flex;align-items:center;justify-content:center;" aria-label="Facebook">
              <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.667 5H18V0h-3.808C10.595 0 9 1.582 9 4.615V8z"/></svg>
            </a>
            <a href="<?= e(get_setting('instagram_url', '#')) ?>" target="_blank" rel="noopener noreferrer" style="color:var(--color-coral);width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.06);display:flex;align-items:center;justify-content:center;" aria-label="Instagram">
              <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
            </a>
            <a href="<?= e(get_setting('youtube_url', '#')) ?>" target="_blank" rel="noopener noreferrer" style="color:var(--color-sunset);width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.06);display:flex;align-items:center;justify-content:center;" aria-label="YouTube">
              <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
            </a>
          </div>
        </div>

        <!-- Column 2: Navigation & Discover -->
        <div class="footer-col">
          <h4>Explore Midhila</h4>
          <div class="footer-links">
            <a href="<?= BASE_URL ?>/index.php">Home Horizon</a>
            <a href="<?= BASE_URL ?>/about.php">About Midhila Voyages</a>
            <a href="<?= BASE_URL ?>/destinations.php">All Regional Destinations</a>
            <a href="<?= BASE_URL ?>/packages.php">Curated Tour Packages</a>
            <a href="<?= BASE_URL ?>/pilgrimage.php">Sacred Pilgrimage Trails</a>
            <a href="<?= BASE_URL ?>/services.php">Bespoke Travel Services</a>
            <a href="<?= BASE_URL ?>/gallery.php">Voyage Photo Memoir</a>
            <a href="<?= BASE_URL ?>/faq.php">Frequently Asked Questions</a>
          </div>
        </div>

        <!-- Column 3: Regional Horizons with Distinct Badges -->
        <div class="footer-col">
          <h4>Regional Horizons</h4>
          <div class="footer-links">
            <a href="<?= BASE_URL ?>/destination.php?slug=kerala" style="display:flex;align-items:center;gap:8px;">
              <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#059669;"></span>
              <span>Kerala Backwaters</span>
            </a>
            <a href="<?= BASE_URL ?>/destination.php?slug=tamil-nadu" style="display:flex;align-items:center;gap:8px;">
              <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#C2410C;"></span>
              <span>Tamil Nadu Temples</span>
            </a>
            <a href="<?= BASE_URL ?>/destination.php?slug=lakshadweep" style="display:flex;align-items:center;gap:8px;">
              <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#06B6D4;"></span>
              <span>Lakshadweep Coral Reefs</span>
            </a>
            <a href="<?= BASE_URL ?>/destination.php?slug=andaman-islands" style="display:flex;align-items:center;gap:8px;">
              <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#0284C7;"></span>
              <span>Andaman Tropical Islands</span>
            </a>
            <a href="<?= BASE_URL ?>/destination.php?slug=varanasi" style="display:flex;align-items:center;gap:8px;">
              <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#EA580C;"></span>
              <span>Varanasi Kashi Sanctum</span>
            </a>
            <a href="<?= BASE_URL ?>/destination.php?slug=prayagraj" style="display:flex;align-items:center;gap:8px;">
              <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#FBBF24;"></span>
              <span>Prayagraj Holy Sangam</span>
            </a>
            <a href="<?= BASE_URL ?>/destination.php?slug=himalayas" style="display:flex;align-items:center;gap:8px;">
              <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#38BDF8;"></span>
              <span>Himalayan Mountain Peaks</span>
            </a>
            <a href="<?= BASE_URL ?>/destination.php?slug=hyderabad" style="display:flex;align-items:center;gap:8px;">
              <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#D97706;"></span>
              <span>Hyderabad Nizam Heritage</span>
            </a>
          </div>
        </div>

        <!-- Column 4: Contact & Office -->
        <div class="footer-col">
          <h4>Contact & Assistance</h4>
          <div class="footer-contact-item">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <span><?= e($agencyAddress) ?></span>
          </div>
          <div class="footer-contact-item">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <div>
              <a href="tel:<?= preg_replace('/[^0-9+]/', '', $agencyPhone) ?>"><?= e($agencyPhone) ?></a><br>
              <a href="tel:<?= preg_replace('/[^0-9+]/', '', $agencySecondaryPhone) ?>"><?= e($agencySecondaryPhone) ?></a>
            </div>
          </div>
          <div class="footer-contact-item">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <a href="mailto:<?= e($agencyEmail) ?>"><?= e($agencyEmail) ?></a>
          </div>
          <div class="footer-contact-item">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span><?= e($workingHours) ?></span>
          </div>
        </div>

      </div>

      <!-- Footer Bottom Legal & Copyright -->
      <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> MIDHILA VOYAGES. All rights reserved.</p>
        <div class="footer-legal-links">
          <a href="<?= BASE_URL ?>/privacy.php">Privacy Policy</a>
          <a href="<?= BASE_URL ?>/terms.php">Terms &amp; Conditions</a>
          <a href="<?= BASE_URL ?>/refund-policy.php">Cancellation &amp; Refund Policy</a>
          <a href="<?= BASE_URL ?>/admin/login.php" style="opacity:0.5;">Agency Portal</a>
        </div>
      </div>

    </div>
  </footer>

  <!-- Floating WhatsApp Quick Button -->
  <a href="<?= e($waFloatUrl) ?>" target="_blank" rel="noopener noreferrer" class="fab-whatsapp" aria-label="Chat with Midhila Voyages on WhatsApp">
    <svg width="30" height="30" viewBox="0 0 24 24" fill="currentColor">
      <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 2.11.83 3.292.83 3.183 0 5.768-2.587 5.768-5.766.001-3.181-2.585-5.767-5.764-5.767zm7.653 5.766c0 4.225-3.438 7.663-7.654 7.663-1.309 0-2.569-.334-3.674-.925l-4.148 1.088 1.107-4.041c-.696-1.187-1.077-2.55-1.077-3.957 0-4.224 3.438-7.662 7.654-7.662 4.226 0 7.662 3.438 7.662 7.662z"/>
    </svg>
    <span class="fab-whatsapp__tooltip">Chat on WhatsApp</span>
  </a>

  <!-- Core Scripts -->
  <script src="<?= ASSETS_URL ?>/js/main.js" defer></script>
  <script src="<?= ASSETS_URL ?>/js/animations.js" defer></script>
</body>
</html>
