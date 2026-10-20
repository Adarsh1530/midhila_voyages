<?php
/**
 * MIDHILA VOYAGES — About Us
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'About Us | The Philosophy of MIDHILA VOYAGES';
$pageDescription = 'Learn about Midhila Voyages, our dedication to personalized leisure holidays, authentic cultural discoveries, and sacred pilgrimage journeys across India and beyond.';

$agencyWhatsApp = get_setting('whatsapp_number', '+911234567890');
$agencyPhone = get_setting('primary_phone', '+91 12345 67890');

require_once __DIR__ . '/includes/header.php';
?>

<!-- Banner Header -->
<section style="position:relative;background-color:var(--color-navy-deep);padding-top:calc(var(--header-height) + 50px);padding-bottom:70px;color:#FFF;overflow:hidden;">
  <div style="position:absolute;inset:0;opacity:0.25;">
    <img src="https://images.unsplash.com/photo-1593693397690-362cb9666fc2?auto=format&fit=crop&w=1800&q=80" alt="Misty mountain valley" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
    <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(6,16,30,0.7) 0%, var(--color-navy-deep) 100%);"></div>
  </div>
  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:760px;">
    <div class="float-slow" style="display:inline-block;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.2em;color:#4EECD8;margin-bottom:12px;font-weight:700;background:rgba(0,196,180,0.12);padding:6px 18px;border-radius:var(--radius-pill);border:1px solid rgba(0,196,180,0.35);">✨ Our Story &amp; Purpose</div>
    <h1 class="editorial-headline" style="color:#FFF;font-size:clamp(2.4rem, 4vw, 3.6rem);margin-bottom:16px;text-shadow:0 0 20px rgba(0,196,180,0.25);">The Heart Behind Midhila Voyages</h1>
    <p class="lead-text" style="color:#CBD5E0;">We cultivate deep encounters with landscapes, cultural heritages, and sacred traditions through personalized, thoughtful travel design.</p>
  </div>
</section>

<!-- Brand Philosophy Section -->
<section class="section-padding" style="background-color:transparent;">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:clamp(40px, 6vw, 70px);align-items:center;">
      
      <div class="reveal-fade-left">
        <span class="section-subtitle no-after">A Mindful Journey</span>
        <h2 class="section-title">Beyond Mere Sightseeing — Travel as a Sacred Art</h2>
        <p class="lead-text" style="margin-bottom:20px;">
          Founded with a commitment to uncompromised comfort, genuine hospitality, and complete transparency, <strong>MIDHILA VOYAGES</strong> bridges the gap between commercial tourism and genuine exploration.
        </p>
        <p style="margin-bottom:20px;line-height:1.75;color:var(--color-text-body);">
          We understand that no two travellers are identical. A family holiday with young children requires spacious transitions and engaging stops; a senior citizen couple seeking blessings across South Indian temples needs tranquil pacing and proximity; and honeymooners seek intimate, private moments in breathtaking locales.
        </p>
        <p style="line-height:1.75;color:var(--color-text-body);">
          We do not believe in oversized bus herds or rigid formulas. Our travel team directly curates every hotel, inspects vehicle cleanliness, negotiates with local boatmen and drivers, and stays in continuous contact throughout your itinerary.
        </p>
      </div>

      <div class="reveal-fade-right">
        <div style="position:relative;">
          <div style="border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-xl);border:6px solid rgba(255,255,255,0.8);">
            <img src="https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1000&q=80" alt="Kerala traditional houseboat" style="width:100%;height:440px;object-fit:cover;" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
          </div>
          <div class="card-3d float-slow glass-specular" style="position:absolute;bottom:-20px;right:-15px;background:rgba(14,23,46,0.85);backdrop-filter:blur(20px);color:#FFF;padding:22px 26px;border-radius:var(--radius-md);box-shadow:0 20px 40px rgba(0,0,0,0.4);border:1px solid rgba(196,181,253,0.35);max-width:280px;">
            <div style="font-family:var(--font-serif);font-size:1.1rem;color:var(--color-lavender);margin-bottom:4px;font-weight:700;">100% Dedicated Care</div>
            <div style="font-size:0.82rem;color:#CBD5E0;">Real travel consultants answering your calls and WhatsApp messages.</div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Core Pillars -->
<section class="section-padding section-dark">
  <div class="container">
    
    <div class="section-header section-header--center">
      <span class="section-subtitle">Our Commitments</span>
      <h2 class="section-title">The Four Pillars of Midhila Voyages</h2>
      <p class="lead-text">Principles that guide every itinerary we plan and every guest we welcome.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:28px;">
      
      <div class="glass-card--dark card-3d glass-specular" style="padding:36px 28px;border-radius:var(--radius-lg);">
        <div style="font-size:2rem;color:var(--color-lavender);margin-bottom:16px;">✦</div>
        <h3 style="font-size:1.25rem;color:#FFF;margin-bottom:12px;">Uncompromising Integrity</h3>
        <p style="font-size:0.9rem;color:#CBD5E0;line-height:1.65;">We quote clear, honest prices. What is included and excluded is written unambiguously in your proposal before you pay a single rupee.</p>
      </div>

      <div class="glass-card--dark card-3d glass-specular" style="padding:36px 28px;border-radius:var(--radius-lg);">
        <div style="font-size:2rem;color:var(--color-periwinkle);margin-bottom:16px;">✦</div>
        <h3 style="font-size:1.25rem;color:#FFF;margin-bottom:12px;">Human-Centric Planning</h3>
        <p style="font-size:0.9rem;color:#CBD5E0;line-height:1.65;">You will never be handed off to an impersonal automated chatbot for trip design. A knowledgeable travel specialist listens to your wishes.</p>
      </div>

      <div class="glass-card--dark card-3d glass-specular" style="padding:36px 28px;border-radius:var(--radius-lg);">
        <div style="font-size:2rem;color:var(--color-lavender);margin-bottom:16px;">✦</div>
        <h3 style="font-size:1.25rem;color:#FFF;margin-bottom:12px;">Reverent Pilgrimages</h3>
        <p style="font-size:0.9rem;color:#CBD5E0;line-height:1.65;">We treat religious yatras with the devotion they deserve. Senior-friendly vehicles, pure vegetarian dining choices, and respectful pacing.</p>
      </div>

      <div class="glass-card--dark card-3d glass-specular" style="padding:36px 28px;border-radius:var(--radius-lg);">
        <div style="font-size:2rem;color:var(--color-icy-blue);margin-bottom:16px;">✦</div>
        <h3 style="font-size:1.25rem;color:#FFF;margin-bottom:12px;">Vetted Network</h3>
        <p style="font-size:0.9rem;color:#CBD5E0;line-height:1.65;">We work exclusively with registered hotel partners, licensed houseboats, and professional chauffeurs who prioritize passenger safety.</p>
      </div>

    </div>
  </div>
</section>

<!-- Call to Action Banner -->
<section class="section-padding" style="background-color:var(--color-ivory);text-align:center;">
  <div class="container container-narrow">
    <span class="section-subtitle">Let's Connect</span>
    <h2 class="section-title">Ready to Design Your Next Expedition?</h2>
    <p class="lead-text" style="margin-bottom:32px;">Whether you have a specific date in mind or simply want to explore possibilities, our team is here for you.</p>
    <div style="display:flex;justify-content:center;gap:18px;flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>/enquiry.php" class="btn btn-primary btn-lg">Plan Your Journey</a>
      <a href="<?= e(build_whatsapp_url($agencyWhatsApp, "Hello MIDHILA VOYAGES, I would like to learn more about your services.")) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-teal btn-lg">Chat on WhatsApp</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
