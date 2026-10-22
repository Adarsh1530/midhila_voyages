<?php
/**
 * MIDHILA VOYAGES — Dedicated Pilgrimage & Sacred Trails Portal
 * Pure White Editorial Hospitality Canvas
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Sacred Pilgrimage Tours | Divine Yatras & Temple Trails | Midhila Voyages';
$pageDescription = 'Experience deeply reverent, comfortable pilgrimage journeys across Rameswaram, Madurai, Thanjavur, Varanasi, and holy shrines with Midhila Voyages.';

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

$agencyWhatsApp = get_setting('whatsapp_number', '+918075021427');

require_once __DIR__ . '/includes/header.php';
?>

<!-- Pilgrimage Portal Editorial Hero (Pure White with 35% Subtle Photo Layer) -->
<section style="position:relative;background:#FFFFFF;padding-top:calc(var(--header-height) + 50px);padding-bottom:50px;overflow:hidden;border-bottom:1px solid var(--color-border);">
  <div class="cinematic-bg-layer cinematic-bg-mask-radial" aria-hidden="true">
    <img src="https://images.unsplash.com/photo-1561361513-2d000a50f0dc?auto=format&fit=crop&w=1920&q=80" 
         alt="" 
         class="cinematic-bg-layer__img" 
         style="opacity:0.25;">
  </div>

  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:820px;">
    <h1 class="editorial-headline-large" style="margin-bottom:18px;">
      <?= e(get_setting('pilgrim_hero_title', 'Journeys of Faith, Serenity & Devotion')) ?>
    </h1>
    
    <p class="editorial-subtext" style="margin:0 auto 32px;">
      <?= e(get_setting('pilgrim_hero_subtext', 'Thoughtfully planned pilgrimage trails honoring your spiritual quest. Elderly comfort, dedicated private AC transport, verified sanitized stays, and respectful unhurried pacing.')) ?>
    </p>

    <div style="display:flex;justify-content:center;gap:16px;flex-wrap:wrap;">
      <a href="#pilgrimPackages" class="btn-cinematic-gold">
        <span>Explore Sacred Circuits</span>
      </a>
      <a href="<?= e(build_whatsapp_url($agencyWhatsApp, "Hello MIDHILA VOYAGES, I would like to arrange a family/group pilgrimage tour.")) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-solid">
        <span>Group Yatra Enquiry</span>
      </a>
    </div>
  </div>
</section>

<!-- Featured Pilgrim Tour (Directly Inspired by Official Agency Poster) -->
<section style="background:#FFFFFF;padding-top:60px;padding-bottom:60px;position:relative;">
  <div class="container">
    
    <div class="pilgrim-banner">
      <div style="text-align:center;max-width:760px;margin:0 auto 36px;">
        <span style="font-size:0.75rem;letter-spacing:0.22em;text-transform:uppercase;color:var(--color-gold-deep);font-weight:700;">Signature 5-Day Circuit</span>
        <h2 style="font-size:clamp(1.8rem, 3.2vw, 2.8rem);margin-top:6px;">Pilgrim Tours &bull; Divine Destinations</h2>
        <p style="font-size:0.95rem;color:var(--color-text-secondary);margin-top:8px;">A comprehensive, beautifully organized spiritual voyage across Tamil Nadu’s most celebrated shrines.</p>
      </div>

      <!-- Places Covered Grid -->
      <div style="margin-bottom:36px;">
        <h3 style="font-size:1.1rem;text-transform:uppercase;letter-spacing:0.12em;color:var(--color-text-main);margin-bottom:16px;text-align:center;">
          Holy Places Covered on the Trail
        </h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;">
          
          <div style="background:#FAFAFA;border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:16px;text-align:center;">
            <div style="font-size:1.2rem;color:var(--color-gold-deep);margin-bottom:6px;">🛕</div>
            <strong style="display:block;font-size:0.92rem;color:var(--color-text-headline);">Sri Murugan Temple</strong>
            <span style="font-size:0.8rem;color:var(--color-text-muted);">Thiruchendur</span>
          </div>

          <div style="background:#FAFAFA;border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:16px;text-align:center;">
            <div style="font-size:1.2rem;color:var(--color-gold-deep);margin-bottom:6px;">🛕</div>
            <strong style="display:block;font-size:0.92rem;color:var(--color-text-headline);">Sri Ramanathaswamy Temple</strong>
            <span style="font-size:0.8rem;color:var(--color-text-muted);">Rameswaram (Overnight stay)</span>
          </div>

          <div style="background:#FAFAFA;border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:16px;text-align:center;">
            <div style="font-size:1.2rem;color:var(--color-gold-deep);margin-bottom:6px;">🌊</div>
            <strong style="display:block;font-size:0.92rem;color:var(--color-text-headline);">Dhanushkodi Land's End</strong>
            <span style="font-size:0.8rem;color:var(--color-text-muted);">Sacred Sangam Point</span>
          </div>

          <div style="background:#FAFAFA;border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:16px;text-align:center;">
            <div style="font-size:1.2rem;color:var(--color-gold-deep);margin-bottom:6px;">🏛</div>
            <strong style="display:block;font-size:0.92rem;color:var(--color-text-headline);">APJ Abdul Kalam Museum</strong>
            <span style="font-size:0.8rem;color:var(--color-text-muted);">National Memorial</span>
          </div>

          <div style="background:#FAFAFA;border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:16px;text-align:center;">
            <div style="font-size:1.2rem;color:var(--color-gold-deep);margin-bottom:6px;">🛕</div>
            <strong style="display:block;font-size:0.92rem;color:var(--color-text-headline);">Brihadeeswarar Temple</strong>
            <span style="font-size:0.8rem;color:var(--color-text-muted);">Thanjavur (Overnight stay)</span>
          </div>

          <div style="background:#FAFAFA;border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:16px;text-align:center;">
            <div style="font-size:1.2rem;color:var(--color-gold-deep);margin-bottom:6px;">🛕</div>
            <strong style="display:block;font-size:0.92rem;color:var(--color-text-headline);">Nataraja Temple</strong>
            <span style="font-size:0.8rem;color:var(--color-text-muted);">Chidambaram</span>
          </div>

          <div style="background:#FAFAFA;border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:16px;text-align:center;">
            <div style="font-size:1.2rem;color:var(--color-gold-deep);margin-bottom:6px;">🛕</div>
            <strong style="display:block;font-size:0.92rem;color:var(--color-text-headline);">Sri Ranganathaswamy Temple</strong>
            <span style="font-size:0.8rem;color:var(--color-text-muted);">Trichy (Overnight stay)</span>
          </div>

          <div style="background:#FAFAFA;border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:16px;text-align:center;">
            <div style="font-size:1.2rem;color:var(--color-gold-deep);margin-bottom:6px;">🛕</div>
            <strong style="display:block;font-size:0.92rem;color:var(--color-text-headline);">Meenakshi Amman Temple</strong>
            <span style="font-size:0.8rem;color:var(--color-text-muted);">Madurai (Overnight stay)</span>
          </div>

        </div>
      </div>

      <!-- Tour Tariff, Dates & Inclusions Breakdown -->
      <div class="responsive-2col" style="align-items:center;background:#FAFAFA;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:clamp(20px, 3vw, 32px);">
        <div>
          <span style="font-size:0.75rem;letter-spacing:0.14em;text-transform:uppercase;color:var(--color-gold-deep);font-weight:700;">Fixed Transparent Tariff</span>
          <div style="font-size:2.8rem;font-weight:800;color:var(--color-text-headline);line-height:1;margin:6px 0;">
            <?= e(get_setting('pilgrim_featured_price', '₹6,500')) ?> <span style="font-size:1rem;color:var(--color-text-muted);font-weight:400;">/ person</span>
          </div>
          <p style="font-size:0.88rem;color:var(--color-text-secondary);margin-bottom:16px;">
            <strong>Tour Dates:</strong> <?= e(get_setting('pilgrim_upcoming_dates', '23.12.26 to 27.12.26')) ?> &bull; <?= e(get_setting('pilgrim_featured_duration', '5 Days / 4 Nights')) ?>
          </p>
          <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <a href="<?= e(build_whatsapp_url($agencyWhatsApp, "Hello MIDHILA VOYAGES, I want to reserve slots for the 5-Day Pilgrim Tour (₹6,500).")) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-solid">
              <span>Reserve via WhatsApp</span>
            </a>
            <a href="<?= BASE_URL ?>/enquiry.php?package=pilgrim-tour" class="btn-cinematic-primary">
              <span>Book Online</span>
            </a>
          </div>
        </div>

        <div>
          <h4 style="font-size:0.95rem;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:12px;color:var(--color-text-main);">Inclusions Verified:</h4>
          <ul style="display:flex;flex-direction:column;gap:8px;font-size:0.86rem;color:var(--color-text-body);">
            <li style="display:flex;align-items:center;gap:8px;"><span style="color:#059669;font-weight:700;">✓</span> Sanitized AC Transportation throughout</li>
            <li style="display:flex;align-items:center;gap:8px;"><span style="color:#059669;font-weight:700;">✓</span> Accommodations in Rameswaram, Thanjavur, Trichy &amp; Madurai</li>
            <li style="display:flex;align-items:center;gap:8px;"><span style="color:#059669;font-weight:700;">✓</span> Rameswaram to Dhanushkodi bus arrangement</li>
            <li style="display:flex;align-items:center;gap:8px;"><span style="color:#059669;font-weight:700;">✓</span> Two breakfasts provided</li>
          </ul>
        </div>
      </div>

    </div>

  </div>
</section>

<!-- Dedicated Pilgrimage Packages Grid (100% Opacity Photography) -->
<section class="section-padding" id="pilgrimPackages" style="background:#FFFFFF;">
  <div class="container">
    
    <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:40px;flex-wrap:wrap;gap:20px;">
      <div>
        <h2 class="editorial-headline-large" style="font-size:2.2rem;">All Sacred Pilgrimage Circuits</h2>
      </div>

      <a href="<?= BASE_URL ?>/enquiry.php?category=pilgrimage" class="btn-cinematic-outline">
        <span>Request Custom Pilgrimage</span>
      </a>
    </div>

    <div class="packages-grid">
      <?php if (!empty($pilgrimPackages)): ?>
        <?php foreach ($pilgrimPackages as $package): ?>
          <?php include __DIR__ . '/includes/package-card.php'; ?>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="grid-column:1/-1;text-align:center;padding:60px 20px;border:1px dashed var(--color-border);border-radius:var(--radius-md);background:#FAFAFA;">
          <p style="color:var(--color-text-muted);">Please connect with our pilgrimage specialists on WhatsApp for upcoming yatra dates.</p>
        </div>
      <?php endif; ?>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
