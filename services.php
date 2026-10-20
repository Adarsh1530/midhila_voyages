<?php
/**
 * MIDHILA VOYAGES — Travel Services & Concierge
 * Pure White Editorial Hospitality Canvas
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Travel Services & Concierge | Midhila Voyages';
$pageDescription = 'Discover comprehensive travel management services by Midhila Voyages: customized holidays, pilgrimage coordination, ticketing, stays, and 24/7 chauffeured logistics.';

$agencyWhatsApp = get_setting('whatsapp_number', '+918075021427');
$waServiceUrl = build_whatsapp_url($agencyWhatsApp, "Hello MIDHILA VOYAGES, I would like to inquire about your travel services.");

require_once __DIR__ . '/includes/header.php';
?>

<!-- Services Editorial Hero (Pure White with Subtle 35% Photo Layer) -->
<section style="position:relative;background:#FFFFFF;padding-top:calc(var(--header-height) + 50px);padding-bottom:50px;overflow:hidden;border-bottom:1px solid var(--color-border);">
  <div class="cinematic-bg-layer cinematic-bg-mask-radial" aria-hidden="true">
    <img src="https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=1920&q=80" 
         alt="" 
         class="cinematic-bg-layer__img" 
         style="opacity:0.25;">
  </div>

  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:820px;">
    <div class="chapter-badge" style="justify-content:center;">
      <span class="chapter-number">Concierge Capabilities</span>
      <span class="chapter-coordinates">Bespoke Hospitality</span>
    </div>
    <h1 class="editorial-headline-large" style="margin-bottom:16px;">
      Seamless Travel Curation
    </h1>
    <p class="editorial-subtext" style="margin:0 auto 28px;">
      From private chauffeured road transit to sacred darshan planning and luxury boutique stays, our concierge manages every detail.
    </p>
    <a href="<?= e($waServiceUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-solid">
      <span>Consult with a Travel Specialist</span>
    </a>
  </div>
</section>

<!-- 6 Dedicated Services Grid (Pure White Cards) -->
<section class="section-padding" style="background:#FFFFFF;">
  <div class="container">
    
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(360px, 1fr));gap:32px;">
      
      <!-- Service 1 -->
      <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:36px;box-shadow:var(--shadow-card);">
        <div style="font-size:2rem;color:var(--color-gold-deep);margin-bottom:16px;">🗺️</div>
        <h3 style="font-size:1.35rem;margin-bottom:12px;">Bespoke Domestic Holidays</h3>
        <p style="font-size:0.92rem;line-height:1.75;color:var(--color-text-body);margin-bottom:20px;">
          Tailor-made itineraries across Kerala backwaters, Himachal hill sanctuaries, Rajasthan royal heritage corridors, and pristine Andaman beaches.
        </p>
        <a href="<?= BASE_URL ?>/packages.php?category=family" class="link-editorial-arrow">
          <span>Explore Family Itineraries &rarr;</span>
        </a>
      </div>

      <!-- Service 2 -->
      <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:36px;box-shadow:var(--shadow-card);">
        <div style="font-size:2rem;color:var(--color-gold-deep);margin-bottom:16px;">🛕</div>
        <h3 style="font-size:1.35rem;margin-bottom:12px;">Sacred Vedic Pilgrimage Yatras</h3>
        <p style="font-size:0.92rem;line-height:1.75;color:var(--color-text-body);margin-bottom:20px;">
          Comprehensive group and family yatras to Rameswaram, Madurai, Varanasi, Ayodhya, and Kashi with senior-friendly pacing and verified accommodations.
        </p>
        <a href="<?= BASE_URL ?>/pilgrimage.php" class="link-editorial-arrow">
          <span>View Pilgrimage Circuit &rarr;</span>
        </a>
      </div>

      <!-- Service 3 -->
      <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:36px;box-shadow:var(--shadow-card);">
        <div style="font-size:2rem;color:var(--color-gold-deep);margin-bottom:16px;">🚗</div>
        <h3 style="font-size:1.35rem;margin-bottom:12px;">Chauffeured Private Transport</h3>
        <p style="font-size:0.92rem;line-height:1.75;color:var(--color-text-body);margin-bottom:20px;">
          Modern sanitized sedans, luxury SUVs, and tempo travellers with courteous, seasoned regional drivers who understand punctuality and scenic routing.
        </p>
        <a href="<?= BASE_URL ?>/enquiry.php" class="link-editorial-arrow">
          <span>Inquire Transport Hire &rarr;</span>
        </a>
      </div>

      <!-- Service 4 -->
      <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:36px;box-shadow:var(--shadow-card);">
        <div style="font-size:2rem;color:var(--color-gold-deep);margin-bottom:16px;">🏨</div>
        <h3 style="font-size:1.35rem;margin-bottom:12px;">Curated Accommodations</h3>
        <p style="font-size:0.92rem;line-height:1.75;color:var(--color-text-body);margin-bottom:20px;">
          Direct contracted rates with boutique plantations, luxury heritage palaces, peaceful ashram suites, and trusted beachside resorts.
        </p>
        <a href="<?= BASE_URL ?>/enquiry.php" class="link-editorial-arrow">
          <span>Request Hotel Curation &rarr;</span>
        </a>
      </div>

      <!-- Service 5 -->
      <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:36px;box-shadow:var(--shadow-card);">
        <div style="font-size:2rem;color:var(--color-gold-deep);margin-bottom:16px;">✈️</div>
        <h3 style="font-size:1.35rem;margin-bottom:12px;">Flight &amp; Rail Coordination</h3>
        <p style="font-size:0.92rem;line-height:1.75;color:var(--color-text-body);margin-bottom:20px;">
          Hassle-free domestic and international flight ticketing, seat selection assistance, train connections, and baggage handling advice.
        </p>
        <a href="<?= BASE_URL ?>/contact.php" class="link-editorial-arrow">
          <span>Flight Inquiries &rarr;</span>
        </a>
      </div>

      <!-- Service 6 -->
      <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:36px;box-shadow:var(--shadow-card);">
        <div style="font-size:2rem;color:var(--color-gold-deep);margin-bottom:16px;">👥</div>
        <h3 style="font-size:1.35rem;margin-bottom:12px;">Group Expeditions &amp; Corporate</h3>
        <p style="font-size:0.92rem;line-height:1.75;color:var(--color-text-body);margin-bottom:20px;">
          End-to-end management for extended families, temple societies, alumni cohorts, and corporate retreat groups with dedicated tour escorts.
        </p>
        <a href="<?= e($waServiceUrl) ?>" target="_blank" rel="noopener noreferrer" class="link-editorial-arrow">
          <span>Discuss Group Travel &rarr;</span>
        </a>
      </div>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
