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

// 2. Fetch Package Categories
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
    LIMIT 6
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

$agencyWhatsApp = get_setting('whatsapp_number', '+918075021427');
$agencyPhone = get_setting('primary_phone', '+91 8075021427');

require_once __DIR__ . '/includes/header.php';
?>

<!-- =====================================================================
     HERO SECTION (CINEMATIC LUXURY ON PURE WHITE)
     ===================================================================== -->
<section class="section-hero" id="heroSection">
  
  <!-- Delicate Geographical Texture Grid Watermark -->
  <div class="geo-texture-grid" aria-hidden="true"></div>

  <div class="container hero-container">
    <div class="hero-grid">
      
      <!-- Left Column: Crystal-Clear Editorial Headline & Subtext -->
      <div class="hero-content">
        <h1 class="editorial-headline-hero">
          Travelled by Scroll.<br>
          <span class="editorial-headline-hero__sub">
            Unfolding Sacred &amp; Sublime Horizons.
          </span>
        </h1>

        <p class="editorial-subtext">
          Where bespoke travel is an art of quiet luxury, reverence, and unhurried discovery. Handcrafted journeys across India’s most storied sanctuaries.
        </p>
      </div>

      <!-- Right Column: Dedicated Visual Showcase Frame (Images & 3D Globe) -->
      <div class="hero-visual">
        
        <!-- 3D Interactive Three.js Golden Coordinate Sphere (Contained in Right Frame) -->
        <div class="travel-scene-wrapper" aria-hidden="true">
          <canvas id="travelSceneCanvas"></canvas>
          <div class="fallback-globe">
            <div class="fallback-globe__ring fallback-globe__ring--1"></div>
            <div class="fallback-globe__ring fallback-globe__ring--2"></div>
          </div>
        </div>

        <!-- Layered Destination Photo Memoirs Collage -->
        <div class="hero-memoirs-frame" aria-hidden="true">
          <!-- Memoir 1: Kerala Backwaters (Primary Feature) -->
          <div class="hero-memoir hero-memoir--1">
            <img src="https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=650&q=80" 
                 alt="Kerala Tranquil Waters" 
                 loading="eager">
          </div>

          <!-- Memoir 2: Sacred Temple Gopuram (Secondary Layer) -->
          <div class="hero-memoir hero-memoir--2">
            <img src="https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=550&q=80" 
                 alt="Sacred Architecture" 
                 loading="lazy">
          </div>

          <!-- Memoir 3: Varanasi Dawn Ghat (Tertiary Layer) -->
          <div class="hero-memoir hero-memoir--3">
            <img src="https://images.unsplash.com/photo-1561359313-0639aad49ca6?auto=format&fit=crop&w=500&q=80" 
                 alt="Ganga Sunrise" 
                 loading="lazy">
          </div>
        </div>

      </div>

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
    
    <!-- Chapter Header -->
    <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:24px;margin-bottom:60px;">
      <div>
        <div class="chapter-badge">
          <span class="chapter-number">Chapter II &bull; Horizons</span>
          <span class="chapter-coordinates">Geographic Sanctuaries</span>
        </div>
        <h2 class="editorial-headline-large">
          Six Sacred Lands.<br>
          <span style="font-family:var(--font-editorial);font-style:italic;font-weight:400;color:var(--color-text-secondary);">
            Crafted for the Discerning Explorer.
          </span>
        </h2>
      </div>

      <div>
        <a href="<?= BASE_URL ?>/destinations.php" class="link-editorial-arrow">
          <span>View All Horizons</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </a>
      </div>
    </div>

    <!-- Asymmetric Showcase Grid (Pure White Cards, Full 100% Photos) -->
    <div class="dest-chapter-grid">
      <?php if (!empty($featuredDestinations)): ?>
        <?php foreach ($featuredDestinations as $idx => $dest): 
          $colSpan = ($idx === 0 || $idx === 3) ? 'grid-column: span 7;' : 'grid-column: span 5;';
          $destImg = !empty($dest['featured_image']) ? $dest['featured_image'] : (ASSETS_URL . '/images/placeholder.svg');
          $destSlug = $dest['slug'] ?? ('destination-' . $dest['id']);
        ?>
          <div class="dest-showcase-card reveal-fade-up stagger-<?= ($idx % 3) + 1 ?>" style="<?= $colSpan ?>">
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
                <?= e($dest['region_state'] ?? 'India') ?> &bull; Curated Circuit
              </div>
              <h3 class="dest-title">
                <a href="<?= BASE_URL ?>/destination.php?slug=<?= urlencode($destSlug) ?>">
                  <?= e($dest['name']) ?>
                </a>
              </h3>
              <p style="font-size:0.9rem;color:var(--color-text-muted);line-height:1.65;margin-bottom:16px;">
                <?= e(truncate_words($dest['description'] ?? '', 18)) ?>
              </p>
              <a href="<?= BASE_URL ?>/destination.php?slug=<?= urlencode($destSlug) ?>" class="link-editorial-arrow">
                <span>Discover Itinerary</span>
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
     CHAPTER III: SIGNATURE JOURNEYS (TOUR PACKAGES CATALOG)
     ===================================================================== -->
