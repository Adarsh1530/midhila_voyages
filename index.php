<?php
/**
 * MIDHILA VOYAGES — Homepage
 * Inspired by Brand Motion's "Travelled by Scroll" (VAARĀ — A Palace Resort)
 * Pure-White Canvas with Layered 30-50% Subtle Photography & 100% Inspection Packages
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'MIDHILA VOYAGES | Travelled by Scroll • Sacred Trails & Sublime Horizons';
$pageDescription = 'Experience bespoke travel planning, idyllic tropical escapes, and sacred pilgrimage circuits with Midhila Voyages. Personalized itineraries, transparent service, and dedicated support.';

$db = getDB();

// 1. Fetch Featured Destinations (Top 6)
$destStmt = $db->query("
    SELECT * FROM `destinations` 
    WHERE `is_published` = 1 
    ORDER BY `is_featured` DESC, `display_order` ASC 
    LIMIT 6
");
$featuredDestinations = $destStmt->fetchAll();

// 1b. Fetch Kerala Tourism Categories
$tourismCategories = [];
try {
    $tcStmt = $db->query("SELECT * FROM `tourism_categories` WHERE `is_active` = 1 ORDER BY `display_order` ASC");
    $tourismCategories = $tcStmt->fetchAll();
} catch (Exception $e) {}

// 2. Fetch Package Categories
$catStmt = $db->query("
    SELECT * FROM `package_categories` 
    WHERE `is_active` = 1 
    ORDER BY `display_order` ASC
");
$categories = $catStmt->fetchAll();

// 3. Fetch Featured Tour Packages
$pkgStmt = $db->query("
    SELECT p.*, d.name AS destination_name, d.name_ml AS destination_name_ml, d.name_ta AS destination_name_ta, d.region_state, 
           c.name AS category_name, c.name_ml AS category_name_ml, c.name_ta AS category_name_ta, c.slug AS category_slug
    FROM `packages` p
    JOIN `destinations` d ON p.destination_id = d.id
    JOIN `package_categories` c ON p.category_id = c.id
    WHERE p.is_published = 1
    ORDER BY p.is_featured DESC, p.display_order ASC
    LIMIT 6
");
$packages = $pkgStmt->fetchAll();

// 4. Fetch Dedicated Pilgrimage Packages
$pilgrimStmt = $db->query("
    SELECT p.*, d.name AS destination_name, d.name_ml AS destination_name_ml, d.name_ta AS destination_name_ta, d.region_state, 
           c.name AS category_name, c.name_ml AS category_name_ml, c.name_ta AS category_name_ta, c.slug AS category_slug
    FROM `packages` p
    JOIN `destinations` d ON p.destination_id = d.id
    JOIN `package_categories` c ON p.category_id = c.id
    WHERE p.is_published = 1 AND p.is_pilgrimage = 1
    ORDER BY p.display_order ASC
    LIMIT 3
");
$pilgrimagePackages = $pilgrimStmt->fetchAll();

// 5. Fetch Gallery Preview
$galleryStmt = $db->query("
    SELECT * FROM `gallery_images`
    WHERE `is_active` = 1
    ORDER BY `display_order` ASC
    LIMIT 6
");
$galleryItems = $galleryStmt->fetchAll();

// 6. Fetch FAQs
$faqStmt = $db->query("
    SELECT * FROM `faqs`
    WHERE `is_published` = 1
    ORDER BY `display_order` ASC
    LIMIT 6
");
$faqs = $faqStmt->fetchAll();

// 7. Fetch Verified Testimonials
$testStmt = $db->query("
    SELECT * FROM `testimonials`
    WHERE `is_verified` = 1 AND `is_featured` = 1
    ORDER BY `display_order` ASC
    LIMIT 3
");
$testimonials = $testStmt->fetchAll();

$agencyWhatsApp = get_setting('whatsapp_number', '+918075021427');
$agencyPhone = get_setting('primary_phone', '+91 8075021427');

require_once __DIR__ . '/includes/header.php';
?>

<!-- =====================================================================
     CHAPTER I: THE DEPARTURE (CINEMATIC HERO ON PURE WHITE)
     ===================================================================== -->
<section class="section-hero" style="position:relative;min-height:92vh;display:flex;align-items:center;padding-top:calc(var(--header-height) + 40px);padding-bottom:80px;background:#FFFFFF;overflow:hidden;">
  
  <!-- Subtle Background Destination Layer (35% Opacity with Soft White Radial Mask) -->
  <div class="cinematic-bg-layer cinematic-bg-mask-radial" aria-hidden="true">
    <img src="<?= e(get_setting('hero_bg_image', 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1920&q=80')) ?>" 
         alt="" 
         class="cinematic-bg-layer__img" 
         style="opacity:0.32;">
  </div>

  <!-- Delicate Geographical Texture Grid Watermark -->
  <div class="geo-texture-grid" aria-hidden="true"></div>

  <!-- 3D Interactive Travel Scene Canvas (Three.js Golden Coordinate Globe) -->
  <div class="travel-scene-wrapper" aria-hidden="true">
    <canvas id="travelSceneCanvas"></canvas>
    
    <!-- Fallback CSS/SVG 3D Wireframe Rings -->
    <div class="fallback-globe">
      <div class="fallback-globe__ring fallback-globe__ring--1"></div>
      <div class="fallback-globe__ring fallback-globe__ring--2"></div>
    </div>
  </div>

  <!-- Floating Photographic Memoirs (Natural Parallax Floating Depth) -->
  <div class="floating-memoirs" aria-hidden="true">
    <!-- Memoir 1: Kerala Backwaters -->
    <div class="floating-memoir floating-memoir--1">
      <img src="https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=600&q=80" 
           alt="Kerala Tranquil Waters" 
           loading="lazy">
    </div>

    <!-- Memoir 2: Sacred Temple Gopuram -->
    <div class="floating-memoir floating-memoir--2">
      <img src="https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=600&q=80" 
           alt="Sacred Architecture" 
           loading="lazy">
    </div>

    <!-- Memoir 3: Varanasi Dawn Ghat -->
    <div class="floating-memoir floating-memoir--3">
      <img src="https://images.unsplash.com/photo-1561359313-0639aad49ca6?auto=format&fit=crop&w=500&q=80" 
           alt="Ganga Sunrise" 
           loading="lazy">
    </div>
  </div>

  <!-- Hero Content Layer -->
  <div class="container" style="position:relative;z-index:3;">
    <div style="max-width:760px;">
      
      <!-- Main Display Headline -->
      <h1 class="editorial-headline-hero">
        <?= e(get_setting('hero_headline_1', 'Travelled by Scroll.')) ?><br>
        <span style="font-family:var(--font-editorial);font-style:italic;font-weight:400;color:var(--color-gold-deep);">
          <?= e(get_setting('hero_headline_2', 'Unfolding Sacred & Sublime Horizons.')) ?>
        </span>
      </h1>

      <!-- Editorial Subtext -->
      <p class="editorial-subtext">
        <?= e(get_setting('hero_subtext', 'Where bespoke travel is an art of quiet luxury, reverence, and unhurried discovery. Handcrafted journeys across India’s most storied sanctuaries.')) ?>
      </p>

    </div>
  </div>

</section>

<div class="section-divider-hairline"></div>

<!-- =====================================================================
     CHAPTER II: GEOGRAPHIC SANCTUARIES (DESTINATIONS SHOWCASE)
     ===================================================================== -->
<section class="section-padding" id="destinations-showcase" style="position:relative;">
  
  <!-- Subtle Destination Layer at 30% Opacity -->
  <div class="cinematic-bg-layer cinematic-bg-mask-vertical" aria-hidden="true">
    <img src="https://images.unsplash.com/photo-1548013146-72479768bada?auto=format&fit=crop&w=1920&q=80" 
         alt="" 
         class="cinematic-bg-layer__img" 
         style="opacity:0.25;">
  </div>

  <div class="container">
    
    <!-- Destinations Header -->
    <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:24px;margin-bottom:60px;">
      <div>
        <h2 class="editorial-headline-large">
          <?= e(get_setting('dest_heading_1', 'Six Sacred Lands.')) ?><br>
          <span style="font-family:var(--font-editorial);font-style:italic;font-weight:400;color:var(--color-text-secondary);">
            <?= e(get_setting('dest_heading_2', 'Crafted for the Discerning Explorer.')) ?>
          </span>
        </h2>
      </div>

      <div>
        <a href="<?= BASE_URL ?>/destinations.php" class="link-editorial-arrow">
          <span><?= e(get_setting('dest_link_text', 'View All Horizons')) ?></span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </a>
      </div>
    </div>

    <!-- Asymmetric Showcase Grid (Pure White Cards, Full 100% Photos) -->
    <div class="dest-chapter-grid">
      <?php if (!empty($featuredDestinations)): ?>
        <?php foreach ($featuredDestinations as $idx => $dest): 
          $colClass = ($idx === 0 || $idx === 3) ? 'dest-col-7' : 'dest-col-5';
          $destImg = !empty($dest['featured_image']) ? $dest['featured_image'] : (ASSETS_URL . '/images/placeholder.svg');
          $destSlug = $dest['slug'] ?? ('destination-' . $dest['id']);
        ?>
          <div class="dest-showcase-card <?= $colClass ?> reveal-fade-up stagger-<?= ($idx % 3) + 1 ?>">
            <div class="dest-showcase-card__img-wrap" style="height:320px;">
              <a href="<?= BASE_URL ?>/destination.php?slug=<?= urlencode($destSlug) ?>" aria-label="Explore <?= e($dest['name']) ?>">
                <img src="<?= e($destImg) ?>" 
                     alt="<?= e($dest['name']) ?>" 
                     class="dest-showcase-card__img" 
                     loading="lazy" 
                     width="640" 
                     height="320">
              </a>
            </div>
            <div class="dest-showcase-card__content">
              <div class="dest-coordinates">
                <?= e($dest['region_state'] ?? 'India') ?> &bull; <?= e(__t('curated_circuit', 'Curated Circuit')) ?>
              </div>
              <h3 class="dest-title">
                <a href="<?= BASE_URL ?>/destination.php?slug=<?= urlencode($destSlug) ?>">
                  <?= e(loc($dest, 'name')) ?>
                </a>
              </h3>
              <p style="font-size:0.9rem;color:var(--color-text-muted);line-height:1.65;margin-bottom:16px;">
                <?= e(truncate_words(loc($dest, 'description'), 18)) ?>
              </p>
              <a href="<?= BASE_URL ?>/destination.php?slug=<?= urlencode($destSlug) ?>" class="link-editorial-arrow">
                <span><?= e(__t('discover_itinerary', 'Discover Itinerary')) ?></span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div>
</section>

<div class="section-divider-hairline"></div>

<!-- =====================================================================
     CHAPTER II-B: GOD'S OWN COUNTRY — DEDICATED KERALA TOURISM MODULE
     ===================================================================== -->
<section class="section-padding" id="kerala-tourism-showcase" style="position:relative;background:#FFFFFF;">
  <div class="container">
    
    <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:24px;margin-bottom:48px;">
      <div>
        <div style="display:inline-flex;align-items:center;gap:8px;padding:6px 14px;border-radius:20px;background:#F0FDF4;border:1px solid #BBF7D0;color:#15803D;font-size:0.8rem;font-weight:700;margin-bottom:14px;text-transform:uppercase;letter-spacing:0.05em;">
          <span>🌴 <?= e(__t('nav_kerala_tourism', 'Dedicated Kerala Tourism')) ?></span>
        </div>
        <h2 class="editorial-headline-large" style="margin-bottom:8px;">
          <?= e(__t('kerala_hero_title', 'God\'s Own Country • Tailored Experiences')) ?><br>
          <span style="font-family:var(--font-editorial);font-style:italic;font-weight:400;color:var(--color-gold-deep);">
            5 Signature Kerala Tourism Disciplines
          </span>
        </h2>
        <p style="font-size:0.95rem;color:var(--color-text-muted);max-width:640px;line-height:1.6;">
          <?= e(__t('kerala_hero_sub', 'Discover Kerala through our specialized tourism categories with curated destinations, verified boutique stays, private chauffeurs, and customized itineraries.')) ?>
        </p>
      </div>

      <div>
        <a href="<?= BASE_URL ?>/kerala-tourism.php" class="btn-cinematic-primary">
          <span><?= e(__t('explore_all_packages', 'Explore All Kerala Tours')) ?></span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </a>
      </div>
    </div>

    <!-- 5 Kerala Categories Grid -->
    <div class="kerala-disciplines-grid">
      <?php if (!empty($tourismCategories)): ?>
        <?php foreach ($tourismCategories as $cat): 
          $catName = loc($cat, 'name', $cat['name_en'] ?? '');
          $catTag = loc($cat, 'tagline', $cat['tagline_en'] ?? '');
          $catDesc = loc($cat, 'description', $cat['description_en'] ?? '');
          $catImg = !empty($cat['featured_image']) ? $cat['featured_image'] : (ASSETS_URL . '/images/placeholder.svg');
        ?>
          <div class="dest-showcase-card" style="display:flex;flex-direction:column;border:1px solid #E2E8F0;border-radius:14px;overflow:hidden;background:#FFFFFF;box-shadow:0 2px 10px rgba(15,23,42,0.04);transition:transform 0.25s, box-shadow 0.25s;">
            <div style="position:relative;height:210px;overflow:hidden;background:#0F172A;">
              <a href="<?= BASE_URL ?>/kerala-tourism.php?category=<?= urlencode($cat['slug']) ?>">
                <img src="<?= e($catImg) ?>" alt="<?= e($catName) ?>" style="width:100%;height:100%;object-fit:cover;transition:transform 0.5s ease;" loading="lazy" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
              </a>
              <span style="position:absolute;top:14px;left:14px;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);color:#FFFFFF;padding:4px 10px;border-radius:6px;font-size:0.75rem;font-weight:700;letter-spacing:0.04em;">
                <?= e($cat['slug'] === 'pilgrimage' ? 'TEMPLE & SACRED' : ($cat['slug'] === 'hill-stations' ? 'MIST & MOUNTAINS' : ($cat['slug'] === 'backwaters' ? 'HOUSEBOATS & LAGOONS' : ($cat['slug'] === 'beaches' ? 'COASTAL RETREATS' : 'REJUVENATION')))) ?>
              </span>
            </div>
            <div style="padding:22px;display:flex;flex-direction:column;flex-grow:1;justify-content:space-between;">
              <div>
                <h3 style="font-size:1.15rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">
                  <a href="<?= BASE_URL ?>/kerala-tourism.php?category=<?= urlencode($cat['slug']) ?>" style="color:inherit;text-decoration:none;">
                    <?= e($catName) ?>
                  </a>
                </h3>
                <div style="font-size:0.8rem;color:var(--color-gold-deep);font-weight:600;margin-bottom:10px;">
                  <?= e($catTag) ?>
                </div>
                <p style="font-size:0.86rem;color:var(--color-text-muted);line-height:1.55;margin-bottom:16px;">
                  <?= e(truncate_words($catDesc, 16)) ?>
                </p>
              </div>
              <div style="padding-top:14px;border-top:1px solid #F1F5F9;display:flex;justify-content:space-between;align-items:center;">
                <a href="<?= BASE_URL ?>/kerala-tourism.php?category=<?= urlencode($cat['slug']) ?>" class="link-editorial-arrow" style="font-size:0.84rem;font-weight:700;">
                  <span><?= e(__t('btn_view_packages', 'View Packages')) ?></span>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- 3 Quick Access Interactive Service Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:20px;">
      
      <!-- Card 1: Destination Accommodation -->
      <div style="background:#F8FAFC;border:1.5px solid #E2E8F0;border-radius:16px;padding:28px;display:flex;flex-direction:column;justify-content:space-between;transition:border-color 0.2s;">
        <div>
          <div style="width:48px;height:48px;border-radius:12px;background:#EFF6FF;color:#2563EB;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
          </div>
          <h3 style="font-size:1.2rem;font-weight:700;color:var(--color-navy);margin-bottom:8px;">
            <?= e(__t('nav_accommodation', 'Accommodation Booking')) ?>
          </h3>
          <p style="font-size:0.88rem;color:var(--color-text-muted);line-height:1.6;margin-bottom:20px;">
            Filter hotels, tea plantation resorts, beach villas, and houseboats by destination with custom room views, dietary options, and facilities.
          </p>
        </div>
        <div>
          <a href="<?= BASE_URL ?>/accommodation.php" class="btn-cinematic-primary" style="display:inline-flex;width:auto;">
            <span>Browse Accommodations &rarr;</span>
          </a>
        </div>
      </div>

      <!-- Card 2: Custom Tour Planner -->
      <div style="background:#FFFDF5;border:1.5px solid #FEF08A;border-radius:16px;padding:28px;display:flex;flex-direction:column;justify-content:space-between;">
        <div>
          <div style="width:48px;height:48px;border-radius:12px;background:#FEF3C7;color:#D97706;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
          </div>
          <h3 style="font-size:1.2rem;font-weight:700;color:var(--color-navy);margin-bottom:8px;">
            <?= e(__t('nav_custom_tour', 'Customised Tour Planner')) ?>
          </h3>
          <p style="font-size:0.88rem;color:var(--color-text-muted);line-height:1.6;margin-bottom:20px;">
            Specify your route, transport preference (Sedan, Innova, Urbania), private chauffeur, days, dietary choices, and receive a bespoke itinerary proposal.
          </p>
        </div>
        <div>
          <a href="<?= BASE_URL ?>/custom-tour.php" class="btn-cinematic-primary" style="display:inline-flex;width:auto;background:#C5A059;border-color:#C5A059;">
            <span>Plan Custom Trip &rarr;</span>
          </a>
        </div>
      </div>

      <!-- Card 3: Corporate & Business Events -->
      <div style="background:#F8FAFC;border:1.5px solid #E2E8F0;border-radius:16px;padding:28px;display:flex;flex-direction:column;justify-content:space-between;">
        <div>
          <div style="width:48px;height:48px;border-radius:12px;background:#F3E8FF;color:#7E22CE;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
          </div>
          <h3 style="font-size:1.2rem;font-weight:700;color:var(--color-navy);margin-bottom:8px;">
            <?= e(__t('nav_corporate', 'Corporate & Business Events')) ?>
          </h3>
          <p style="font-size:0.88rem;color:var(--color-text-muted);line-height:1.6;margin-bottom:20px;">
            Reserve conference halls, seminar auditoriums, and convention centres with theater/roundtable seating, AV equipment, catering, and guest accommodation.
          </p>
        </div>
        <div>
          <a href="<?= BASE_URL ?>/corporate-events.php" class="btn-cinematic-primary" style="display:inline-flex;width:auto;">
            <span>Corporate Venues &rarr;</span>
          </a>
        </div>
      </div>

    </div>

  </div>
</section>

<div class="section-divider-hairline"></div>

<!-- =====================================================================
     CHAPTER III: SIGNATURE JOURNEYS (TOUR PACKAGES CATALOG)
     ===================================================================== -->
<section class="section-padding" id="signature-journeys" style="position:relative;background:#FFFFFF;">
  
  <div class="container">
    
    <!-- Portfolios Header -->
    <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:24px;margin-bottom:50px;">
      <div>
        <h2 class="editorial-headline-large">
          <?= e(get_setting('pkg_heading_1', 'Handcrafted Journeys.')) ?><br>
          <span style="font-family:var(--font-editorial);font-style:italic;font-weight:400;color:var(--color-text-secondary);">
            <?= e(get_setting('pkg_heading_2', 'Every detail verified with private chauffeur & verified stays.')) ?>
          </span>
        </h2>
      </div>

      <div>
        <a href="<?= BASE_URL ?>/packages.php" class="btn-cinematic-primary">
          <span><?= e(get_setting('pkg_btn_text', 'Complete Catalog')) ?></span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </a>
      </div>
    </div>

    <!-- Category Filter Pills -->
    <div class="filter-pills" style="margin-bottom:40px;">
      <a href="<?= BASE_URL ?>/packages.php" class="filter-pill is-active"><?= e(__t('all_itineraries', 'All Itineraries')) ?></a>
      <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $cat): ?>
          <a href="<?= BASE_URL ?>/packages.php?category=<?= urlencode($cat['slug']) ?>" class="filter-pill">
            <?= e(loc($cat, 'name')) ?>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Tour Packages Grid (100% Opacity Verified Photography) -->
    <div class="packages-grid">
      <?php if (!empty($packages)): ?>
        <?php foreach ($packages as $package): ?>
          <?php include __DIR__ . '/includes/package-card.php'; ?>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="grid-column:1/-1;padding:60px 20px;text-align:center;background:#FAFAFA;border:1px dashed var(--color-border);border-radius:var(--radius-md);">
          <p style="color:var(--color-text-muted);">New seasonal itineraries are currently being curated. Contact our concierge for immediate custom planning.</p>
        </div>
      <?php endif; ?>
    </div>

  </div>
</section>

<div class="section-divider-hairline"></div>

<!-- =====================================================================
     THE SACRED CIRCUIT (PILGRIMAGE TOURS)
     ===================================================================== -->
<section class="section-padding" style="position:relative;background:#FFFFFF;">
  
  <!-- Subtle Vedic Architecture Backdrop Layer at 30% Opacity -->
  <div class="cinematic-bg-layer cinematic-bg-mask-side" aria-hidden="true">
    <img src="https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1920&q=80" 
         alt="" 
         class="cinematic-bg-layer__img" 
         style="opacity:0.28;">
  </div>

  <div class="container">
    
    <div class="pilgrim-banner">
      <div class="pilgrim-banner__grid">
        
        <div class="pilgrim-banner__left">
          <div class="pilgrim-temple-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            <span><?= e(get_setting('pilgrim_badge', 'Sacred Vedic Circuit • Tamil Nadu & Kashi')) ?></span>
          </div>

          <h2 style="font-size:clamp(1.8rem, 3.2vw, 2.8rem);margin-bottom:16px;">
            <?= e(get_setting('pilgrim_title', 'Divine Destinations...')) ?><br>
            <span style="font-family:var(--font-editorial);font-style:italic;font-weight:400;color:var(--color-gold-deep);">
              <?= e(get_setting('pilgrim_subtitle', 'A Journey of Deep Reverence & Faith.')) ?>
            </span>
          </h2>

          <p style="font-size:0.96rem;line-height:1.75;color:var(--color-text-secondary);margin-bottom:24px;">
            <?= nl2br(e(get_setting('pilgrim_desc', 'Experience seamlessly managed temple yatras across Sri Murugan Temple Thiruchendur, Sri Ramanathaswamy Rameswaram, Dhanushkodi, Brihadeeswarar Thanjavur, and Meenakshi Amman Madurai. Verified sanitised accommodations, elderly-first ground coordination, and zero hidden costs.'))) ?>
          </p>

          <!-- Inclusion Highlights from the Official Poster -->
          <div class="pilgrim-inclusions-grid">
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="color:#059669;font-weight:700;">✓</span> Dedicated Chauffeured Transport
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="color:#059669;font-weight:700;">✓</span> Curated AC Accommodations
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="color:#059669;font-weight:700;">✓</span> Dhanushkodi Land's End Transfer
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="color:#059669;font-weight:700;">✓</span> Transparent Fixed Pricing
            </div>
          </div>

          <div style="display:flex;align-items:center;flex-wrap:wrap;gap:16px;">
            <a href="<?= BASE_URL ?>/pilgrimage.php" class="btn-cinematic-gold">
              <span><?= e(__t('btn_explore_sacred', 'View Pilgrimage Circuit')) ?></span>
            </a>
            <a href="<?= e(build_whatsapp_url($agencyWhatsApp, "Hello MIDHILA VOYAGES, I would like to reserve seats for the upcoming Pilgrim Tour.")) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-solid">
              <span><?= e(__t('btn_whatsapp_us', 'Reserve via WhatsApp')) ?></span>
            </a>
          </div>
        </div>

        <!-- Featured Price Badge & Image Frame -->
        <div class="pilgrim-banner__right">
          <div style="background:#FFFFFF;border:2px solid rgba(197, 160, 89, 0.4);border-radius:var(--radius-lg);padding:32px 24px;box-shadow:var(--shadow-card);">
            <span style="font-size:0.75rem;letter-spacing:0.2em;text-transform:uppercase;color:var(--color-gold-deep);font-weight:700;"><?= e(__t('signature_5day_circuit', 'Special Yatra Package')) ?></span>
            <div style="font-size:3rem;font-weight:800;color:var(--color-text-headline);line-height:1;margin:12px 0 6px;">
              <?= e(get_setting('pilgrim_featured_price', '₹6,500')) ?>
            </div>
            <span style="font-size:0.82rem;color:var(--color-text-muted);display:block;margin-bottom:20px;"><?= e(get_setting('pilgrim_featured_duration', 'per person • 5 Days / 4 Nights')) ?></span>
            
            <div style="background:#FAFAFA;border-radius:var(--radius-sm);padding:14px;font-size:0.82rem;color:var(--color-text-secondary);text-align:left;margin-bottom:20px;border:1px solid var(--color-border);">
              <strong>Upcoming Tour Dates:</strong> <?= e(get_setting('pilgrim_upcoming_dates', '23.12.26 to 27.12.26')) ?><br>
              <em style="color:var(--color-gold-deep);"><?= e(get_setting('pilgrim_special_note', 'Hurry, limited slots on first come, first served basis!')) ?></em>
            </div>

            <div style="font-size:0.84rem;color:var(--color-text-body);font-weight:600;">
              Hotlines: <?= e(get_setting('pilgrim_hotlines', '+91 8075021427, 9446154048')) ?>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</section>

<div class="section-divider-hairline"></div>

<!-- =====================================================================
     CHAPTER V: CONCIERGE CRAFT & PILLARS
     ===================================================================== -->
<section class="section-padding" style="position:relative;background:#FFFFFF;">
  <div class="container">
    
    <div style="text-align:center;max-width:760px;margin:0 auto 60px;">
      <h2 class="editorial-headline-large">
        <?= e(get_setting('pillars_title', 'Hospitality Defined by Stillness, Depth & Care.')) ?>
      </h2>
      <p class="editorial-subtext" style="margin:0 auto;">
        <?= e(get_setting('pillars_subtitle', 'We believe a journey should be deeply felt, never rushed.')) ?>
      </p>
    </div>

    <!-- 4 Pillars Grid -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(min(100%, 260px), 1fr));gap:32px;">
      
      <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:32px 28px;box-shadow:var(--shadow-ambient);">
        <div style="font-size:1.6rem;color:var(--color-gold-deep);margin-bottom:16px;">✦</div>
        <h3 style="font-size:1.15rem;margin-bottom:12px;"><?= e(get_setting('pillar_1_title', 'Handcrafted Routing')) ?></h3>
        <p style="font-size:0.88rem;color:var(--color-text-muted);line-height:1.7;">
          <?= e(get_setting('pillar_1_desc', 'Every route is custom paced to balance sacred exploration, cultural depth, and gentle rest.')) ?>
        </p>
      </div>

      <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:32px 28px;box-shadow:var(--shadow-ambient);">
        <div style="font-size:1.6rem;color:var(--color-gold-deep);margin-bottom:16px;">❖</div>
        <h3 style="font-size:1.15rem;margin-bottom:12px;"><?= e(get_setting('pillar_2_title', 'Verified Sanctuaries')) ?></h3>
        <p style="font-size:0.88rem;color:var(--color-text-muted);line-height:1.7;">
          <?= e(get_setting('pillar_2_desc', 'Personally vetted heritage resorts, clean temple guest houses, and scenic boutique retreats.')) ?>
        </p>
      </div>

      <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:32px 28px;box-shadow:var(--shadow-ambient);">
        <div style="font-size:1.6rem;color:var(--color-gold-deep);margin-bottom:16px;">◈</div>
        <h3 style="font-size:1.15rem;margin-bottom:12px;"><?= e(get_setting('pillar_3_title', 'Dedicated Chauffeurs')) ?></h3>
        <p style="font-size:0.88rem;color:var(--color-text-muted);line-height:1.7;">
          <?= e(get_setting('pillar_3_desc', 'Courteous, verified regional drivers with well-maintained AC fleets ensuring seamless road transit.')) ?>
        </p>
      </div>

      <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:32px 28px;box-shadow:var(--shadow-ambient);">
        <div style="font-size:1.6rem;color:var(--color-gold-deep);margin-bottom:16px;">❂</div>
        <h3 style="font-size:1.15rem;margin-bottom:12px;"><?= e(get_setting('pillar_4_title', 'Transparent Integrity')) ?></h3>
        <p style="font-size:0.88rem;color:var(--color-text-muted);line-height:1.7;">
          <?= e(get_setting('pillar_4_desc', 'Zero hidden surcharges, honest pricing, and complete written inclusions on every voucher.')) ?>
        </p>
      </div>

    </div>

  </div>
</section>

<div class="section-divider-hairline"></div>

<!-- =====================================================================
     EXPLORER MEMOIRS & TRUST
     ===================================================================== -->
<?php if (!empty($testimonials)): ?>
<section class="section-padding" style="position:relative;background:#FFFFFF;">
  <div class="container">
    
    <div style="text-align:center;max-width:700px;margin:0 auto 50px;">
      <h2 class="editorial-headline-large"><?= e(__t('testimonials_title', 'Journeys Remembered.')) ?></h2>
    </div>

    <div class="packages-grid">
      <?php foreach ($testimonials as $t): ?>
        <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:32px 28px;box-shadow:var(--shadow-card);display:flex;flex-direction:column;justify-content:space-between;">
          <div>
            <div style="color:var(--color-gold);font-size:1rem;margin-bottom:14px;">★★★★★</div>
            <p style="font-family:var(--font-editorial);font-style:italic;font-size:1.15rem;line-height:1.65;color:var(--color-text-main);margin-bottom:20px;">
              "<?= e($t['feedback'] ?? $t['comment'] ?? '') ?>"
            </p>
          </div>
          <div style="border-top:1px solid var(--color-border-hairline);padding-top:14px;display:flex;align-items:center;justify-content:space-between;">
            <div>
              <strong style="color:var(--color-text-headline);font-size:0.9rem;"><?= e($t['client_name'] ?? $t['customer_name'] ?? 'Traveller') ?></strong>
              <span style="display:block;font-size:0.75rem;color:var(--color-text-muted);"><?= e($t['location'] ?? $t['customer_location'] ?? 'India') ?></span>
            </div>
            <span style="font-size:0.72rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--color-gold-deep);font-weight:700;"><?= e(__t('verified_journey', 'Verified Journey')) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>
<div class="section-divider-hairline"></div>
<?php endif; ?>

<!-- =====================================================================
     COMMENCE YOUR JOURNEY (ENQUIRY CONCIERGE)
     ===================================================================== -->
<section class="section-padding" style="position:relative;background:#FFFFFF;">
  <div class="container container-narrow">
    
    <div class="editorial-form-panel">
      <div style="text-align:center;margin-bottom:36px;">
        <h2 style="font-size:clamp(1.8rem, 3.2vw, 2.6rem);margin-bottom:12px;"><?= e(get_setting('cta_title', 'Plan Your Bespoke Journey')) ?></h2>
        <p style="font-size:0.95rem;color:var(--color-text-secondary);max-width:540px;margin:0 auto;">
          <?= e(get_setting('cta_subtitle', 'Tell our travel specialists your preferred destinations, dates, and rhythm. We respond within hours with a tailored itinerary.')) ?>
        </p>
      </div>

      <form action="<?= BASE_URL ?>/enquiry.php" method="POST">
        <?= csrf_field() ?>

        <div class="form-row-2col">
          <div class="editorial-form-group">
            <label class="editorial-label"><?= e(__t('form_full_name', 'Your Name *')) ?></label>
            <input type="text" name="name" class="editorial-input" placeholder="e.g. Adarsh" required>
          </div>

          <div class="editorial-form-group">
            <label class="editorial-label"><?= e(__t('form_phone', 'Phone / WhatsApp Number *')) ?></label>
            <input type="tel" name="phone" class="editorial-input" placeholder="+91 98765 43210" required>
          </div>
        </div>

        <div class="form-row-2col">
          <div class="editorial-form-group">
            <label class="editorial-label"><?= e(__t('form_email', 'Email Address *')) ?></label>
            <input type="email" name="email" class="editorial-input" placeholder="you@domain.com" required>
          </div>

          <div class="editorial-form-group">
            <label class="editorial-label"><?= e(__t('form_select_destination', 'Preferred Destination')) ?></label>
            <select name="destination" class="editorial-select">
              <option value=""><?= e(__t('form_select_destination', 'Select Destination')) ?></option>
              <option value="kerala">Kerala Backwaters &amp; Munnar</option>
              <option value="tamil-nadu">Tamil Nadu Temple Circuit</option>
              <option value="varanasi">Varanasi &amp; Prayagraj</option>
              <option value="himalayas">Himalayan Valleys</option>
              <option value="lakshadweep">Lakshadweep Atolls</option>
              <option value="andaman">Andaman Islands</option>
              <option value="other">Other / Custom Route</option>
            </select>
          </div>
        </div>

        <div class="editorial-form-group">
          <label class="editorial-label"><?= e(__t('form_additional_req', 'Travel Notes / Preferences')) ?></label>
          <textarea name="message" class="editorial-textarea" rows="3" placeholder="Tell us about travelers, expected dates, and any special accommodations..."></textarea>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-top:28px;">
          <button type="submit" class="btn-cinematic-primary" style="padding:14px 36px;">
            <span><?= e(get_setting('cta_btn_text', 'Submit Travel Request')) ?></span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
          </button>

          <span style="font-size:0.8rem;color:var(--color-text-muted);">
            <?= e(__t('prefer_instant_messaging', 'Prefer instant messaging?')) ?> <a href="<?= e(build_whatsapp_url($agencyWhatsApp, "Hello MIDHILA VOYAGES, I would like to enquire about travel packages.")) ?>" target="_blank" rel="noopener noreferrer" style="color:var(--color-whatsapp);font-weight:700;"><?= e(__t('chat_on_whatsapp', 'Chat on WhatsApp')) ?> &rarr;</a>
          </span>
        </div>
      </form>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
