<?php
/**
 * MIDHILA VOYAGES — Tour Package Details & Itinerary
 * Pure White Editorial Hospitality Canvas
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$slug = $_GET['slug'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$package = null;
if (!empty($slug)) {
    $stmt = $db->prepare("
        SELECT p.*, d.name AS destination_name, d.region_state, d.slug AS destination_slug,
               c.name AS category_name, c.slug AS category_slug
        FROM `packages` p
        JOIN `destinations` d ON p.destination_id = d.id
        JOIN `package_categories` c ON p.category_id = c.id
        WHERE p.slug = ? AND p.is_published = 1 LIMIT 1
    ");
    $stmt->execute([$slug]);
    $package = $stmt->fetch();
} elseif ($id > 0) {
    $stmt = $db->prepare("
        SELECT p.*, d.name AS destination_name, d.region_state, d.slug AS destination_slug,
               c.name AS category_name, c.slug AS category_slug
        FROM `packages` p
        JOIN `destinations` d ON p.destination_id = d.id
        JOIN `package_categories` c ON p.category_id = c.id
        WHERE p.id = ? AND p.is_published = 1 LIMIT 1
    ");
    $stmt->execute([$id]);
    $package = $stmt->fetch();
}

if (!$package) {
    http_response_code(404);
    $pageTitle = 'Package Not Found | MIDHILA VOYAGES';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container section-padding" style="text-align:center;background:#FFFFFF;">';
    echo '<h1 class="editorial-headline-large">Package Not Found</h1>';
    echo '<p class="editorial-subtext" style="margin:16px auto 28px;">The tour package you requested may have been archived or updated.</p>';
    echo '<a href="' . BASE_URL . '/packages.php" class="btn-cinematic-primary">Browse All Packages</a>';
    echo '</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = e($package['title']) . ' (' . ($package['duration_days'] ?? 1) . 'D/' . ($package['duration_nights'] ?? 0) . 'N) | Midhila Voyages';
$pageDescription = e($package['meta_description'] ?: substr(strip_tags($package['short_description']), 0, 160));

// Fetch Itinerary Days
$itinStmt = $db->prepare("SELECT * FROM `package_itineraries` WHERE `package_id` = ? ORDER BY `day_number` ASC");
$itinStmt->execute([$package['id']]);
$itineraries = $itinStmt->fetchAll();

// Fetch Supplementary Package Gallery Images
$pkgImgStmt = $db->prepare("SELECT * FROM `package_images` WHERE `package_id` = ? ORDER BY `display_order` ASC, `id` ASC");
$pkgImgStmt->execute([$package['id']]);
$packageGallery = $pkgImgStmt->fetchAll();

// Fetch Related Packages
$relStmt = $db->prepare("
    SELECT p.*, d.name AS destination_name, d.region_state, c.name AS category_name, c.slug AS category_slug
    FROM `packages` p
    JOIN `destinations` d ON p.destination_id = d.id
    JOIN `package_categories` c ON p.category_id = c.id
    WHERE p.id != ? AND (p.destination_id = ? OR p.category_id = ?) AND p.is_published = 1
    LIMIT 3
");
$relStmt->execute([$package['id'], $package['destination_id'], $package['category_id']]);
$relatedPackages = $relStmt->fetchAll();

$agencyWhatsApp = get_setting('whatsapp_number', '+918075021427');
$durationText = ($package['duration_days'] ?? 1) . ' Days / ' . ($package['duration_nights'] ?? 0) . ' Nights';

$waMsg = "Hello MIDHILA VOYAGES, I would like to book or enquire about the \"{$package['title']}\" package ({$durationText}). Please share availability and customized quotation.";
$waPkgUrl = build_whatsapp_url($agencyWhatsApp, $waMsg);

$inclusions = !empty($package['inclusions']) ? array_map('trim', explode(',', $package['inclusions'])) : [];
$exclusions = !empty($package['exclusions']) ? array_map('trim', explode(',', $package['exclusions'])) : [];

require_once __DIR__ . '/includes/header.php';
?>

<!-- Package Cinematic Hero (Pure White with 35% Subtle Destination Underlayer) -->
<section style="position:relative;background:#FFFFFF;padding-top:calc(var(--header-height) + 40px);padding-bottom:50px;overflow:hidden;border-bottom:1px solid var(--color-border);">
  
  <div class="cinematic-bg-layer cinematic-bg-mask-radial" aria-hidden="true">
    <img src="<?= e(get_image_url($package['featured_image'])) ?>" 
         alt="" 
         class="cinematic-bg-layer__img" 
         style="opacity:0.25;">
  </div>

  <div class="container" style="position:relative;z-index:2;">
    <!-- Breadcrumb -->
    <nav aria-label="Breadcrumb" style="margin-bottom:16px;font-size:0.82rem;display:flex;align-items:center;gap:8px;color:var(--color-text-muted);">
      <a href="<?= BASE_URL ?>/index.php" style="color:var(--color-text-secondary);">Home</a> <span>/</span>
      <a href="<?= BASE_URL ?>/packages.php" style="color:var(--color-text-secondary);">Packages</a> <span>/</span>
      <a href="<?= BASE_URL ?>/destination.php?slug=<?= e($package['destination_slug']) ?>" style="color:var(--color-text-secondary);"><?= e($package['destination_name']) ?></a> <span>/</span>
      <span style="color:var(--color-text-main);font-weight:700;"><?= e($package['title']) ?></span>
    </nav>

    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
      <span class="chapter-number">
        <?= e($package['category_name']) ?>
      </span>
      <span style="font-size:0.8rem;background:#FAFAFA;border:1px solid var(--color-border);padding:4px 14px;border-radius:var(--radius-pill);color:var(--color-text-secondary);font-weight:600;">
        ⏱ <?= e($durationText) ?>
      </span>
      <?php if (!empty($package['is_pilgrimage'])): ?>
        <span style="font-size:0.8rem;background:rgba(197,160,89,0.1);border:1px solid rgba(197,160,89,0.3);padding:4px 14px;border-radius:var(--radius-pill);color:var(--color-gold-deep);font-weight:700;">
          🕉 Sacred Pilgrimage
        </span>
      <?php endif; ?>
    </div>

    <h1 class="editorial-headline-large" style="margin-bottom:12px;">
      <?= e($package['title']) ?>
    </h1>
    
    <?php if (!empty($package['subtitle'])): ?>
      <p class="editorial-subtext" style="max-width:820px;"><?= e($package['subtitle']) ?></p>
    <?php endif; ?>
  </div>
</section>

<!-- Package Overview, Itinerary & Sticky Booking Sidebar -->
<section style="background:#FFFFFF;padding-top:60px;padding-bottom:100px;position:relative;">
  <div class="container">
    
    <div style="display:grid;grid-template-columns:1fr 380px;gap:48px;align-items:start;">
      
      <!-- Left Column: Details & Itinerary Breakdown -->
      <div>
        
        <!-- Large High-Fidelity Photo for 100% Inspection -->
        <div style="width:100%;height:440px;border-radius:var(--radius-md);overflow:hidden;border:1px solid var(--color-border);box-shadow:var(--shadow-card);margin-bottom:40px;background:#F8FAFC;">
          <img src="<?= e(get_image_url($package['featured_image'])) ?>" 
               alt="<?= e($package['alt_text'] ?: $package['title']) ?>" 
               style="width:100%;height:100%;object-fit:cover;opacity:1 !important;" 
               onerror="this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
        </div>

        <!-- Tour Overview Card -->
        <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:36px;box-shadow:var(--shadow-ambient);margin-bottom:44px;">
          <h2 style="font-size:1.5rem;margin-bottom:16px;">The Journey Synopsis</h2>
          <p style="font-size:1rem;line-height:1.85;color:var(--color-text-body);margin-bottom:20px;">
            <?= nl2br(e($package['overview'] ?: $package['short_description'])) ?>
          </p>
          <?php if (!empty($package['seasonal_info'])): ?>
            <div style="display:inline-flex;align-items:center;gap:10px;padding:10px 18px;background:#FAFAFA;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.86rem;color:var(--color-text-secondary);">
              <span>🌤</span>
              <span><strong>Optimal Seasonality:</strong> <?= e($package['seasonal_info']) ?></span>
            </div>
          <?php endif; ?>
        </div>

        <!-- Day-by-Day Itinerary -->
        <div style="margin-bottom:44px;">
          <div class="chapter-badge">
            <span class="chapter-number">Route Breakdown</span>
            <span class="chapter-coordinates">Sequential Day Plans</span>
          </div>
          <h2 class="editorial-headline-large" style="font-size:2rem;margin-bottom:24px;">Day-by-Day Itinerary</h2>

          <?php if (empty($itineraries)): ?>
            <div style="padding:28px;border:1px dashed var(--color-border);border-radius:var(--radius-md);text-align:center;background:#FAFAFA;">
              <p style="color:var(--color-text-muted);">A day-by-day customized sequence will be provided upon enquiry by our concierge team.</p>
            </div>
          <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:20px;">
              <?php foreach ($itineraries as $itin): ?>
                <div style="background:#FFFFFF;border:1px solid var(--color-border);border-left:4px solid var(--color-gold-deep);border-radius:var(--radius-md);padding:28px;box-shadow:var(--shadow-ambient);">
                  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
                    <span style="font-family:var(--font-serif);font-size:0.85rem;font-weight:700;color:var(--color-gold-deep);letter-spacing:0.1em;text-transform:uppercase;">
                      DAY <?= str_pad($itin['day_number'], 2, '0', STR_PAD_LEFT) ?>
                    </span>
                    <div style="display:flex;gap:14px;font-size:0.82rem;color:var(--color-text-muted);">
                      <?php if (!empty($itin['meals_included'])): ?>
                        <span>🍽 Meals: <strong><?= e($itin['meals_included']) ?></strong></span>
                      <?php endif; ?>
                      <?php if (!empty($itin['stay_location'])): ?>
                        <span>🏨 Stay: <strong><?= e($itin['stay_location']) ?></strong></span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <h3 style="font-size:1.25rem;margin-bottom:10px;"><?= e($itin['title']) ?></h3>
                  <p style="font-size:0.95rem;line-height:1.75;color:var(--color-text-secondary);"><?= nl2br(e($itin['description'])) ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Inclusions & Exclusions Grid -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:44px;">
          <!-- Inclusions -->
          <div style="background:#FFFFFF;border:1px solid rgba(16, 124, 65, 0.25);border-radius:var(--radius-md);padding:28px;box-shadow:var(--shadow-ambient);">
            <h3 style="font-size:1.15rem;color:#0F5132;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
              <span>✓ What Is Included</span>
            </h3>
            <ul style="display:flex;flex-direction:column;gap:10px;font-size:0.9rem;color:var(--color-text-body);">
              <?php foreach ($inclusions as $inc): ?>
                <li style="display:flex;align-items:flex-start;gap:8px;">
                  <span style="color:#0F5132;font-weight:700;">•</span>
                  <span><?= e($inc) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

          <!-- Exclusions -->
          <div style="background:#FFFFFF;border:1px solid rgba(185, 28, 28, 0.25);border-radius:var(--radius-md);padding:28px;box-shadow:var(--shadow-ambient);">
            <h3 style="font-size:1.15rem;color:#991B1B;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
              <span>✕ What Is Not Included</span>
            </h3>
            <ul style="display:flex;flex-direction:column;gap:10px;font-size:0.9rem;color:var(--color-text-body);">
              <?php foreach ($exclusions as $exc): ?>
                <li style="display:flex;align-items:flex-start;gap:8px;">
                  <span style="color:#991B1B;font-weight:700;">•</span>
                  <span><?= e($exc) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

        <!-- Package Gallery (100% Opacity Photography Inspection) -->
        <?php if (!empty($packageGallery)): ?>
          <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:32px;box-shadow:var(--shadow-ambient);margin-bottom:44px;">
            <h3 style="font-size:1.3rem;margin-bottom:18px;">Accommodations &amp; Scenery Gallery</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:16px;">
              <?php foreach ($packageGallery as $pgImg): ?>
                <div style="border-radius:var(--radius-sm);overflow:hidden;border:1px solid var(--color-border);aspect-ratio:4/3;background:#FAFAFA;">
                  <img src="<?= e(get_image_url($pgImg['image_url'])) ?>" 
                       alt="<?= e($pgImg['caption'] ?: $package['title']) ?>" 
                       style="width:100%;height:100%;object-fit:cover;opacity:1 !important;transition:transform 0.4s ease;" 
                       onmouseover="this.style.transform='scale(1.05)'" 
                       onmouseout="this.style.transform='scale(1)'">
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

      </div>

      <!-- Right Column: Sticky Reservation Box -->
      <div>
        <div style="position:sticky;top:100px;background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:34px 28px;box-shadow:var(--shadow-card);">
          
          <div style="margin-bottom:24px;border-bottom:1px solid var(--color-border);padding-bottom:20px;">
            <span style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-text-muted);font-weight:700;">Package Tariff</span>
            <div style="margin-top:8px;">
              <?php if (empty($package['is_price_on_request']) && !empty($package['starting_price'])): ?>
                <span style="font-size:2.4rem;font-weight:800;color:var(--color-text-headline);line-height:1;">
                  <?= format_price((float)$package['starting_price'], $package['currency'] ?? 'INR') ?>
                </span>
                <span style="font-size:0.82rem;color:var(--color-text-muted);display:block;margin-top:4px;">per person &bull; twin sharing</span>
              <?php else: ?>
                <span style="font-size:1.8rem;font-weight:700;color:var(--color-gold-deep);">
                  Custom Quote
                </span>
                <p style="font-size:0.82rem;color:var(--color-text-muted);margin-top:4px;">Tailored based on travel dates &amp; hotel choice.</p>
              <?php endif; ?>
            </div>
          </div>

          <div style="display:flex;flex-direction:column;gap:12px;">
            <a href="<?= BASE_URL ?>/booking.php?package_id=<?= (int)$package['id'] ?>" class="btn-cinematic-primary" style="width:100%;justify-content:center;">
              <span>Reserve Journey Online</span>
            </a>

            <a href="<?= e($waPkgUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-solid" style="width:100%;justify-content:center;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 2.11.83 3.292.83 3.183 0 5.768-2.587 5.768-5.766.001-3.181-2.585-5.767-5.764-5.767zm7.653 5.766c0 4.225-3.438 7.663-7.654 7.663-1.309 0-2.569-.334-3.674-.925l-4.148 1.088 1.107-4.041c-.696-1.187-1.077-2.55-1.077-3.957 0-4.224 3.438-7.662 7.654-7.662 4.226 0 7.662 3.438 7.662 7.662z"/></svg>
              <span>Instant WhatsApp Enquiry</span>
            </a>

            <a href="<?= BASE_URL ?>/enquiry.php?package_id=<?= (int)$package['id'] ?>&destination=<?= urlencode($package['destination_name']) ?>" class="btn-cinematic-outline" style="width:100%;justify-content:center;">
              <span>Request Custom Proposal</span>
            </a>
          </div>

          <div style="margin-top:24px;border-top:1px solid var(--color-border);padding-top:20px;font-size:0.84rem;color:var(--color-text-secondary);display:flex;flex-direction:column;gap:10px;">
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="color:var(--color-gold-deep);font-weight:700;">✓</span>
              <span>100% Tailored Private Route</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="color:var(--color-gold-deep);font-weight:700;">✓</span>
              <span>Dedicated AC Chauffeured Fleet</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="color:var(--color-gold-deep);font-weight:700;">✓</span>
              <span>Transparent Written Vouchers</span>
            </div>
          </div>

        </div>
      </div>

    </div>

  </div>
</section>

<!-- Related Tour Packages (Pure White Surface) -->
<?php if (!empty($relatedPackages)): ?>
<div class="section-divider-hairline"></div>
<section class="section-padding" style="background:#FFFFFF;">
  <div class="container">
    <div style="margin-bottom:40px;">
      <div class="chapter-badge">
        <span class="chapter-number">Companion Routes</span>
        <span class="chapter-coordinates">Related Itineraries</span>
      </div>
      <h2 class="editorial-headline-large" style="font-size:2.2rem;">You May Also Appreciate</h2>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(350px, 1fr));gap:32px;">
      <?php foreach ($relatedPackages as $package): ?>
        <?php include __DIR__ . '/includes/package-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