<section class="section-padding" id="signature-journeys" style="position:relative;background:#FFFFFF;">
  
  <div class="container">
    
    <!-- Chapter Header -->
    <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:24px;margin-bottom:50px;">
      <div>
        <div class="chapter-badge">
          <span class="chapter-number">Chapter III &bull; Portfolios</span>
          <span class="chapter-coordinates">Curated Signature Itineraries</span>
        </div>
        <h2 class="editorial-headline-large">
          Handcrafted Journeys.<br>
          <span style="font-family:var(--font-editorial);font-style:italic;font-weight:400;color:var(--color-text-secondary);">
            Every detail verified with private chauffeur &amp; verified stays.
          </span>
        </h2>
      </div>

      <div>
        <a href="<?= BASE_URL ?>/packages.php" class="btn-cinematic-primary">
          <span>Complete Catalog</span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </a>
      </div>
    </div>

    <!-- Category Filter Pills -->
    <div class="filter-pills" style="margin-bottom:40px;">
      <a href="<?= BASE_URL ?>/packages.php" class="filter-pill is-active">All Itineraries</a>
      <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $cat): ?>
          <a href="<?= BASE_URL ?>/packages.php?category=<?= urlencode($cat['slug']) ?>" class="filter-pill">
            <?= e($cat['name']) ?>
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
     CHAPTER IV: THE SACRED CIRCUIT (PILGRIMAGE TOURS)
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
            <span>Sacred Vedic Circuit &bull; Tamil Nadu &amp; Kashi</span>
          </div>

          <h2 style="font-size:clamp(1.8rem, 3.2vw, 2.8rem);margin-bottom:16px;">
            Divine Destinations...<br>
            <span style="font-family:var(--font-editorial);font-style:italic;font-weight:400;color:var(--color-gold-deep);">
              A Journey of Deep Reverence &amp; Faith.
            </span>
          </h2>

          <p style="font-size:0.96rem;line-height:1.75;color:var(--color-text-secondary);margin-bottom:24px;">
            Experience seamlessly managed temple yatras across <strong>Sri Murugan Temple Thiruchendur</strong>, <strong>Sri Ramanathaswamy Rameswaram</strong>, <strong>Dhanushkodi</strong>, <strong>Brihadeeswarar Thanjavur</strong>, and <strong>Meenakshi Amman Madurai</strong>. Verified sanitised accommodations, elderly-first ground coordination, and zero hidden costs.
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
              <span>View Pilgrimage Circuit</span>
            </a>
            <a href="<?= e(build_whatsapp_url($agencyWhatsApp, "Hello MIDHILA VOYAGES, I would like to reserve seats for the upcoming Pilgrim Tour.")) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-solid">
              <span>Reserve via WhatsApp</span>
            </a>
          </div>
        </div>

        <!-- Featured Price Badge & Image Frame -->
        <div class="pilgrim-banner__right">
          <div style="background:#FFFFFF;border:2px solid rgba(197, 160, 89, 0.4);border-radius:var(--radius-lg);padding:32px 24px;box-shadow:var(--shadow-card);">
            <span style="font-size:0.75rem;letter-spacing:0.2em;text-transform:uppercase;color:var(--color-gold-deep);font-weight:700;">Special Yatra Package</span>
            <div style="font-size:3rem;font-weight:800;color:var(--color-text-headline);line-height:1;margin:12px 0 6px;">
              ₹6,500
            </div>
            <span style="font-size:0.82rem;color:var(--color-text-muted);display:block;margin-bottom:20px;">per person &bull; 5 Days / 4 Nights</span>
            
            <div style="background:#FAFAFA;border-radius:var(--radius-sm);padding:14px;font-size:0.82rem;color:var(--color-text-secondary);text-align:left;margin-bottom:20px;border:1px solid var(--color-border);">
              <strong>Upcoming Tour Dates:</strong> 23.12.26 to 27.12.26<br>
              <em style="color:var(--color-gold-deep);">Hurry, limited slots on first come, first served basis!</em>
            </div>

            <div style="font-size:0.84rem;color:var(--color-text-body);font-weight:600;">
              Hotlines: +91 8075021427, 9446154048
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
      <div class="chapter-badge" style="justify-content:center;">
        <span class="chapter-number">Chapter V &bull; The Craft</span>
        <span class="chapter-coordinates">Bespoke Concierge Philosophy</span>
      </div>
      <h2 class="editorial-headline-large">
        Hospitality Defined by Stillness, Depth &amp; Care.
      </h2>
      <p class="editorial-subtext" style="margin:0 auto;">
        We believe a journey should be deeply felt, never rushed.
      </p>
    </div>

    <!-- 4 Pillars Grid -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:32px;">
      
      <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:32px 28px;box-shadow:var(--shadow-ambient);">
        <div style="font-size:1.6rem;color:var(--color-gold-deep);margin-bottom:16px;">✦</div>
        <h3 style="font-size:1.15rem;margin-bottom:12px;">Handcrafted Routing</h3>
        <p style="font-size:0.88rem;color:var(--color-text-muted);line-height:1.7;">
          Every route is custom paced to balance sacred exploration, cultural depth, and gentle rest.
        </p>
      </div>

      <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:32px 28px;box-shadow:var(--shadow-ambient);">
        <div style="font-size:1.6rem;color:var(--color-gold-deep);margin-bottom:16px;">❖</div>
        <h3 style="font-size:1.15rem;margin-bottom:12px;">Verified Sanctuaries</h3>
        <p style="font-size:0.88rem;color:var(--color-text-muted);line-height:1.7;">
          Personally vetted heritage resorts, clean temple guest houses, and scenic boutique retreats.
        </p>
      </div>

      <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:32px 28px;box-shadow:var(--shadow-ambient);">
        <div style="font-size:1.6rem;color:var(--color-gold-deep);margin-bottom:16px;">◈</div>
        <h3 style="font-size:1.15rem;margin-bottom:12px;">Dedicated Chauffeurs</h3>
        <p style="font-size:0.88rem;color:var(--color-text-muted);line-height:1.7;">
          Courteous, verified regional drivers with well-maintained AC fleets ensuring seamless road transit.
        </p>
      </div>

      <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:32px 28px;box-shadow:var(--shadow-ambient);">
        <div style="font-size:1.6rem;color:var(--color-gold-deep);margin-bottom:16px;">❂</div>
        <h3 style="font-size:1.15rem;margin-bottom:12px;">Transparent Integrity</h3>
        <p style="font-size:0.88rem;color:var(--color-text-muted);line-height:1.7;">
          Zero hidden surcharges, honest pricing, and complete written inclusions on every voucher.
        </p>
      </div>

    </div>

  </div>
