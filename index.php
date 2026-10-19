<?php
/**
 * MIDHILA VOYAGES — Homepage
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'MIDHILA VOYAGES | Curated Domestic, International & Pilgrimage Journeys';
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

// 2. Fetch Package Categories for Tabs
$catStmt = $db->query("
    SELECT * FROM `package_categories` 
    WHERE `is_active` = 1 
    ORDER BY `display_order` ASC
");
$categories = $catStmt->fetchAll();

// 3. Fetch Featured Tour Packages
$pkgStmt = $db->query("
    SELECT p.*, d.name AS destination_name, d.region_state, c.name AS category_name, c.slug AS category_slug
    FROM `packages` p
    JOIN `destinations` d ON p.destination_id = d.id
    JOIN `package_categories` c ON p.category_id = c.id
    WHERE p.is_published = 1
    ORDER BY p.is_featured DESC, p.display_order ASC
    LIMIT 8
");
$packages = $pkgStmt->fetchAll();

// 4. Fetch Dedicated Pilgrimage Packages
$pilgrimStmt = $db->query("
    SELECT p.*, d.name AS destination_name, d.region_state, c.name AS category_name, c.slug AS category_slug
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

$heroHeadline = get_setting('hero_headline', "The World Is Waiting. Let's Make It Yours.");
$heroSubheading = get_setting('hero_subheading', "Discover handpicked escapes, immersive cultural expeditions, and sacred pilgrimage trails tailored to your rhythm.");
$agencyWhatsApp = get_setting('whatsapp_number', '+911234567890');
$agencyPhone = get_setting('primary_phone', '+91 12345 67890');

require_once __DIR__ . '/includes/header.php';
?>

<!-- =====================================================================
     HERO SECTION
     ===================================================================== -->
<section class="hero-section" style="position:relative;min-height:100vh;background-color:var(--color-navy-deep);display:flex;align-items:center;padding-top:calc(var(--header-height) + 40px);padding-bottom:80px;overflow:hidden;">
  
  <!-- Atmospheric Background Visual with Mask Reveal -->
  <div style="position:absolute;inset:0;z-index:1;overflow:hidden;">
    <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=2000&q=85" 
         alt="Serene coastal travel horizon" 
         style="width:100%;height:100%;object-fit:cover;object-position:center;opacity:0.32;filter:brightness(0.7) contrast(1.1);transform:scale(1.05);"
         data-parallax-speed="0.08"
         onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
    <div style="position:absolute;inset:0;background:radial-gradient(circle at 60% 40%, rgba(0, 168, 150, 0.15) 0%, transparent 60%), linear-gradient(180deg, rgba(6, 16, 30, 0.7) 0%, rgba(6, 16, 30, 0.85) 65%, var(--color-navy-deep) 100%);"></div>
  </div>

  <!-- Decorative Animated Route Lines & Floating Elements -->
  <svg style="position:absolute;inset:0;width:100%;height:100%;z-index:2;pointer-events:none;opacity:0.25;" viewBox="0 0 1440 900" fill="none">
    <path class="route-path" d="M-50,200 C300,100 450,450 720,320 C990,190 1200,600 1500,400" stroke="#C5A059" stroke-width="2" />
    <circle cx="720" cy="320" r="6" fill="#00A896" />
    <circle cx="720" cy="320" r="14" stroke="#00A896" stroke-width="1.5" stroke-dasharray="3 3" class="rotate-slow" />
  </svg>

  <div class="container" style="position:relative;z-index:3;">
    <div style="max-width:840px;">
      
      <!-- Coordinate & Horizon Pill -->
      <div class="reveal-fade-up" style="display:inline-flex;align-items:center;gap:10px;background:rgba(255,255,255,0.08);backdrop-filter:blur(8px);border:1px solid var(--color-border-gold);padding:6px 18px;border-radius:var(--radius-pill);margin-bottom:24px;">
        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#02C39A;box-shadow:0 0 10px #02C39A;"></span>
        <span style="font-size:0.8rem;letter-spacing:0.18em;text-transform:uppercase;color:var(--color-gold);font-weight:600;">9.9312° N, 76.2673° E • Curated Journeys</span>
      </div>

      <!-- Main Editorial Headline with Staggered Reveal -->
      <h1 class="editorial-headline reveal-fade-up stagger-1" style="color:var(--color-white);margin-bottom:20px;">
        <?= e($heroHeadline) ?>
      </h1>

      <!-- Supporting Statement -->
      <p class="lead-text reveal-fade-up stagger-2" style="color:#CBD5E0;max-width:680px;margin-bottom:36px;font-size:1.18rem;">
        <?= e($heroSubheading) ?>
      </p>

      <!-- Primary & Secondary CTAs -->
      <div class="reveal-fade-up stagger-3" style="display:flex;align-items:center;flex-wrap:wrap;gap:18px;margin-bottom:48px;">
        <a href="<?= BASE_URL ?>/enquiry.php" class="btn btn-primary btn-lg pulse-gold">
          <span>Plan Your Journey</span>
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </a>
        <a href="#featuredDestinations" class="btn btn-outline-white btn-lg">
          <span>Explore Destinations</span>
        </a>
      </div>

      <!-- Compact Travel Quick-Finder Bar -->
      <div class="reveal-fade-up stagger-4 glass-specular" style="background:rgba(14, 23, 46, 0.72);backdrop-filter:blur(24px) saturate(180%);border:1px solid rgba(196, 181, 253, 0.35);border-radius:var(--radius-xl);padding:22px 28px;box-shadow:inset 0 1px 1px 0 rgba(255,255,255,0.25), 0 24px 50px -10px rgba(0,0,0,0.5);max-width:780px;">
        <form action="<?= BASE_URL ?>/packages.php" method="GET" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)) auto;gap:16px;align-items:end;">
          <div>
            <label style="display:block;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-lavender);margin-bottom:6px;font-weight:700;">Where To?</label>
            <select name="destination" style="width:100%;padding:11px 16px;background:rgba(10, 17, 40, 0.75);border:1.5px solid rgba(196, 181, 253, 0.35);border-radius:var(--radius-md);color:#FFF;font-size:0.9rem;outline:none;">
              <option value="">All Destinations</option>
              <?php foreach ($featuredDestinations as $fd): ?>
                <option value="<?= e($fd['slug']) ?>"><?= e($fd['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="display:block;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-lavender);margin-bottom:6px;font-weight:700;">Trip Style</label>
            <select name="category" style="width:100%;padding:11px 16px;background:rgba(10, 17, 40, 0.75);border:1.5px solid rgba(196, 181, 253, 0.35);border-radius:var(--radius-md);color:#FFF;font-size:0.9rem;outline:none;">
              <option value="">All Experiences</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <button type="submit" class="btn btn-teal" style="width:100%;padding:12px 24px;">
              <span>Find Tours</span>
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>

  <!-- Scroll Down Indicator -->
  <div style="position:absolute;bottom:24px;left:50%;transform:translateX(-50%);z-index:3;text-align:center;">
    <a href="#brandIntro" style="color:var(--color-gold);font-size:0.75rem;letter-spacing:0.15em;text-transform:uppercase;display:flex;flex-direction:column;align-items:center;gap:6px;opacity:0.8;">
      <span>Discover More</span>
      <svg class="scroll-indicator-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
    </a>
  </div>
</section>

<!-- =====================================================================
     SECTION A: BRAND INTRODUCTION (Editorial Asymmetric Layout)
     ===================================================================== -->
<section class="section-padding" id="brandIntro">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:clamp(36px, 6vw, 80px);align-items:center;">
      
      <!-- Left Column: Editorial Typography -->
      <div class="reveal-fade-left">
        <span class="section-subtitle no-after">The Art of Mindful Travel</span>
        <h2 class="section-title">Journeys Crafted With Distinction, Reverence &amp; Soul</h2>
        <p class="lead-text" style="margin-bottom:20px;">
          At <strong>MIDHILA VOYAGES</strong>, we believe travel is not merely about reaching a geographic pin on a map — it is an intimate encounter with history, landscape, and spirit.
        </p>
        <p style="margin-bottom:24px;color:var(--color-text-body);line-height:1.75;">
          Whether you seek the tranquil mist of Kerala's high ranges, the timeless vibration of sacred Vedic shrines, or the tropical azure waters of international archipelagos, our travel designers tailor every milestone with transparent pricing, verified hotel partnerships, and attentive ground coordination.
        </p>

        <!-- Trust Highlights Grid -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:32px;">
          <div style="border-left:3px solid var(--color-gold);padding-left:14px;background:rgba(255,255,255,0.25);backdrop-filter:blur(10px);padding:12px 14px;border-radius:0 var(--radius-sm) var(--radius-sm) 0;border-top:1px solid rgba(255,255,255,0.4);border-bottom:1px solid rgba(255,255,255,0.4);">
            <h4 style="font-size:1.05rem;color:var(--color-navy);margin-bottom:4px;">Bespoke Planning</h4>
            <p style="font-size:0.85rem;color:var(--color-text-muted);">Itineraries customized to your family rhythm and individual preferences.</p>
          </div>
          <div style="border-left:3px solid var(--color-teal);padding-left:14px;background:rgba(255,255,255,0.25);backdrop-filter:blur(10px);padding:12px 14px;border-radius:0 var(--radius-sm) var(--radius-sm) 0;border-top:1px solid rgba(255,255,255,0.4);border-bottom:1px solid rgba(255,255,255,0.4);">
            <h4 style="font-size:1.05rem;color:var(--color-navy);margin-bottom:4px;">Sacred Tourism</h4>
            <p style="font-size:0.85rem;color:var(--color-text-muted);">Devotional circuits arranged with senior comfort, clean transit, and peaceful darshans.</p>
          </div>
        </div>

        <a href="<?= BASE_URL ?>/about.php" class="btn btn-outline-gold">
          <span>Read Our Story</span>
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </a>
      </div>

      <!-- Right Column: Asymmetric Layered Image Frame -->
      <div class="reveal-fade-right" style="position:relative;">
        <!-- Large Arch Image Frame -->
        <div style="width:85%;margin-left:auto;height:480px;border-radius:var(--radius-arch);overflow:hidden;box-shadow:var(--shadow-xl), 0 0 35px rgba(196,181,253,0.3);border:2px solid rgba(255,255,255,0.75);">
          <img src="https://images.unsplash.com/photo-1593693397690-362cb9666fc2?auto=format&fit=crop&w=1000&q=80" 
               alt="Misty tea gardens of Munnar in Kerala" 
               style="width:100%;height:100%;object-fit:cover;"
               onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
        </div>

        <!-- Overlapping Offset Polaroid Badge -->
        <div class="card-3d" style="position:absolute;bottom:-20px;left:0;width:55%;background:var(--glass-surface-card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);padding:14px;border-radius:var(--radius-md);box-shadow:var(--glass-inner-highlight), var(--shadow-xl);border:1px solid var(--glass-border);">
          <img src="https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=600&q=80" 
               alt="Ancient temple gopuram" 
               style="width:100%;height:140px;object-fit:cover;border-radius:var(--radius-sm);margin-bottom:10px;"
               onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
          <div style="font-family:var(--font-serif);font-size:0.92rem;font-weight:700;color:var(--color-navy);">Living Heritage Trails</div>
          <div style="font-size:0.75rem;color:var(--color-teal-dark);">Tamil Nadu &amp; Kashi Circuits</div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- =====================================================================
     SECTION B: FEATURED DESTINATIONS
     ===================================================================== -->
<section class="section-padding section-dark" id="featuredDestinations">
  <div class="container">
    
    <div class="section-header" style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:20px;">
      <div>
        <span class="section-subtitle">Curated Horizons</span>
        <h2 class="section-title">Featured Destinations</h2>
        <p class="lead-text" style="max-width:540px;">From the tranquil backwaters of South India to vibrant international metropolises and coral lagoons.</p>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/destinations.php" class="btn btn-outline-gold">View All Destinations</a>
      </div>
    </div>

    <!-- Varied Composition Cards Grid -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:28px;">
      <?php foreach ($featuredDestinations as $idx => $dest): ?>
        <div class="card-perspective reveal-fade-up stagger-<?= ($idx % 3) + 1 ?>">
          <a href="<?= BASE_URL ?>/destination.php?slug=<?= e($dest['slug']) ?>" class="dest-card card-3d">
            <img src="<?= e(get_image_url($dest['featured_image'])) ?>" alt="<?= e($dest['alt_text'] ?: $dest['name']) ?>" class="dest-card__bg" loading="lazy" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
            <div class="dest-card__overlay"></div>
            
            <span class="dest-card__tag"><?= strtoupper(e($dest['category'])) ?></span>

            <div class="dest-card__content">
              <div class="dest-card__location">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span><?= e($dest['region_state']) ?>, <?= e($dest['country']) ?></span>
              </div>
              <h3 class="dest-card__title"><?= e($dest['name']) ?></h3>
              <p class="dest-card__desc"><?= e($dest['tagline'] ?: substr($dest['description'], 0, 100) . '...') ?></p>
              
              <div class="dest-card__action">
                <span>Discover Destination</span>
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
              </div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- =====================================================================
     SECTION C: TOUR PACKAGE DISCOVERY (Filter Tabs & Search)
     ===================================================================== -->
<section class="section-padding">
  <div class="container">
    
    <div class="section-header section-header--center">
      <span class="section-subtitle">Handpicked Itineraries</span>
      <h2 class="section-title">Discover Tour Packages</h2>
      <p class="lead-text">Choose from our domestic vacations, global excursions, romantic getaways, and devotional pilgrimage routes.</p>
    </div>

    <!-- Category Filter Tabs & Search Bar -->
    <div style="margin-bottom:36px;display:flex;flex-direction:column;gap:20px;align-items:center;">
      
      <!-- Tabs -->
      <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:10px;">
        <button type="button" class="package-filter-tab is-active" data-category-slug="all" style="padding:9px 22px;border-radius:var(--radius-pill);font-size:0.86rem;font-weight:600;border:1px solid rgba(196,181,253,0.35);background:rgba(255,255,255,0.45);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);color:var(--color-navy);cursor:pointer;transition:all var(--transition-fast);">
          All Packages
        </button>
        <?php foreach ($categories as $cat): ?>
          <button type="button" class="package-filter-tab" data-category-slug="<?= e($cat['slug']) ?>" style="padding:9px 22px;border-radius:var(--radius-pill);font-size:0.86rem;font-weight:600;border:1px solid rgba(196,181,253,0.35);background:rgba(255,255,255,0.3);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);color:var(--color-navy);cursor:pointer;transition:all var(--transition-fast);">
            <?= e($cat['name']) ?>
          </button>
        <?php endforeach; ?>
      </div>

      <!-- Live Search Input -->
      <div style="width:100%;max-width:440px;position:relative;">
        <input type="text" id="packageSearch" placeholder="Search by destination or package name..." 
               style="width:100%;padding:12px 18px 12px 42px;border:1px solid rgba(196,181,253,0.4);border-radius:var(--radius-pill);font-size:0.9rem;background:rgba(255,255,255,0.48);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);box-shadow:var(--glass-inner-highlight), var(--shadow-sm);outline:none;color:var(--color-navy);">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="position:absolute;left:16px;top:50%;transform:translateY(-50%);color:var(--color-text-muted);">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
      </div>

    </div>

    <!-- Package Cards Grid -->
    <div class="package-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:28px;">
      <?php foreach ($packages as $package): ?>
        <?php include __DIR__ . '/includes/package-card.php'; ?>
      <?php endforeach; ?>
    </div>

    <!-- Empty State for Live Filter -->
    <div id="packageEmptyState" style="display:none;text-align:center;padding:60px 20px;background:var(--glass-surface-card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);border-radius:var(--radius-lg);margin-top:24px;border:1px dashed rgba(196,181,253,0.45);box-shadow:var(--shadow-md);">
      <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 16px;color:var(--color-gold);"><circle cx="12" cy="12" r="10"/><path d="M16 16s-1.5-2-4-2-4 2-4 2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
      <h3 style="font-size:1.3rem;margin-bottom:8px;">No packages match your search</h3>
      <p style="color:var(--color-text-muted);margin-bottom:20px;">Try adjusting your keywords or category filters, or enquire for a custom travel plan.</p>
      <a href="<?= BASE_URL ?>/enquiry.php" class="btn btn-primary">Custom Travel Enquiry</a>
    </div>

    <div style="text-align:center;margin-top:48px;">
      <a href="<?= BASE_URL ?>/packages.php" class="btn btn-outline-gold btn-lg">Browse Complete Catalog</a>
    </div>

  </div>
</section>

<!-- =====================================================================
     SECTION D: PILGRIMAGE TRAVEL (Dedicated Spiritual Experience)
     ===================================================================== -->
<section class="section-padding" style="background:linear-gradient(180deg, #0A192F 0%, #0D223F 100%);color:#FFF;position:relative;overflow:hidden;">
  
  <div class="container" style="position:relative;z-index:2;">
    
    <div class="section-header" style="text-align:center;max-width:760px;margin-left:auto;margin-right:auto;">
      <span class="section-subtitle" style="color:var(--color-gold-bright);">Sacred Horizons &amp; Devotional Trails</span>
      <h2 class="section-title" style="color:#FFF;">Pilgrimage Tourism with Dignity &amp; Care</h2>
      <p class="lead-text" style="color:#CBD5E0;">
        Experience seamless darshans, respectful logistics, and serene spiritual environments across India's most venerated shrines — Varanasi, Ayodhya, and Tamil Nadu's ancient temple corridors.
      </p>
    </div>

    <!-- 3 Highlighted Sacred Circuits -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:28px;margin-bottom:48px;">
      <?php foreach ($pilgrimagePackages as $package): ?>
        <?php include __DIR__ . '/includes/package-card.php'; ?>
      <?php endforeach; ?>
    </div>

    <!-- Sacred Tourism Key Guarantees -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:24px;background:rgba(255,255,255,0.05);backdrop-filter:blur(10px);border:1px solid var(--color-border-gold);border-radius:var(--radius-lg);padding:30px;">
      <div style="display:flex;gap:14px;align-items:flex-start;">
        <span style="font-size:1.5rem;color:var(--color-gold);">🏛️</span>
        <div>
          <h4 style="font-size:1rem;color:#FFF;margin-bottom:4px;">Temple Vicinity Stays</h4>
          <p style="font-size:0.85rem;color:#A0AEC0;">Clean, vetted hotels near temple precincts to minimize walking for elders.</p>
        </div>
      </div>
      <div style="display:flex;gap:14px;align-items:flex-start;">
        <span style="font-size:1.5rem;color:var(--color-teal-bright);">🕉️</span>
        <div>
          <h4 style="font-size:1rem;color:#FFF;margin-bottom:4px;">Darshan Guidance</h4>
          <p style="font-size:0.85rem;color:#A0AEC0;">Assistance with darshan protocols, timings, and local queue coordination.</p>
        </div>
      </div>
      <div style="display:flex;gap:14px;align-items:flex-start;">
        <span style="font-size:1.5rem;color:var(--color-gold);">🚐</span>
        <div>
          <h4 style="font-size:1rem;color:#FFF;margin-bottom:4px;">Dedicated AC Transit</h4>
          <p style="font-size:0.85rem;color:#A0AEC0;">Private sanitised vehicles with polite, experienced local chauffeurs.</p>
        </div>
      </div>
    </div>

    <div style="text-align:center;margin-top:40px;">
      <a href="<?= BASE_URL ?>/pilgrimage.php" class="btn btn-primary btn-lg">Explore All Pilgrimage Circuits</a>
    </div>

  </div>
</section>

<!-- =====================================================================
     SECTION E: WHY CHOOSE MIDHILA VOYAGES (Trust Signals)
     ===================================================================== -->
<section class="section-padding">
  <div class="container">
    
    <div class="section-header section-header--center">
      <span class="section-subtitle">Our Principles</span>
      <h2 class="section-title">Why Travel With Midhila Voyages</h2>
      <p class="lead-text">Genuine care, clear communication, and professional accountability at every turn.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:28px;">
      
      <div class="reveal-fade-up stagger-1 why-card" style="background:var(--glass-surface-card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);padding:32px 26px;border-radius:var(--radius-lg);box-shadow:var(--glass-inner-highlight), var(--shadow-md);border:1px solid var(--glass-border);transition:transform var(--transition-base), box-shadow var(--transition-base);height:100%;display:flex;flex-direction:column;">
        <div style="width:52px;height:52px;border-radius:12px;background:var(--color-gold-soft);color:var(--color-gold-dark);display:flex;align-items:center;justify-content:center;margin-bottom:18px;border:1px solid rgba(197,160,89,0.35);">
          <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <h3 style="font-size:1.2rem;margin-bottom:10px;color:var(--color-navy);">Personalized Itinerary Design</h3>
        <p style="font-size:0.9rem;color:var(--color-text-muted);line-height:1.6;margin-top:auto;">No rigid cookie-cutter formulas. Every schedule is tailored to your pace, dietary needs, and travel style.</p>
      </div>

      <div class="reveal-fade-up stagger-2 why-card" style="background:var(--glass-surface-card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);padding:32px 26px;border-radius:var(--radius-lg);box-shadow:var(--glass-inner-highlight), var(--shadow-md);border:1px solid var(--glass-border);transition:transform var(--transition-base), box-shadow var(--transition-base);height:100%;display:flex;flex-direction:column;">
        <div style="width:52px;height:52px;border-radius:12px;background:rgba(0,168,150,0.15);color:var(--color-teal);display:flex;align-items:center;justify-content:center;margin-bottom:18px;border:1px solid rgba(0,168,150,0.3);">
          <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        </div>
        <h3 style="font-size:1.2rem;margin-bottom:10px;color:var(--color-navy);">Transparent Package Details</h3>
        <p style="font-size:0.9rem;color:var(--color-text-muted);line-height:1.6;margin-top:auto;">Explicit listings of inclusions, vehicle types, stay categories, and exclusions with zero hidden surprises.</p>
      </div>

      <div class="reveal-fade-up stagger-3 why-card" style="background:var(--glass-surface-card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);padding:32px 26px;border-radius:var(--radius-lg);box-shadow:var(--glass-inner-highlight), var(--shadow-md);border:1px solid var(--glass-border);transition:transform var(--transition-base), box-shadow var(--transition-base);height:100%;display:flex;flex-direction:column;">
        <div style="width:52px;height:52px;border-radius:12px;background:rgba(124,58,237,0.15);color:var(--color-periwinkle-dark);display:flex;align-items:center;justify-content:center;margin-bottom:18px;border:1px solid rgba(196,181,253,0.35);">
          <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <h3 style="font-size:1.2rem;margin-bottom:10px;color:var(--color-navy);">Multi-Generational Harmony</h3>
        <p style="font-size:0.9rem;color:var(--color-text-muted);line-height:1.6;margin-top:auto;">Thoughtfully balanced for grandparents, parents, and youngsters with comfortable transit breaks.</p>
      </div>

      <div class="reveal-fade-up stagger-4 why-card" style="background:var(--glass-surface-card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);padding:32px 26px;border-radius:var(--radius-lg);box-shadow:var(--glass-inner-highlight), var(--shadow-md);border:1px solid var(--glass-border);transition:transform var(--transition-base), box-shadow var(--transition-base);height:100%;display:flex;flex-direction:column;">
        <div style="width:52px;height:52px;border-radius:12px;background:rgba(37,211,102,0.15);color:#25D366;display:flex;align-items:center;justify-content:center;margin-bottom:18px;border:1px solid rgba(37,211,102,0.3);">
          <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 2.11.83 3.292.83 3.183 0 5.768-2.587 5.768-5.766.001-3.181-2.585-5.767-5.764-5.767zm7.653 5.766c0 4.225-3.438 7.663-7.654 7.663-1.309 0-2.569-.334-3.674-.925l-4.148 1.088 1.107-4.041c-.696-1.187-1.077-2.55-1.077-3.957 0-4.224 3.438-7.662 7.654-7.662 4.226 0 7.662 3.438 7.662 7.662z"/></svg>
        </div>
        <h3 style="font-size:1.2rem;margin-bottom:10px;color:var(--color-navy);">Responsive WhatsApp Support</h3>
        <p style="font-size:0.9rem;color:var(--color-text-muted);line-height:1.6;margin-top:auto;">Direct access to human travel specialists before, during, and after your trip for prompt assistance.</p>
      </div>

    </div>

  </div>
</section>

<!-- =====================================================================
     SECTION F: HOW THE JOURNEY WORKS (5-Step Visual Process)
     ===================================================================== -->
<section class="section-padding" style="background:rgba(6,11,24,0.55);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);color:#FFF;">
  <div class="container">
    
    <div class="section-header section-header--center">
      <span class="section-subtitle" style="color:var(--color-gold);">Seamless Journey Process</span>
      <h2 class="section-title" style="color:#FFF;">How Your Experience Unfolds</h2>
      <p class="lead-text" style="color:#CBD5E0;">From your initial spark of inspiration to your triumphant return home.</p>
    </div>

    <!-- 5 Steps with Connecting Line -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(210px, 1fr));gap:24px;position:relative;">
      
      <div class="reveal-fade-up stagger-1 process-step-card" style="background:linear-gradient(135deg, rgba(20,32,60,0.55) 0%, rgba(14,23,46,0.4) 100%);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);border:1px solid rgba(196,181,253,0.3);border-radius:var(--radius-md);padding:24px 20px;text-align:center;height:100%;display:flex;flex-direction:column;box-shadow:inset 0 1px 1px rgba(255,255,255,0.2), 0 12px 30px rgba(0,0,0,0.35);">
        <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg, #DFBA73, #C5A059);color:#06101E;font-family:var(--font-serif);font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.1rem;flex-shrink:0;box-shadow:0 0 16px rgba(197,160,89,0.4);">1</div>
        <h4 style="font-size:1.05rem;color:#FFF;margin-bottom:8px;">Choose Destination</h4>
        <p style="font-size:0.85rem;color:#CBD5E1;margin-top:auto;">Browse our curated packages or share your wishlist destination.</p>
      </div>

      <div class="reveal-fade-up stagger-2 process-step-card" style="background:linear-gradient(135deg, rgba(20,32,60,0.55) 0%, rgba(14,23,46,0.4) 100%);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);border:1px solid rgba(196,181,253,0.3);border-radius:var(--radius-md);padding:24px 20px;text-align:center;height:100%;display:flex;flex-direction:column;box-shadow:inset 0 1px 1px rgba(255,255,255,0.2), 0 12px 30px rgba(0,0,0,0.35);">
        <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg, #DFBA73, #C5A059);color:#06101E;font-family:var(--font-serif);font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.1rem;flex-shrink:0;box-shadow:0 0 16px rgba(197,160,89,0.4);">2</div>
        <h4 style="font-size:1.05rem;color:#FFF;margin-bottom:8px;">Share Preferences</h4>
        <p style="font-size:0.85rem;color:#CBD5E1;margin-top:auto;">Tell us your tentative dates, traveller count, and comfort style.</p>
      </div>

      <div class="reveal-fade-up stagger-3 process-step-card" style="background:linear-gradient(135deg, rgba(20,32,60,0.55) 0%, rgba(14,23,46,0.4) 100%);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);border:1px solid rgba(196,181,253,0.3);border-radius:var(--radius-md);padding:24px 20px;text-align:center;height:100%;display:flex;flex-direction:column;box-shadow:inset 0 1px 1px rgba(255,255,255,0.2), 0 12px 30px rgba(0,0,0,0.35);">
        <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg, #DFBA73, #C5A059);color:#06101E;font-family:var(--font-serif);font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.1rem;flex-shrink:0;box-shadow:0 0 16px rgba(197,160,89,0.4);">3</div>
        <h4 style="font-size:1.05rem;color:#FFF;margin-bottom:8px;">Receive Proposal</h4>
        <p style="font-size:0.85rem;color:#CBD5E1;margin-top:auto;">Review an itemized day-by-day itinerary with transparent costs.</p>
      </div>

      <div class="reveal-fade-up stagger-4 process-step-card" style="background:linear-gradient(135deg, rgba(20,32,60,0.55) 0%, rgba(14,23,46,0.4) 100%);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);border:1px solid rgba(196,181,253,0.3);border-radius:var(--radius-md);padding:24px 20px;text-align:center;height:100%;display:flex;flex-direction:column;box-shadow:inset 0 1px 1px rgba(255,255,255,0.2), 0 12px 30px rgba(0,0,0,0.35);">
        <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg, #DFBA73, #C5A059);color:#06101E;font-family:var(--font-serif);font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.1rem;flex-shrink:0;box-shadow:0 0 16px rgba(197,160,89,0.4);">4</div>
        <h4 style="font-size:1.05rem;color:#FFF;margin-bottom:8px;">Confirm &amp; Deposit</h4>
        <p style="font-size:0.85rem;color:#CBD5E1;margin-top:auto;">Lock in your hotel reservations and receive official vouchers.</p>
      </div>

      <div class="reveal-fade-up stagger-5 process-step-card" style="background:linear-gradient(135deg, rgba(20,32,60,0.55) 0%, rgba(14,23,46,0.4) 100%);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);border:1px solid rgba(196,181,253,0.3);border-radius:var(--radius-md);padding:24px 20px;text-align:center;height:100%;display:flex;flex-direction:column;box-shadow:inset 0 1px 1px rgba(255,255,255,0.2), 0 12px 30px rgba(0,0,0,0.35);">
        <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg, #02C39A, #00A896);color:#FFF;font-family:var(--font-serif);font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.1rem;flex-shrink:0;box-shadow:0 0 16px rgba(0,168,150,0.4);">5</div>
        <h4 style="font-size:1.05rem;color:#FFF;margin-bottom:8px;">Embark &amp; Savor</h4>
        <p style="font-size:0.85rem;color:#CBD5E1;margin-top:auto;">Enjoy continuous on-trip coordination for total peace of mind.</p>
      </div>

    </div>

  </div>
</section>

<!-- =====================================================================
     SECTION G: TRAVEL GALLERY PREVIEW
     ===================================================================== -->
<section class="section-padding">
  <div class="container">
    
    <div class="section-header" style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:20px;">
      <div>
        <span class="section-subtitle">Visual Memoirs</span>
        <h2 class="section-title">Experiences &amp; Travel Gallery</h2>
        <p class="lead-text" style="max-width:540px;">Glimpses of tranquil waters, golden hours, and sacred corridors captured across journeys.</p>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/gallery.php" class="btn btn-outline-gold">View Full Gallery</a>
      </div>
    </div>

    <!-- Gallery Masonry Preview Grid -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(300px, 1fr));gap:20px;">
      <?php foreach ($galleryItems as $idx => $item): ?>
        <div class="gallery-card reveal-fade-up stagger-<?= ($idx % 3) + 1 ?>" 
             data-full="<?= e(get_image_url($item['image_url'])) ?>"
             data-title="<?= e($item['title']) ?>"
             data-location="<?= e($item['location_name']) ?>"
             style="position:relative;height:280px;border-radius:var(--radius-lg);overflow:hidden;cursor:pointer;box-shadow:var(--shadow-md);border:1px solid rgba(255,255,255,0.4);">
          <img src="<?= e(get_image_url($item['image_url'])) ?>" alt="<?= e($item['alt_text']) ?>" style="width:100%;height:100%;object-fit:cover;transition:transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);" loading="lazy" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
          <div style="position:absolute;inset:0;background:linear-gradient(180deg, transparent 40%, rgba(6,16,30,0.85) 100%);backdrop-filter:blur(2px);display:flex;flex-direction:column;justify-content:flex-end;padding:20px;color:#FFF;">
            <span style="font-size:0.75rem;color:var(--color-gold);text-transform:uppercase;letter-spacing:0.1em;"><?= e($item['location_name']) ?></span>
            <h4 style="font-size:1.1rem;color:#FFF;margin-top:2px;"><?= e($item['title']) ?></h4>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- Lightbox Modal Component (Accessible) -->
<div id="galleryLightbox" class="lightbox-modal" style="position:fixed;inset:0;background:rgba(6,16,30,0.88);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);z-index:2000;display:none;align-items:center;justify-content:center;padding:20px;">
  <button class="lightbox__close" style="position:absolute;top:24px;right:24px;color:#FFF;font-size:2rem;line-height:1;background:none;border:none;cursor:pointer;" aria-label="Close Lightbox">&times;</button>
  <button class="lightbox__prev" style="position:absolute;left:24px;color:#FFF;font-size:2rem;background:none;border:none;cursor:pointer;" aria-label="Previous image">&#10094;</button>
  <button class="lightbox__next" style="position:absolute;right:24px;color:#FFF;font-size:2rem;background:none;border:none;cursor:pointer;" aria-label="Next image">&#10095;</button>
  
  <div style="max-width:900px;width:100%;text-align:center;">
    <img src="" alt="" class="lightbox__image" style="max-height:75vh;margin:0 auto;border-radius:var(--radius-md);box-shadow:var(--shadow-xl);border:1px solid rgba(255,255,255,0.3);">
    <div style="margin-top:16px;">
      <h3 class="lightbox__title" style="color:#FFF;font-size:1.3rem;"></h3>
      <p class="lightbox__location" style="color:var(--color-gold);font-size:0.9rem;"></p>
    </div>
  </div>
</div>
<style>
.lightbox-modal.is-open { display: flex !important; }
</style>

<!-- =====================================================================
     SECTION H: TESTIMONIALS (Verified Experiences)
     ===================================================================== -->
<?php if (!empty($testimonials)): ?>
<section class="section-padding">
  <div class="container">
    
    <div class="section-header section-header--center">
      <span class="section-subtitle">Verified Feedback</span>
      <h2 class="section-title">Words From Our Travellers</h2>
      <p class="lead-text">Genuine experiences shared by families, couples, and pilgrims who explored with Midhila Voyages.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:28px;">
      <?php foreach ($testimonials as $idx => $t): ?>
        <div class="reveal-fade-up stagger-<?= ($idx % 3) + 1 ?>" style="background:var(--glass-surface-card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);padding:32px;border-radius:var(--radius-lg);box-shadow:var(--glass-inner-highlight), var(--shadow-md);border:1px solid var(--glass-border);display:flex;flex-direction:column;justify-content:space-between;">
          <div>
            <!-- Star Rating -->
            <div style="color:var(--color-gold);font-size:1.1rem;margin-bottom:14px;">
              <?= str_repeat('★', (int)$t['rating']) ?>
            </div>
            <p style="font-style:italic;color:var(--color-text-body);line-height:1.7;margin-bottom:20px;font-size:0.95rem;">
              &ldquo;<?= e($t['feedback']) ?>&rdquo;
            </p>
          </div>

          <div style="border-top:1px solid rgba(196,181,253,0.3);padding-top:16px;display:flex;align-items:center;justify-content:space-between;">
            <div>
              <div style="font-weight:700;color:var(--color-navy);"><?= e($t['client_name']) ?></div>
              <div style="font-size:0.8rem;color:var(--color-text-muted);"><?= e($t['location'] ?: 'Traveller') ?></div>
            </div>
            <span style="font-size:0.75rem;background:rgba(0,168,150,0.15);color:var(--color-teal);padding:4px 12px;border-radius:var(--radius-pill);font-weight:600;border:1px solid rgba(0,168,150,0.3);">Verified Tour</span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>
<?php endif; ?>

<!-- =====================================================================
     SECTION I: FREQUENTLY ASKED QUESTIONS
     ===================================================================== -->
<section class="section-padding">
  <div class="container container-narrow">
    
    <div class="section-header section-header--center">
      <span class="section-subtitle">Clear Answers</span>
      <h2 class="section-title">Frequently Asked Questions</h2>
      <p class="lead-text">Everything you need to know about enquiry, package customization, and booking with us.</p>
    </div>

    <div class="faq-accordion" style="display:flex;flex-direction:column;gap:14px;">
      <?php foreach ($faqs as $idx => $faq): ?>
        <details class="faq-item reveal-fade-up" style="background:var(--glass-surface-card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);border:1px solid var(--glass-border);border-radius:var(--radius-md);padding:18px 24px;box-shadow:var(--glass-inner-highlight), var(--shadow-sm);transition:all var(--transition-base);">
          <summary style="font-family:var(--font-serif);font-size:1.05rem;font-weight:700;color:var(--color-navy);cursor:pointer;outline:none;display:flex;justify-content:space-between;align-items:center;">
            <span><?= e($faq['question']) ?></span>
            <span style="color:var(--color-periwinkle-dark);font-size:1.4rem;font-weight:400;margin-left:12px;">+</span>
          </summary>
          <div style="padding-top:14px;color:var(--color-text-body);line-height:1.7;font-size:0.95rem;border-top:1px solid rgba(196,181,253,0.25);margin-top:12px;">
            <?= nl2br(e($faq['answer'])) ?>
          </div>
        </details>
      <?php endforeach; ?>
    </div>

    <div style="text-align:center;margin-top:36px;">
      <p style="font-size:0.95rem;color:var(--color-text-muted);margin-bottom:14px;">Have a specific question not listed here?</p>
      <a href="<?= BASE_URL ?>/contact.php" class="btn btn-outline-gold">Reach Out to Our Help Desk</a>
    </div>

  </div>
</section>

<!-- =====================================================================
     SECTION J: FINAL CONVERSION CTA
     ===================================================================== -->
<section class="section-padding" style="background:linear-gradient(135deg, var(--color-navy-deep) 0%, #0D1B3E 50%, #070D1E 100%);color:#FFF;position:relative;overflow:hidden;text-align:center;">
  
  <!-- Decorative Animated Compass Rose -->
  <div style="position:absolute;top:-60px;right:-60px;width:320px;height:320px;opacity:0.12;pointer-events:none;">
    <svg class="rotate-slow" viewBox="0 0 100 100" fill="none" stroke="#C4B5FD" stroke-width="1.2">
      <circle cx="50" cy="50" r="46" stroke-dasharray="4 4" />
      <circle cx="50" cy="50" r="32" stroke-dasharray="2 2" />
      <polygon points="50,10 57,43 90,50 57,57 50,90 43,57 10,50 43,43" fill="rgba(196,181,253,0.35)" />
    </svg>
  </div>
  <div style="position:absolute;bottom:-60px;left:-60px;width:280px;height:280px;opacity:0.1;pointer-events:none;">
    <svg class="rotate-slow-reverse" viewBox="0 0 100 100" fill="none" stroke="#8DA4EF" stroke-width="1.2">
      <circle cx="50" cy="50" r="46" stroke-dasharray="3 3" />
      <polygon points="50,15 56,44 85,50 56,56 50,85 44,56 15,50 44,44" fill="rgba(141,164,239,0.3)" />
    </svg>
  </div>

  <div class="container" style="position:relative;z-index:2;max-width:820px;">
    <!-- Floating Journey Emblem -->
    <div class="reveal-fade-up float-slow" style="display:inline-flex;align-items:center;gap:10px;background:rgba(196,181,253,0.12);backdrop-filter:blur(12px);border:1px solid rgba(196,181,253,0.35);padding:6px 20px;border-radius:var(--radius-pill);margin-bottom:24px;box-shadow:0 0 20px rgba(139,92,246,0.25);">
      <span style="font-size:1rem;">✨</span>
      <span style="font-size:0.8rem;letter-spacing:0.18em;text-transform:uppercase;color:var(--color-lavender);font-weight:700;">Begin Your Next Chapter</span>
    </div>

    <h2 class="editorial-headline reveal-fade-up stagger-1" style="color:#FFF;margin-bottom:20px;font-size:clamp(2.2rem, 4.5vw, 3.4rem);text-shadow:0 0 24px rgba(196,181,253,0.2);">
      Your Next Great Journey Starts Here.
    </h2>
    <p class="lead-text reveal-fade-up stagger-2" style="color:#CBD5E0;margin-bottom:38px;max-width:700px;margin-left:auto;margin-right:auto;">
      Whether it is an intimate family vacation, a romantic anniversary getaway, or a peaceful devotional pilgrimage, our travel specialists are ready to turn your aspirations into an unforgettable reality.
    </p>

    <div class="reveal-fade-up stagger-3" style="display:flex;align-items:center;justify-content:center;flex-wrap:wrap;gap:18px;">
      <a href="<?= BASE_URL ?>/enquiry.php" class="btn btn-primary btn-lg pulse-luminous">
        <span>Plan My Trip</span>
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
      </a>
      <a href="<?= e(build_whatsapp_url($agencyWhatsApp, "Hello MIDHILA VOYAGES, I would like to consult with your travel specialist to plan a trip.")) ?>" 
         target="_blank" rel="noopener noreferrer" class="btn btn-teal btn-lg">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 2.11.83 3.292.83 3.183 0 5.768-2.587 5.768-5.766.001-3.181-2.585-5.767-5.764-5.767zm7.653 5.766c0 4.225-3.438 7.663-7.654 7.663-1.309 0-2.569-.334-3.674-.925l-4.148 1.088 1.107-4.041c-.696-1.187-1.077-2.55-1.077-3.957 0-4.224 3.438-7.662 7.654-7.662 4.226 0 7.662 3.438 7.662 7.662z"/></svg>
        <span>Instant WhatsApp Consultation</span>
      </a>
    </div>

    <div class="reveal-fade-up stagger-4" style="margin-top:32px;display:flex;align-items:center;justify-content:center;gap:20px;font-size:0.88rem;color:#A0AEC0;">
      <span>📞 Call Us: <a href="tel:<?= preg_replace('/[^0-9+]/', '', $agencyPhone) ?>" style="color:var(--color-lavender);font-weight:600;"><?= e($agencyPhone) ?></a></span>
      <span>•</span>
      <span>⚡ Quick Response Guaranteed</span>
    </div>
  </div>
</section>

<!-- Page Specific Scripts -->
<script src="<?= ASSETS_URL ?>/js/packages.js" defer></script>
<script src="<?= ASSETS_URL ?>/js/gallery.js" defer></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
