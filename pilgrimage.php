<?php
/**
 * MIDHILA VOYAGES — Dedicated Pilgrimage & Sacred Trails Portal
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Sacred Pilgrimage Tours | Divine Yatras & Temple Trails | Midhila Voyages';
$pageDescription = 'Experience deeply reverent, comfortable pilgrimage journeys across Varanasi, Ayodhya, Tamil Nadu temple corridors, and Kerala shrines with Midhila Voyages.';

$db = getDB();

// Fetch dedicated pilgrimage packages
$stmt = $db->query("
    SELECT p.*, d.name AS destination_name, d.region_state, c.name AS category_name, c.slug AS category_slug
    FROM `packages` p
    JOIN `destinations` d ON p.destination_id = d.id
    JOIN `package_categories` c ON p.category_id = c.id
    WHERE p.is_published = 1 AND p.is_pilgrimage = 1
    ORDER BY p.display_order ASC
");
$pilgrimPackages = $stmt->fetchAll();

$agencyWhatsApp = get_setting('whatsapp_number', '+911234567890');

require_once __DIR__ . '/includes/header.php';
?>

<!-- Pilgrimage Portal Cinematic Hero -->
<section style="position:relative;background:radial-gradient(circle at 50% 30%, rgba(234, 88, 12, 0.22) 0%, rgba(245, 158, 11, 0.12) 40%, #06101E 85%);padding-top:calc(var(--header-height) + 50px);padding-bottom:80px;color:#FFF;overflow:hidden;border-bottom:1px solid rgba(234, 88, 12, 0.25);">
  <div style="position:absolute;inset:0;opacity:0.32;">
    <img src="https://images.unsplash.com/photo-1561361513-2d000a50f0dc?auto=format&fit=crop&w=1800&q=80" alt="Varanasi Ganga Aarti evening lamps" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
    <div style="position:absolute;inset:0;background:radial-gradient(circle at center, transparent 0%, #06101E 85%);"></div>
  </div>

  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:820px;">
    <div class="float-slow" style="display:inline-block;padding:6px 20px;border-radius:var(--radius-pill);background:rgba(234,88,12,0.18);border:1px solid rgba(234,88,12,0.45);font-size:0.8rem;text-transform:uppercase;letter-spacing:0.2em;color:#FED7AA;margin-bottom:16px;font-weight:700;">
      🕉️ Sacred Vedic Yatras &bull; Divine Sanctuaries
    </div>
    <h1 class="editorial-headline" style="color:#FFF;font-size:clamp(2.4rem, 4.5vw, 3.8rem);margin-bottom:18px;text-shadow:0 0 24px rgba(245,158,11,0.3);">
      Journeys of Faith, Serenity &amp; Devotion
    </h1>
    <p class="lead-text" style="color:#CBD5E0;margin-bottom:32px;">
      Thoughtfully planned pilgrimage trails honoring your spiritual quest. Senior-citizen comfort, dedicated private transport, proximity to holy shrines, and respectful, unhurried pacing.
    </p>

    <div style="display:flex;justify-content:center;gap:16px;flex-wrap:wrap;">
      <a href="#pilgrimPackages" class="btn btn-vibrant-sunset btn-lg">Explore Sacred Circuits</a>
      <a href="<?= e(build_whatsapp_url($agencyWhatsApp, "Hello MIDHILA VOYAGES, I would like to arrange a family/group pilgrimage tour.")) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-vibrant-gold btn-lg">
        <span>Group Yatra Enquiry</span>
      </a>
    </div>
  </div>
</section>

<!-- Sacred Circuits Featured Packages -->
<section class="section-padding" id="pilgrimPackages" style="background-color:transparent;">
  <div class="container">
    
    <div class="section-header section-header--center">
      <span class="section-subtitle">Venerated Yatras</span>
      <h2 class="section-title">Featured Pilgrimage Itineraries</h2>
      <p class="lead-text">Pre-planned devotional journeys curated with senior-friendly logistics and clean vegetarian dining.</p>
    </div>

    <div class="package-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:30px;margin-bottom:50px;">
      <?php foreach ($pilgrimPackages as $package): ?>
        <?php include __DIR__ . '/includes/package-card.php'; ?>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- Respectful Travel Philosophy -->
<section class="section-padding section-dark">
  <div class="container">
    <div style="max-width:880px;margin:0 auto;text-align:center;">
      <span class="section-subtitle">Devotional Ethics</span>
      <h2 class="section-title">Our Sacred Travel Standard</h2>
      <p class="lead-text" style="margin-bottom:40px;">
        Pilgrimage is an internal transformation. We arrange every operational element so you and your elders can focus entirely on prayer and quiet contemplation.
      </p>

      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:24px;text-align:left;">
        
        <div class="glass-card--dark card-3d glass-specular" style="padding:26px;border-radius:var(--radius-md);">
          <h4 style="font-size:1.1rem;color:var(--color-lavender);margin-bottom:8px;font-weight:700;">Elder-Friendly Pacing</h4>
          <p style="font-size:0.88rem;color:#CBD5E0;line-height:1.6;">No rushed early schedules or excessive continuous driving. Planned rest pauses and hotels close to temple darshan gates.</p>
        </div>

        <div class="glass-card--dark card-3d glass-specular" style="padding:26px;border-radius:var(--radius-md);">
          <h4 style="font-size:1.1rem;color:var(--color-periwinkle);margin-bottom:8px;font-weight:700;">Pure Vegetarian Meals</h4>
          <p style="font-size:0.88rem;color:#CBD5E0;line-height:1.6;">Arrangements at vetted Sattvic and authentic South &amp; North Indian vegetarian dining establishments.</p>
        </div>

        <div class="glass-card--dark card-3d glass-specular" style="padding:26px;border-radius:var(--radius-md);">
          <h4 style="font-size:1.1rem;color:var(--color-icy-blue);margin-bottom:8px;font-weight:700;">Darshan Protocol Support</h4>
          <p style="font-size:0.88rem;color:#CBD5E0;line-height:1.6;">Guidance regarding temple dress codes, puja timings, special queue access, and holy bath rituals.</p>
        </div>

      </div>

      <div style="margin-top:48px;">
        <a href="<?= BASE_URL ?>/enquiry.php?category=pilgrimage" class="btn btn-primary btn-lg pulse-luminous">Plan Custom Pilgrimage Yatra</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
