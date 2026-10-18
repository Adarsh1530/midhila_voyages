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
<section style="position:relative;min-height:55vh;background-color:var(--color-navy-deep);display:flex;align-items:flex-end;padding-top:calc(var(--header-height) + 40px);padding-bottom:50px;color:#FFF;overflow:hidden;">
  <div style="position:absolute;inset:0;">
    <img src="<?= e(get_image_url($dest['featured_image'])) ?>" alt="<?= e($dest['alt_text'] ?: $dest['name']) ?>" style="width:100%;height:100%;object-fit:cover;opacity:0.4;" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
    <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(6,16,30,0.5) 0%, rgba(6,16,30,0.85) 75%, var(--color-navy-deep) 100%);"></div>
  </div>

  <div class="container" style="position:relative;z-index:2;">
    <!-- Breadcrumbs -->
    <nav aria-label="Breadcrumb" style="margin-bottom:14px;font-size:0.85rem;color:var(--color-gold);">
      <a href="<?= BASE_URL ?>/index.php" style="color:#CBD5E0;">Home</a> &nbsp;/&nbsp;
      <a href="<?= BASE_URL ?>/destinations.php" style="color:#CBD5E0;">Destinations</a> &nbsp;/&nbsp;
      <span><?= e($dest['name']) ?></span>
    </nav>

    <div style="display:inline-block;background:rgba(255,255,255,0.1);padding:4px 14px;border-radius:var(--radius-pill);border:1px solid var(--color-border-gold);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.12em;color:var(--color-gold-bright);margin-bottom:12px;">
      <?= strtoupper(e($dest['category'])) ?> &bull; <?= e($dest['region_state']) ?>, <?= e($dest['country']) ?>
    </div>

    <h1 class="editorial-headline" style="color:#FFF;margin-bottom:12px;font-size:clamp(2.4rem, 4.5vw, 3.8rem);">
      <?= e($dest['name']) ?>
    </h1>
    <?php if (!empty($dest['tagline'])): ?>
      <p class="lead-text" style="color:#E2E8F0;max-width:720px;font-size:1.15rem;"><?= e($dest['tagline']) ?></p>
    <?php endif; ?>
  </div>
</section>

<!-- Destination Detailed Overview -->
<section class="section-padding" style="background-color:var(--color-ivory);">
  <div class="container">
    <div class="detail-layout-grid">
      
      <!-- Main Content -->
      <div>
        <h2 class="section-title" style="font-size:1.85rem;">About <?= e($dest['name']) ?></h2>
        <div style="font-size:1.02rem;line-height:1.8;color:var(--color-text-body);margin-bottom:36px;">
          <?= nl2br(e($dest['description'])) ?>
        </div>

        <!-- Highlights Grid -->
        <?php if (!empty($highlights)): ?>
          <div style="background:#FFF;padding:32px;border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:1px solid var(--color-border);margin-bottom:40px;">
            <h3 style="font-size:1.25rem;margin-bottom:18px;color:var(--color-navy);">Key Experience Highlights</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:14px;">
              <?php foreach ($highlights as $hl): ?>
                <div style="display:flex;align-items:center;gap:10px;font-size:0.95rem;color:var(--color-text-headline);">
                  <span style="color:var(--color-teal);font-weight:700;">✓</span>
                  <span><?= e($hl) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Sidebar Quick Info & Action Card -->
      <div>
        <div style="position:sticky;top:100px;background:#FFF;padding:30px;border-radius:var(--radius-lg);box-shadow:var(--shadow-md);border:1px solid var(--color-border);">
          <h3 style="font-size:1.2rem;margin-bottom:20px;color:var(--color-navy);border-bottom:1px solid var(--color-border);padding-bottom:12px;">Travel Quick Facts</h3>
          
          <div style="margin-bottom:16px;">
            <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-text-muted);">Region / State</div>
            <div style="font-weight:600;color:var(--color-navy);"><?= e($dest['region_state']) ?>, <?= e($dest['country']) ?></div>
          </div>

          <?php if (!empty($dest['best_time_to_visit'])): ?>
            <div style="margin-bottom:16px;">
              <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-text-muted);">Best Time to Visit</div>
              <div style="font-weight:600;color:var(--color-navy);"><?= e($dest['best_time_to_visit']) ?></div>
            </div>
          <?php endif; ?>

          <?php if ($dest['latitude'] && $dest['longitude']): ?>
            <div style="margin-bottom:20px;">
              <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-text-muted);">Geographic Coordinates</div>
              <div style="font-weight:600;font-size:0.88rem;color:var(--color-teal-dark);"><?= e($dest['latitude']) ?>° N, <?= e($dest['longitude']) ?>° E</div>
            </div>
          <?php endif; ?>

          <div style="display:flex;flex-direction:column;gap:10px;margin-top:24px;border-top:1px solid var(--color-border);padding-top:20px;">
            <a href="<?= BASE_URL ?>/enquiry.php?destination=<?= urlencode($dest['name']) ?>" class="btn btn-primary" style="width:100%;">
              Plan Trip to <?= e($dest['name']) ?>
            </a>
            <a href="<?= e($waDestUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-teal" style="width:100%;">
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
