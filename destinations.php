<?php
/**
 * MIDHILA VOYAGES — Destinations Catalog
 * Pure White Editorial Hospitality Canvas
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Explore Horizons & Destinations | Midhila Voyages';
$pageDescription = 'Discover domestic escapes across Kerala, Tamil Nadu, Varanasi, Ayodhya, Andaman, and global travel wonders including Dubai, Bali, Singapore, and Maldives.';

$db = getDB();

// Category filter from query
$filterCat = $_GET['cat'] ?? 'all';
$sql = "SELECT * FROM `destinations` WHERE `is_published` = 1";
$params = [];

if (in_array($filterCat, ['domestic', 'international'])) {
    $sql .= " AND `category` = ?";
    $params[] = $filterCat;
}
$sql .= " ORDER BY `is_featured` DESC, `display_order` ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$destinations = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Editorial Destination Hero (Pure White with Subtle 35% Photo Layer) -->
<section style="position:relative;background:#FFFFFF;padding-top:calc(var(--header-height) + 50px);padding-bottom:50px;overflow:hidden;border-bottom:1px solid var(--color-border);">
  <div class="cinematic-bg-layer cinematic-bg-mask-radial" aria-hidden="true">
    <img src="https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1920&q=80" 
         alt="" 
         class="cinematic-bg-layer__img" 
         style="opacity:0.25;">
  </div>
  
  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:820px;">
    <div class="chapter-badge" style="justify-content:center;">
      <span class="chapter-number">Chapter II &bull; Horizons</span>
      <span class="chapter-coordinates">Geographic Sanctuaries</span>
    </div>
    <h1 class="editorial-headline-large" style="margin-bottom:16px;">
      Curated Travel Destinations
    </h1>
    <p class="editorial-subtext" style="margin:0 auto;">
      From the timeless sacred ghats of Varanasi to the tranquil emerald waterways of Kerala and remote island atolls.
    </p>
  </div>
</section>

<!-- Filter Tabs & Destination Cards Grid -->
<section style="background:#FFFFFF;padding-top:40px;padding-bottom:100px;position:relative;">
  <div class="container">
    
    <!-- Filter Tabs (Pure White with Crisp Obsidian Active State) -->
    <div style="display:flex;justify-content:center;gap:12px;margin-bottom:48px;flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>/destinations.php?cat=all" 
         class="filter-pill <?= ($filterCat === 'all') ? 'is-active' : '' ?>" style="padding:10px 22px;">
        All Horizons (<?= count($destinations) ?>)
      </a>
      <a href="<?= BASE_URL ?>/destinations.php?cat=domestic" 
         class="filter-pill <?= ($filterCat === 'domestic') ? 'is-active' : '' ?>" style="padding:10px 22px;">
        Incredible India (Domestic)
      </a>
      <a href="<?= BASE_URL ?>/destinations.php?cat=international" 
         class="filter-pill <?= ($filterCat === 'international') ? 'is-active' : '' ?>" style="padding:10px 22px;">
        Global Escapes
      </a>
    </div>

    <!-- Destinations Grid -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(360px, 1fr));gap:32px;">
      <?php if (!empty($destinations)): ?>
        <?php foreach ($destinations as $dest): 
          $destSlug = $dest['slug'] ?? ('destination-' . $dest['id']);
          $destImg = !empty($dest['featured_image']) ? $dest['featured_image'] : (ASSETS_URL . '/images/placeholder.svg');
        ?>
          <div class="dest-showcase-card reveal-fade-up">
            <div class="dest-showcase-card__img-wrap" style="height:280px;">
              <a href="<?= BASE_URL ?>/destination.php?slug=<?= urlencode($destSlug) ?>" aria-label="Explore <?= e($dest['name']) ?>">
                <img src="<?= e($destImg) ?>" 
                     alt="<?= e($dest['name']) ?>" 
                     class="dest-showcase-card__img" 
                     loading="lazy" 
                     width="420" 
                     height="280">
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
              <p style="font-size:0.88rem;color:var(--color-text-muted);line-height:1.65;margin-bottom:18px;">
                <?= e(truncate_words($dest['description'] ?? '', 20)) ?>
              </p>
              <a href="<?= BASE_URL ?>/destination.php?slug=<?= urlencode($destSlug) ?>" class="link-editorial-arrow">
                <span>View Itineraries &amp; Guide</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="grid-column:1/-1;text-align:center;padding:70px 20px;border:1px dashed var(--color-border);border-radius:var(--radius-md);background:#FAFAFA;">
          <p style="color:var(--color-text-muted);">No destinations found in this portfolio. Contact our concierge for bespoke itineraries.</p>
        </div>
      <?php endif; ?>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
