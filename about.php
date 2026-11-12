<?php
/**
 * MIDHILA VOYAGES — About The Brand & Philosophy
 * Pure White Editorial Hospitality Canvas
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'About Midhila Voyages | The Art of Paced Travel & Devotion';
$pageDescription = 'Learn about Midhila Voyages, our origins, our core philosophy of quiet luxury, sacred reverence, and transparent travel concierge services across India.';

$agencyWhatsApp = get_setting('whatsapp_number', '+918075021427');

require_once __DIR__ . '/includes/header.php';
?>

<!-- About Editorial Hero (Pure White with Subtle 35% Photo Layer) -->
<section style="position:relative;background:#FFFFFF;padding-top:calc(var(--header-height) + 50px);padding-bottom:50px;overflow:hidden;border-bottom:1px solid var(--color-border);">
  <div class="cinematic-bg-layer cinematic-bg-mask-radial" aria-hidden="true">
    <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1920&q=80" 
         alt="" 
         class="cinematic-bg-layer__img" 
         style="opacity:0.25;">
  </div>

  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:820px;">
    <h1 class="editorial-headline-large" style="margin-bottom:16px;">
      <?= e(get_setting('about_hero_title', 'The Midhila Voyages Philosophy')) ?>
    </h1>
    <p class="editorial-subtext" style="margin:0 auto;">
      <?= e(get_setting('about_hero_subtitle', 'Born from a profound respect for India’s timeless landscapes and sacred heritage, we curate journeys defined by grace, stillness, and human warmth.')) ?>
    </p>
  </div>
</section>

<!-- The Narrative Chapter (Pure White Surface) -->
<section class="section-padding" style="background:#FFFFFF;">
  <div class="container container-narrow">
    
    <div style="margin-bottom:60px;">
      <h2 style="font-size:clamp(1.8rem, 3.2vw, 2.6rem);margin-bottom:20px;line-height:1.25;">
        <span style="font-family:var(--font-editorial);font-style:italic;font-weight:400;color:var(--color-gold-deep);">
          <?= e(get_setting('about_quote_headline', '“We are not merely arranging transit. We are guiding you into moments that linger for a lifetime.”')) ?>
        </span>
      </h2>
      <p style="font-size:1.05rem;line-height:1.85;color:var(--color-text-body);margin-bottom:20px;">
        <?= nl2br(e(get_setting('about_story_p1', 'MIDHILA VOYAGES began with a fundamental realization: modern travel had become hurried, transactional, and exhausting. Pilgrims were left navigating overwhelming temple crowds with little care for elder comfort, while families found themselves trapped in cookie-cutter packages devoid of authentic connection.'))) ?>
      </p>
      <p style="font-size:1.05rem;line-height:1.85;color:var(--color-text-body);">
        <?= nl2br(e(get_setting('about_story_p2', 'We chose a different path. Rooted in the rich cultural soil of Kerala, our agency designs every journey like an editorial narrative: unhurried, transparent, respectful of local traditions, and supported by a 24/7 dedicated private chauffeur network.'))) ?>
      </p>
    </div>

    <!-- 4 Foundational Pillars -->
    <div class="responsive-2col" style="gap:28px;margin-bottom:60px;">
      
      <div style="background:#FAFAFA;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:32px;">
        <span style="font-size:1.4rem;color:var(--color-gold-deep);margin-bottom:10px;display:block;">01</span>
        <h3 style="font-size:1.2rem;margin-bottom:10px;">The Dignity of Sacred Yatras</h3>
        <p style="font-size:0.9rem;color:var(--color-text-muted);line-height:1.7;">
          Pilgrimages demand reverence, patience, and impeccable elder care. We coordinate temple timings, close-proximity accommodations, and calm pacing so darshans remain pure and peaceful.
        </p>
      </div>

      <div style="background:#FAFAFA;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:32px;">
        <span style="font-size:1.4rem;color:var(--color-gold-deep);margin-bottom:10px;display:block;">02</span>
        <h3 style="font-size:1.2rem;margin-bottom:10px;">Bespoke Personalization</h3>
        <p style="font-size:0.9rem;color:var(--color-text-muted);line-height:1.7;">
          No two voyagers share identical rhythms. Whether seeking a secluded backwater cruise or a high-altitude mountain retreat, we tailor every day around your personal travel desires.
        </p>
      </div>

      <div style="background:#FAFAFA;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:32px;">
        <span style="font-size:1.4rem;color:var(--color-gold-deep);margin-bottom:10px;display:block;">03</span>
        <h3 style="font-size:1.2rem;margin-bottom:10px;">Zero-Surprise Financials</h3>
        <p style="font-size:0.9rem;color:var(--color-text-muted);line-height:1.7;">
          Transparent itemized vouchers, clear inclusions and exclusions, and zero bait-and-switch fees. You know precisely what is arranged before stepping out your door.
        </p>
      </div>

      <div style="background:#FAFAFA;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:32px;">
        <span style="font-size:1.4rem;color:var(--color-gold-deep);margin-bottom:10px;display:block;">04</span>
        <h3 style="font-size:1.2rem;margin-bottom:10px;">24/7 Human Concierge</h3>
        <p style="font-size:0.9rem;color:var(--color-text-muted);line-height:1.7;">
          Real, responsive coordinators available throughout your journey. From weather updates to route adjustments, you are supported at every turn.
        </p>
      </div>

    </div>

    <!-- Agency Hotline Reassurance -->
    <div style="text-align:center;padding:40px;border:1px solid var(--color-border);border-radius:var(--radius-md);background:#FFFFFF;box-shadow:var(--shadow-card);">
      <h3 style="font-size:1.4rem;margin-bottom:12px;">Speak Directly with Our Travel Curators</h3>
      <p style="font-size:0.95rem;color:var(--color-text-muted);margin-bottom:20px;">
        Reach our headquarters in Kerala for customized route advice and tour reservations.
      </p>
      <div style="display:flex;justify-content:center;gap:16px;flex-wrap:wrap;">
        <a href="<?= e(build_whatsapp_url($agencyWhatsApp, "Hello MIDHILA VOYAGES, I would like to consult with a travel curator.")) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-solid">
          <span>WhatsApp Specialist</span>
        </a>
        <a href="<?= BASE_URL ?>/contact.php" class="btn-cinematic-primary">
          <span>Contact Our Office</span>
        </a>
      </div>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
