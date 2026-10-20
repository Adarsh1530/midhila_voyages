<?php
/**
 * MIDHILA VOYAGES — Destination Details Showcase
 * Pure White Editorial Hospitality Canvas
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
    echo '<div class="container section-padding" style="text-align:center;background:#FFFFFF;">';
    echo '<h1 class="editorial-headline-large">Destination Not Found</h1>';
    echo '<p class="editorial-subtext" style="margin:16px auto 28px;">The destination you are looking for may have been moved or updated.</p>';
    echo '<a href="' . BASE_URL . '/destinations.php" class="btn-cinematic-primary">Browse All Destinations</a>';
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
$agencyWhatsApp = get_setting('whatsapp_number', '+918075021427');

$waDestMsg = "Hello MIDHILA VOYAGES, I am interested in visiting {$dest['name']}. Please share available tour packages and customized itinerary options.";
$waDestUrl = build_whatsapp_url($agencyWhatsApp, $waDestMsg);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Destination Editorial Hero (Pure White with Subtle 35% Photo Layer) -->
<section style="position:relative;background:#FFFFFF;padding-top:calc(var(--header-height) + 40px);padding-bottom:50px;overflow:hidden;border-bottom:1px solid var(--color-border);">
  
  <div class="cinematic-bg-layer cinematic-bg-mask-radial" aria-hidden="true">
    <img src="<?= e(get_image_url($dest['featured_image'])) ?>" 
         alt="" 
         class="cinematic-bg-layer__img" 
         style="opacity:0.25;">
  </div>

  <div class="container" style="position:relative;z-index:2;">
    <!-- Breadcrumbs -->
    <nav aria-label="Breadcrumb" style="margin-bottom:16px;font-size:0.82rem;display:flex;align-items:center;gap:8px;color:var(--color-text-muted);">
      <a href="<?= BASE_URL ?>/index.php" style="color:var(--color-text-secondary);">Home</a> <span>/</span>
      <a href="<?= BASE_URL ?>/destinations.php" style="color:var(--color-text-secondary);">Destinations</a> <span>/</span>
      <span style="color:var(--color-text-main);font-weight:700;"><?= e($dest['name']) ?></span>
    </nav>

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap;">
      <span class="chapter-number">
        <?= strtoupper(e($dest['category'])) ?> &bull; <?= e($dest['region_state']) ?>, <?= e($dest['country']) ?>
      </span>
      <span style="font-size:0.8rem;background:#FAFAFA;border:1px solid var(--color-border);padding:4px 14px;border-radius:var(--radius-pill);color:var(--color-text-secondary);font-weight:600;">
        <?= count($packages) ?> Active Itineraries
      </span>
    </div>

    <h1 class="editorial-headline-large" style="margin-bottom:12px;">
      <?= e($dest['name']) ?>
    </h1>
    
    <?php if (!empty($dest['tagline'])): ?>
      <p class="editorial-subtext" style="max-width:800px;"><?= e($dest['tagline']) ?></p>
    <?php endif; ?>
  </div>
</section>

<!-- Destination Detailed Overview & Quick Facts -->
<section style="background:#FFFFFF;padding-top:60px;padding-bottom:80px;position:relative;">
  <div class="container">
    
    <div style="display:grid;grid-template-columns:1fr 380px;gap:48px;align-items:start;">
      
      <!-- Main Content -->
      <div>
        
        <!-- Large High-Fidelity Photo for 100% Inspection -->
        <div style="width:100%;height:420px;border-radius:var(--radius-md);overflow:hidden;border:1px solid var(--color-border);box-shadow:var(--shadow-card);margin-bottom:40px;background:#F8FAFC;">
          <img src="<?= e(get_image_url($dest['featured_image'])) ?>" 
               alt="<?= e($dest['alt_text'] ?: $dest['name']) ?>" 
               style="width:100%;height:100%;object-fit:cover;opacity:1 !important;" 
               onerror="this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
        </div>

        <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:36px;box-shadow:var(--shadow-ambient);margin-bottom:40px;">
          <h2 style="font-size:1.6rem;margin-bottom:16px;">The Essence of <?= e($dest['name']) ?></h2>
          <div style="font-size:1.02rem;line-height:1.85;color:var(--color-text-body);">
            <?= nl2br(e($dest['description'])) ?>
          </div>
        </div>

        <!-- Highlights Grid -->
        <?php if (!empty($highlights)): ?>
          <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:34px;box-shadow:var(--shadow-ambient);margin-bottom:40px;">
            <h3 style="font-size:1.35rem;margin-bottom:20px;">Signature Highlights</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:14px;">
              <?php foreach ($highlights as $hl): ?>
                <div style="display:flex;align-items:center;gap:12px;font-size:0.92rem;color:var(--color-text-main);padding:12px 16px;border-radius:var(--radius-sm);background:#FAFAFA;border:1px solid var(--color-border);">
                  <span style="color:var(--color-gold-deep);font-weight:700;">✦</span>
                  <span style="font-weight:600;"><?= e($hl) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

      </div>

      <!-- Sidebar Quick Facts -->
      <div>
        <div style="position:sticky;top:100px;background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:34px 28px;box-shadow:var(--shadow-card);">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;border-bottom:1px solid var(--color-border);padding-bottom:14px;">
            <h3 style="font-size:1.25rem;font-weight:700;margin:0;">Travel Quick Facts</h3>
            <span style="color:var(--color-gold-deep);">🧭</span>
          </div>
          
          <div style="margin-bottom:18px;">
            <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-text-muted);font-weight:700;">Region / State</div>
            <div style="font-weight:700;color:var(--color-text-headline);font-size:1rem;margin-top:2px;"><?= e($dest['region_state']) ?>, <?= e($dest['country']) ?></div>
          </div>

          <?php if (!empty($dest['best_time_to_visit'])): ?>
            <div style="margin-bottom:18px;">
              <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-text-muted);font-weight:700;">Best Time to Visit</div>
              <div style="font-weight:600;color:var(--color-text-headline);font-size:0.95rem;margin-top:2px;"><?= e($dest['best_time_to_visit']) ?></div>
            </div>
          <?php endif; ?>

          <?php if (!empty($dest['ideal_duration'])): ?>
            <div style="margin-bottom:18px;">
              <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-text-muted);font-weight:700;">Recommended Pacing</div>
              <div style="font-weight:600;color:var(--color-text-headline);font-size:0.95rem;margin-top:2px;"><?= e($dest['ideal_duration']) ?></div>
            </div>
          <?php endif; ?>

          <?php if (!empty($dest['nearest_airport'])): ?>
            <div style="margin-bottom:24px;">
              <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-text-muted);font-weight:700;">Gateway Transit</div>
              <div style="font-size:0.9rem;color:var(--color-text-body);margin-top:2px;"><?= e($dest['nearest_airport']) ?></div>
            </div>
          <?php endif; ?>

          <div style="display:flex;flex-direction:column;gap:12px;border-top:1px solid var(--color-border);padding-top:20px;">
            <a href="<?= e($waDestUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-solid" style="width:100%;justify-content:center;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 2.11.83 3.292.83 3.183 0 5.768-2.587 5.768-5.766.001-3.181-2.585-5.767-5.764-5.767zm7.653 5.766c0 4.225-3.438 7.663-7.654 7.663-1.309 0-2.569-.334-3.674-.925l-4.148 1.088 1.107-4.041c-.696-1.187-1.077-2.55-1.077-3.957 0-4.224 3.438-7.662 7.654-7.662 4.226 0 7.662 3.438 7.662 7.662z"/></svg>
              <span>Enquire for <?= e($dest['name']) ?></span>
            </a>

            <a href="<?= BASE_URL ?>/enquiry.php?destination=<?= urlencode($dest['name']) ?>" class="btn-cinematic-primary" style="width:100%;justify-content:center;">
              <span>Plan Custom Journey</span>
            </a>
          </div>

        </div>
      </div>

    </div>

  </div>
</section>

<!-- Available Tour Packages for this Destination -->
<div class="section-divider-hairline"></div>
<section class="section-padding" style="background:#FFFFFF;">
  <div class="container">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:40px;flex-wrap:wrap;gap:20px;">
      <div>
        <h2 class="editorial-headline-large" style="font-size:2.2rem;">
          Itineraries in <?= e($dest['name']) ?>
        </h2>
      </div>

      <a href="<?= BASE_URL ?>/packages.php?destination=<?= urlencode($dest['slug']) ?>" class="btn-cinematic-outline">
        <span>View All Filters</span>
      </a>
    </div>

    <?php if (empty($packages)): ?>
      <div style="padding:60px 20px;border:1px dashed var(--color-border);border-radius:var(--radius-md);text-align:center;background:#FAFAFA;">
        <p style="color:var(--color-text-muted);margin-bottom:16px;">We design bespoke private itineraries for <?= e($dest['name']) ?> on request.</p>
        <a href="<?= e($waDestUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-solid">Chat with Our Specialist</a>
      </div>
    <?php else: ?>
      <div class="packages-grid">
        <?php foreach ($packages as $package): ?>
          <?php include __DIR__ . '/includes/package-card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
