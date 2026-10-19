<?php
/**
 * MIDHILA VOYAGES — Tour Package Details & Itinerary
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
    echo '<div class="container section-padding" style="text-align:center;">';
    echo '<h1 class="section-title">Package Not Found</h1>';
    echo '<p class="lead-text" style="margin-bottom:24px;">The tour package you requested may have been archived or is not available.</p>';
    echo '<a href="' . BASE_URL . '/packages.php" class="btn btn-primary">Browse All Packages</a>';
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

// Fetch Related Packages in same category or destination
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

$agencyWhatsApp = get_setting('whatsapp_number', '+911234567890');
$durationText = ($package['duration_days'] ?? 1) . ' Days / ' . ($package['duration_nights'] ?? 0) . ' Nights';

$waMsg = "Hello MIDHILA VOYAGES, I would like to book or enquire about the \"{$package['title']}\" package ({$durationText}). Please share availability and customized quotation.";
$waPkgUrl = build_whatsapp_url($agencyWhatsApp, $waMsg);

$inclusions = !empty($package['inclusions']) ? array_map('trim', explode(',', $package['inclusions'])) : [];
$exclusions = !empty($package['exclusions']) ? array_map('trim', explode(',', $package['exclusions'])) : [];

require_once __DIR__ . '/includes/header.php';
?>

<!-- Package Cinematic Hero -->
<section style="position:relative;min-height:58vh;background:linear-gradient(180deg, rgba(6,11,24,0.9) 0%, rgba(10,17,40,0.85) 100%);display:flex;align-items:flex-end;padding-top:calc(var(--header-height) + 40px);padding-bottom:56px;color:#FFF;overflow:hidden;border-bottom:1px solid rgba(196,181,253,0.18);">
  <div style="position:absolute;inset:0;">
    <img src="<?= e(get_image_url($package['featured_image'])) ?>" alt="<?= e($package['alt_text'] ?: $package['title']) ?>" style="width:100%;height:100%;object-fit:cover;opacity:0.4;transform:scale(1.03);transition:transform 8s ease;" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
    <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(6,11,24,0.45) 0%, rgba(6,11,24,0.85) 75%, var(--color-navy-deep) 100%);"></div>
  </div>

  <div class="container" style="position:relative;z-index:2;">
    <!-- Breadcrumb -->
    <nav aria-label="Breadcrumb" style="margin-bottom:14px;font-size:0.85rem;display:flex;align-items:center;gap:8px;">
      <a href="<?= BASE_URL ?>/index.php" style="color:var(--color-periwinkle);transition:color 0.2s;">Home</a> <span style="opacity:0.5;">/</span>
      <a href="<?= BASE_URL ?>/packages.php" style="color:var(--color-periwinkle);transition:color 0.2s;">Packages</a> <span style="opacity:0.5;">/</span>
      <a href="<?= BASE_URL ?>/destination.php?slug=<?= e($package['destination_slug']) ?>" style="color:var(--color-periwinkle);transition:color 0.2s;"><?= e($package['destination_name']) ?></a> <span style="opacity:0.5;">/</span>
      <span style="color:#FFF;font-weight:600;"><?= e($package['title']) ?></span>
    </nav>

    <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;flex-wrap:wrap;">
      <span style="background:rgba(196,181,253,0.18);backdrop-filter:blur(16px);padding:5px 16px;border-radius:var(--radius-pill);border:1px solid rgba(196,181,253,0.35);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.12em;color:var(--color-icy);font-weight:700;box-shadow:0 0 16px rgba(139,92,246,0.25);">
        ✦ <?= e($package['category_name']) ?>
      </span>
      <span style="background:rgba(141,164,239,0.2);backdrop-filter:blur(12px);color:var(--color-periwinkle);padding:5px 16px;border-radius:var(--radius-pill);border:1px solid rgba(141,164,239,0.35);font-size:0.75rem;font-weight:600;">
        ⏱ <?= e($durationText) ?>
      </span>
      <?php if (!empty($package['is_pilgrimage'])): ?>
        <span style="background:rgba(197,160,89,0.25);backdrop-filter:blur(12px);color:var(--color-gold-light);padding:5px 16px;border-radius:var(--radius-pill);border:1px solid rgba(197,160,89,0.4);font-size:0.75rem;font-weight:600;">
          🕉 Sacred Pilgrimage
        </span>
      <?php endif; ?>
    </div>

    <h1 class="editorial-headline" style="color:#FFF;margin-bottom:12px;font-size:clamp(2.3rem, 4.5vw, 3.6rem);text-shadow:0 4px 16px rgba(0,0,0,0.6);">
      <?= e($package['title']) ?>
    </h1>
    <?php if (!empty($package['subtitle'])): ?>
      <p class="lead-text" style="color:#E2E8F0;max-width:760px;font-size:1.15rem;line-height:1.6;text-shadow:0 2px 8px rgba(0,0,0,0.5);"><?= e($package['subtitle']) ?></p>
    <?php endif; ?>
  </div>
</section>

<!-- Package Overview & Sidebar -->
<section class="section-padding" style="background:transparent;position:relative;z-index:2;">
  <div class="container">
    <div class="detail-layout-grid">
      
      <!-- Main Content Details -->
      <div>
        
        <!-- Short Synopsis -->
        <div class="glass-card glass-specular" style="background:var(--glass-surface-card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);padding:36px;border-radius:24px;border:1px solid var(--glass-border);box-shadow:var(--glass-inner-highlight), var(--shadow-md);margin-bottom:36px;">
          <h2 style="font-size:1.55rem;color:var(--color-navy);margin-bottom:16px;font-weight:700;">Tour Overview</h2>
          <p style="font-size:1.04rem;line-height:1.85;color:var(--color-text-body);margin-bottom:20px;">
            <?= nl2br(e($package['overview'] ?: $package['short_description'])) ?>
          </p>
          <?php if (!empty($package['seasonal_info'])): ?>
            <div style="display:inline-flex;align-items:center;gap:10px;padding:12px 20px;background:rgba(196,181,253,0.18);border-radius:14px;border:1px solid rgba(196,181,253,0.35);font-size:0.92rem;color:var(--color-navy);">
              <span style="font-size:1.2rem;">🌤</span>
              <span><strong>Optimal Seasonality:</strong> <?= e($package['seasonal_info']) ?></span>
            </div>
          <?php endif; ?>
        </div>

        <!-- Day-by-Day Itinerary Breakdown -->
        <div style="margin-bottom:44px;">
          <h2 class="section-title" style="font-size:1.85rem;margin-bottom:24px;">Day-by-Day Itinerary</h2>
          
          <?php if (empty($itineraries)): ?>
            <div class="glass-card" style="padding:24px;border-radius:18px;background:var(--glass-surface-card);text-align:center;">
              <p style="color:var(--color-text-muted);">Detailed itinerary will be provided upon enquiry by our travel designers.</p>
            </div>
          <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:20px;">
              <?php foreach ($itineraries as $itin): ?>
                <div class="glass-card card-3d glass-specular" style="background:var(--glass-surface-card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);border:1px solid var(--glass-border);border-radius:20px;padding:26px 28px;box-shadow:var(--glass-inner-highlight), var(--shadow-md);border-left:5px solid var(--color-violet);">
                  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
                    <span style="background:linear-gradient(135deg, #7C3AED 0%, #4F46E5 100%);color:#FFF;font-size:0.75rem;font-weight:700;padding:4px 14px;border-radius:var(--radius-pill);box-shadow:0 4px 12px rgba(124,58,237,0.3);">
                      DAY <?= e($itin['day_number']) ?>
                    </span>
                    <div style="display:flex;gap:14px;font-size:0.85rem;color:var(--color-text-muted);">
                      <?php if (!empty($itin['meals_included'])): ?>
                        <span>🍽 Meals: <strong><?= e($itin['meals_included']) ?></strong></span>
                      <?php endif; ?>
                      <?php if (!empty($itin['stay_location'])): ?>
                        <span>🏨 Stay: <strong><?= e($itin['stay_location']) ?></strong></span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <h3 style="font-size:1.25rem;color:var(--color-navy);margin-bottom:10px;font-weight:700;"><?= e($itin['title']) ?></h3>
                  <p style="font-size:0.96rem;line-height:1.8;color:var(--color-text-body);"><?= nl2br(e($itin['description'])) ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Inclusions & Exclusions Grid -->
        <div class="form-grid-2" style="margin-bottom:40px;display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:24px;">
          
          <!-- Inclusions -->
          <div class="glass-card glass-specular" style="background:var(--glass-surface-card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);padding:30px;border-radius:22px;border:1px solid rgba(74,222,128,0.4);box-shadow:var(--glass-inner-highlight), var(--shadow-md);">
            <h3 style="font-size:1.2rem;color:#86EFAC !important;margin-bottom:18px;display:flex;align-items:center;gap:8px;font-weight:700;">
              <span>✓ What Is Included</span>
            </h3>
            <ul style="display:flex;flex-direction:column;gap:12px;font-size:0.92rem;color:#F1F5F9;">
              <?php foreach ($inclusions as $inc): ?>
                <li style="display:flex;align-items:flex-start;gap:10px;">
                  <span style="color:#86EFAC;font-weight:700;">•</span>
                  <span><?= e($inc) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

          <!-- Exclusions -->
          <div class="glass-card glass-specular" style="background:var(--glass-surface-card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);padding:30px;border-radius:22px;border:1px solid rgba(248,113,113,0.4);box-shadow:var(--glass-inner-highlight), var(--shadow-md);">
            <h3 style="font-size:1.2rem;color:#FCA5A5 !important;margin-bottom:18px;display:flex;align-items:center;gap:8px;font-weight:700;">
              <span>✕ What Is Not Included</span>
            </h3>
            <ul style="display:flex;flex-direction:column;gap:12px;font-size:0.92rem;color:#F1F5F9;">
              <?php foreach ($exclusions as $exc): ?>
                <li style="display:flex;align-items:flex-start;gap:10px;">
                  <span style="color:#FCA5A5;font-weight:700;">•</span>
                  <span><?= e($exc) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

        </div>

        <!-- Tour Photo Gallery (if supplementary images exist) -->
        <?php if (!empty($packageGallery)): ?>
          <div class="glass-card glass-specular" style="background:var(--glass-surface-card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);padding:32px;border-radius:22px;border:1px solid var(--glass-border);margin-bottom:40px;box-shadow:var(--glass-inner-highlight), var(--shadow-md);">
            <h3 style="font-size:1.35rem;color:var(--color-navy);margin-bottom:18px;font-weight:700;">Tour Photography Gallery</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:16px;">
              <?php foreach ($packageGallery as $pgImg): ?>
                <div style="border-radius:14px;overflow:hidden;box-shadow:0 6px 18px rgba(0,0,0,0.1);aspect-ratio:4/3;background:var(--color-navy-deep);border:1px solid rgba(255,255,255,0.4);">
                  <img src="<?= e(get_image_url($pgImg['image_url'])) ?>" alt="<?= e($pgImg['caption'] ?: $package['title']) ?>" style="width:100%;height:100%;object-fit:cover;transition:transform 0.5s ease;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'" onerror="this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

      </div>

      <!-- Sticky Sidebar Booking Box -->
      <div>
        <div class="glass-card card-3d glass-specular" style="position:sticky;top:100px;background:var(--glass-surface-card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);padding:34px 28px;border-radius:24px;box-shadow:var(--glass-inner-highlight), var(--shadow-xl);border:1px solid var(--glass-border);">
          
          <div style="margin-bottom:22px;border-bottom:1px solid rgba(196,181,253,0.25);padding-bottom:18px;">
            <span style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-text-muted);font-weight:700;">Package Tariff</span>
            <div style="margin-top:6px;">
              <?php if (empty($package['is_price_on_request']) && !empty($package['starting_price'])): ?>
                <span style="font-family:var(--font-serif);font-size:2.2rem;font-weight:700;color:var(--color-navy);">
                  <?= format_price((float)$package['starting_price'], $package['currency'] ?? 'INR') ?>
                </span>
                <span style="font-size:0.85rem;color:var(--color-text-muted);display:block;margin-top:2px;">per person on twin sharing</span>
              <?php else: ?>
                <span style="font-family:var(--font-serif);font-size:1.7rem;font-weight:700;color:var(--color-violet);">
                  Request a Quote
                </span>
                <p style="font-size:0.82rem;color:var(--color-text-muted);margin-top:4px;">Customized based on selected dates and hotel category.</p>
              <?php endif; ?>
            </div>
          </div>

          <div style="display:flex;flex-direction:column;gap:12px;">
            <!-- Book Now / Pay Deposit Button -->
            <a href="<?= BASE_URL ?>/booking.php?package_id=<?= (int)$package['id'] ?>" class="btn btn-primary" style="width:100%;justify-content:center;">
              <span>Book / Reserve Trip</span>
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </a>

            <!-- WhatsApp Direct Enquiry -->
            <a href="<?= e($waPkgUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-teal" style="width:100%;justify-content:center;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 2.11.83 3.292.83 3.183 0 5.768-2.587 5.768-5.766.001-3.181-2.585-5.767-5.764-5.767zm7.653 5.766c0 4.225-3.438 7.663-7.654 7.663-1.309 0-2.569-.334-3.674-.925l-4.148 1.088 1.107-4.041c-.696-1.187-1.077-2.55-1.077-3.957 0-4.224 3.438-7.662 7.654-7.662 4.226 0 7.662 3.438 7.662 7.662z"/></svg>
              <span>Enquire on WhatsApp</span>
            </a>

            <!-- Custom Form Enquiry -->
            <a href="<?= BASE_URL ?>/enquiry.php?package_id=<?= (int)$package['id'] ?>&destination=<?= urlencode($package['destination_name']) ?>" class="btn btn-outline-white" style="width:100%;justify-content:center;color:var(--color-navy);border-color:rgba(196,181,253,0.5);">
              <span>Custom Proposal Enquiry</span>
            </a>
          </div>

          <div style="margin-top:24px;border-top:1px solid rgba(196,181,253,0.25);padding-top:18px;font-size:0.84rem;color:var(--color-text-muted);display:flex;flex-direction:column;gap:10px;">
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="color:var(--color-violet);font-weight:700;">✓</span>
              <span>100% Customizable Itinerary</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="color:var(--color-violet);font-weight:700;">✓</span>
              <span>Sanitized Private AC Transport</span>
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
              <span style="color:var(--color-teal);">✓</span>
              <span>Verified Hotel Accommodations</span>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</section>

<!-- Related Tour Packages -->
<?php if (!empty($relatedPackages)): ?>
<section class="section-padding section-dark">
  <div class="container">
    <div class="section-header">
      <span class="section-subtitle">More Expeditions</span>
      <h2 class="section-title">You May Also Like</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:28px;">
      <?php foreach ($relatedPackages as $package): ?>
        <?php include __DIR__ . '/includes/package-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
