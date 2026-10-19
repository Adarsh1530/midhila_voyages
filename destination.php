<?php
/**
 * MIDHILA VOYAGES — Destination Details Showcase
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$slug = $_GET['slug'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$dest = null;
if (!empty($slug)) {
    $stmt = $db->prepare("SELECT * FROM `destinations` WHERE `slug` = ? AND `is_published` = 1 LIMIT 1");
    $stmt->execute([$slug]);
    $dest = $stmt->fetch();
} elseif ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM `destinations` WHERE `id` = ? AND `is_published` = 1 LIMIT 1");
    $stmt->execute([$id]);
    $dest = $stmt->fetch();
}

if (!$dest) {
    http_response_code(404);
    $pageTitle = 'Destination Not Found | MIDHILA VOYAGES';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container section-padding" style="text-align:center;">';
    echo '<h1 class="section-title">Destination Not Found</h1>';
    echo '<p class="lead-text" style="margin-bottom:24px;">The destination you are looking for may have been moved or is currently not published.</p>';
    echo '<a href="' . BASE_URL . '/destinations.php" class="btn btn-primary">Browse All Destinations</a>';
    echo '</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = e($dest['name']) . ' | Destination Guide & Tour Packages | Midhila Voyages';
$pageDescription = e($dest['meta_description'] ?: substr(strip_tags($dest['description']), 0, 160));

// Fetch packages belonging to this destination
$pkgStmt = $db->prepare("
    SELECT p.*, d.name AS destination_name, d.region_state, c.name AS category_name, c.slug AS category_slug
    FROM `packages` p
    JOIN `destinations` d ON p.destination_id = d.id
    JOIN `package_categories` c ON p.category_id = c.id
    WHERE p.destination_id = ? AND p.is_published = 1
    ORDER BY p.is_featured DESC, p.display_order ASC
");
$pkgStmt->execute([$dest['id']]);
$packages = $pkgStmt->fetchAll();

$highlights = !empty($dest['highlights']) ? array_map('trim', explode(',', $dest['highlights'])) : [];
$agencyWhatsApp = get_setting('whatsapp_number', '+911234567890');

$waDestMsg = "Hello MIDHILA VOYAGES, I am interested in visiting {$dest['name']}. Please share available tour packages and customized itinerary options.";
$waDestUrl = build_whatsapp_url($agencyWhatsApp, $waDestMsg);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Destination Cinematic Hero -->
<section style="position:relative;min-height:58vh;background:linear-gradient(180deg, rgba(6,11,24,0.9) 0%, rgba(10,17,40,0.85) 100%);display:flex;align-items:flex-end;padding-top:calc(var(--header-height) + 40px);padding-bottom:56px;color:#FFF;overflow:hidden;border-bottom:1px solid rgba(196,181,253,0.18);">
  <div style="position:absolute;inset:0;">
    <img src="<?= e(get_image_url($dest['featured_image'])) ?>" alt="<?= e($dest['alt_text'] ?: $dest['name']) ?>" style="width:100%;height:100%;object-fit:cover;opacity:0.45;transform:scale(1.04);transition:transform 8s ease;" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
    <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(6,11,24,0.45) 0%, rgba(6,11,24,0.85) 75%, var(--color-navy-deep) 100%);"></div>
  </div>

  <div class="container" style="position:relative;z-index:2;">
    <!-- Breadcrumbs -->
    <nav aria-label="Breadcrumb" style="margin-bottom:14px;font-size:0.85rem;display:flex;align-items:center;gap:8px;">
      <a href="<?= BASE_URL ?>/index.php" style="color:var(--color-periwinkle);transition:color 0.2s;">Home</a> <span style="opacity:0.5;">/</span>
      <a href="<?= BASE_URL ?>/destinations.php" style="color:var(--color-periwinkle);transition:color 0.2s;">Destinations</a> <span style="opacity:0.5;">/</span>
      <span style="color:#FFF;font-weight:600;"><?= e($dest['name']) ?></span>
    </nav>

    <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(196,181,253,0.15);backdrop-filter:blur(16px);padding:5px 16px;border-radius:var(--radius-pill);border:1px solid rgba(196,181,253,0.35);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.14em;color:var(--color-icy);margin-bottom:14px;font-weight:700;box-shadow:0 0 20px rgba(139,92,246,0.25);">
      <span>✦ <?= strtoupper(e($dest['category'])) ?> &bull; <?= e($dest['region_state']) ?>, <?= e($dest['country']) ?></span>
    </div>

    <h1 class="editorial-headline" style="color:#FFF;margin-bottom:12px;font-size:clamp(2.5rem, 5vw, 4rem);text-shadow:0 4px 16px rgba(0,0,0,0.6);">
      <?= e($dest['name']) ?>
    </h1>
    <?php if (!empty($dest['tagline'])): ?>
      <p class="lead-text" style="color:#E2E8F0;max-width:740px;font-size:1.18rem;line-height:1.6;text-shadow:0 2px 8px rgba(0,0,0,0.5);"><?= e($dest['tagline']) ?></p>
    <?php endif; ?>
  </div>
</section>

<!-- Destination Detailed Overview -->
<section class="section-padding" style="background:transparent;position:relative;z-index:2;">
  <div class="container">
    <div class="detail-layout-grid">
      
      <!-- Main Content -->
      <div>
        <div class="glass-card glass-specular" style="background:var(--glass-surface-card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);padding:36px;border-radius:24px;border:1px solid var(--glass-border);box-shadow:var(--glass-inner-highlight), var(--shadow-md);margin-bottom:36px;">
          <h2 class="section-title" style="font-size:1.85rem;margin-bottom:18px;">About <?= e($dest['name']) ?></h2>
          <div style="font-size:1.04rem;line-height:1.85;color:var(--color-text-body);">
            <?= nl2br(e($dest['description'])) ?>
          </div>
        </div>

        <!-- Highlights Grid -->
        <?php if (!empty($highlights)): ?>
          <div class="glass-card glass-specular" style="background:var(--glass-surface-card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);padding:34px;border-radius:24px;border:1px solid var(--glass-border);box-shadow:var(--glass-inner-highlight), var(--shadow-md);margin-bottom:40px;">
            <div style="display:inline-flex;align-items:center;gap:8px;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.12em;color:var(--color-violet);font-weight:700;margin-bottom:8px;">
              Curated Vignettes
            </div>
            <h3 style="font-size:1.35rem;margin-bottom:20px;color:var(--color-navy);">Key Experience Highlights</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;">
              <?php foreach ($highlights as $hl): ?>
                <div style="display:flex;align-items:center;gap:12px;font-size:0.96rem;color:var(--color-text-headline);padding:10px 14px;border-radius:12px;background:rgba(196,181,253,0.18);border:1px solid rgba(196,181,253,0.35);">
                  <span style="color:var(--color-violet);font-weight:700;font-size:1.1rem;">✦</span>
                  <span style="font-weight:600;"><?= e($hl) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Sidebar Quick Info & Action Card -->
      <div>
        <div class="glass-card card-3d glass-specular" style="position:sticky;top:100px;background:var(--glass-surface-card);backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur);padding:34px 28px;border-radius:24px;box-shadow:var(--glass-inner-highlight), var(--shadow-xl);border:1px solid var(--glass-border);">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;border-bottom:1px solid rgba(196,181,253,0.25);padding-bottom:14px;">
            <h3 style="font-size:1.25rem;color:var(--color-navy);font-weight:700;margin:0;">Travel Quick Facts</h3>
            <span style="font-size:1.2rem;color:var(--color-violet);">🧭</span>
          </div>
          
          <div style="margin-bottom:18px;">
            <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-text-muted);font-weight:700;">Region / State</div>
            <div style="font-weight:600;color:var(--color-navy);font-size:1rem;margin-top:2px;"><?= e($dest['region_state']) ?>, <?= e($dest['country']) ?></div>
          </div>

          <?php if (!empty($dest['best_time_to_visit'])): ?>
            <div style="margin-bottom:18px;">
              <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-text-muted);font-weight:700;">Best Time to Visit</div>
              <div style="font-weight:600;color:var(--color-navy);font-size:1rem;margin-top:2px;"><?= e($dest['best_time_to_visit']) ?></div>
            </div>
          <?php endif; ?>

          <?php if ($dest['latitude'] && $dest['longitude']): ?>
            <div style="margin-bottom:22px;">
              <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-text-muted);font-weight:700;">Geographic Coordinates</div>
              <div style="font-weight:600;font-size:0.92rem;color:var(--color-violet);margin-top:2px;"><?= e($dest['latitude']) ?>° N, <?= e($dest['longitude']) ?>° E</div>
            </div>
          <?php endif; ?>

          <div style="display:flex;flex-direction:column;gap:12px;margin-top:24px;border-top:1px solid rgba(196,181,253,0.25);padding-top:20px;">
            <a href="<?= BASE_URL ?>/enquiry.php?destination=<?= urlencode($dest['name']) ?>" class="btn btn-primary" style="width:100%;justify-content:center;">
              Plan Trip to <?= e($dest['name']) ?>
            </a>
            <a href="<?= e($waDestUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-teal" style="width:100%;justify-content:center;">
              <span>WhatsApp Specialist</span>
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Associated Packages for this Destination -->
<?php if (!empty($packages)): ?>
<section class="section-padding section-dark">
  <div class="container">
    
    <div class="section-header">
      <span class="section-subtitle">Curated Itineraries</span>
      <h2 class="section-title">Tour Packages for <?= e($dest['name']) ?></h2>
      <p class="lead-text">Pre-planned itineraries featuring verified accommodations, transport, and guided activities.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:28px;">
      <?php foreach ($packages as $package): ?>
        <?php include __DIR__ . '/includes/package-card.php'; ?>
      <?php endforeach; ?>
    </div>

  </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