</section>

<div class="section-divider-hairline"></div>

<!-- =====================================================================
     CHAPTER VI: EXPLORER MEMOIRS & TRUST
     ===================================================================== -->
<?php if (!empty($testimonials)): ?>
<section class="section-padding" style="position:relative;background:#FFFFFF;">
  <div class="container">
    
    <div style="text-align:center;max-width:700px;margin:0 auto 50px;">
      <div class="chapter-badge" style="justify-content:center;">
        <span class="chapter-number">Chapter VI &bull; Memoirs</span>
        <span class="chapter-coordinates">Voices of Pilgrims &amp; Voyagers</span>
      </div>
      <h2 class="editorial-headline-large">Journeys Remembered.</h2>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:28px;">
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
            <span style="font-size:0.72rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--color-gold-deep);font-weight:700;">Verified Journey</span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>
<div class="section-divider-hairline"></div>
<?php endif; ?>

<!-- =====================================================================
     CHAPTER VII: COMMENCE YOUR JOURNEY (ENQUIRY CONCIERGE)
     ===================================================================== -->
<section class="section-padding" style="position:relative;background:#FFFFFF;">
  <div class="container container-narrow">
    
    <div class="editorial-form-panel">
      <div style="text-align:center;margin-bottom:36px;">
        <div class="chapter-badge" style="justify-content:center;">
          <span class="chapter-number">Chapter VII &bull; The Horizon</span>
          <span class="chapter-coordinates">Bespoke Travel Inquiry</span>
        </div>
        <h2 style="font-size:clamp(1.8rem, 3.2vw, 2.6rem);margin-bottom:12px;">Plan Your Bespoke Journey</h2>
        <p style="font-size:0.95rem;color:var(--color-text-secondary);max-width:540px;margin:0 auto;">
          Tell our travel specialists your preferred destinations, dates, and rhythm. We respond within hours with a tailored itinerary.
        </p>
      </div>

      <form action="<?= BASE_URL ?>/enquiry.php" method="POST">
        <?= csrf_field() ?>

        <div class="form-row-2col">
          <div class="editorial-form-group">
            <label class="editorial-label">Your Name *</label>
            <input type="text" name="name" class="editorial-input" placeholder="e.g. Adarsh" required>
          </div>

          <div class="editorial-form-group">
            <label class="editorial-label">Phone / WhatsApp Number *</label>
            <input type="tel" name="phone" class="editorial-input" placeholder="+91 98765 43210" required>
          </div>
        </div>

        <div class="form-row-2col">
          <div class="editorial-form-group">
            <label class="editorial-label">Email Address *</label>
            <input type="email" name="email" class="editorial-input" placeholder="you@domain.com" required>
          </div>

          <div class="editorial-form-group">
            <label class="editorial-label">Preferred Destination</label>
            <select name="destination" class="editorial-select">
              <option value="">Select Destination</option>
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
          <label class="editorial-label">Travel Notes / Preferences</label>
          <textarea name="message" class="editorial-textarea" rows="3" placeholder="Tell us about travelers, expected dates, and any special accommodations..."></textarea>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-top:28px;">
          <button type="submit" class="btn-cinematic-primary" style="padding:14px 36px;">
            <span>Submit Travel Request</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
          </button>

          <span style="font-size:0.8rem;color:var(--color-text-muted);">
            Prefer instant messaging? <a href="<?= e(build_whatsapp_url($agencyWhatsApp, "Hello MIDHILA VOYAGES, I would like to enquire about travel packages.")) ?>" target="_blank" rel="noopener noreferrer" style="color:var(--color-whatsapp);font-weight:700;">Chat on WhatsApp &rarr;</a>
          </span>
        </div>
      </form>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
