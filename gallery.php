<?php
/**
 * MIDHILA VOYAGES — Photographic Memoirs & Travel Gallery
 * Pure White Editorial Hospitality Canvas
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Travel Memoir Gallery | Moments from Curated Horizons | Midhila Voyages';
$pageDescription = 'Immerse yourself in authentic destination imagery from our travelers: Kerala backwaters, Vedic temples, tropical islands, and Himalayan vistas.';

$db = getDB();

$tagFilter = $_GET['tag'] ?? 'all';
$sql = "SELECT * FROM `gallery_images` WHERE `is_active` = 1";
$params = [];

if ($tagFilter !== 'all') {
    $sql .= " AND `category` = ?";
    $params[] = $tagFilter;
}
$sql .= " ORDER BY `display_order` ASC, `id` DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$galleryImages = $stmt->fetchAll();

// Fetch distinct categories
$catQuery = $db->query("SELECT DISTINCT `category` FROM `gallery_images` WHERE `is_active` = 1 AND `category` IS NOT NULL AND `category` != ''");
$categories = $catQuery->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Gallery Editorial Hero (Pure White with Subtle 35% Photo Layer) -->
<section style="position:relative;background:#FFFFFF;padding-top:calc(var(--header-height) + 50px);padding-bottom:50px;overflow:hidden;border-bottom:1px solid var(--color-border);">
  <div class="cinematic-bg-layer cinematic-bg-mask-radial" aria-hidden="true">
    <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1920&q=80" 
         alt="" 
         class="cinematic-bg-layer__img" 
         style="opacity:0.25;">
  </div>

  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:820px;">
    <div class="chapter-badge" style="justify-content:center;">
      <span class="chapter-number">Memoir Chronicles</span>
      <span class="chapter-coordinates">Visual Landscapes</span>
    </div>
    <h1 class="editorial-headline-large" style="margin-bottom:16px;">
      Moments Captured Along the Way
    </h1>
    <p class="editorial-subtext" style="margin:0 auto;">
      A curation of verified moments, sacred sanctums, and untouched landscapes captured during Midhila Voyages expeditions.
    </p>
  </div>
</section>

<!-- Filter Tabs & Masonry Image Grid (100% Full Opacity Photography) -->
<section style="background:#FFFFFF;padding-top:40px;padding-bottom:100px;position:relative;">
  <div class="container">
    
    <!-- Filter Tabs -->
    <div style="display:flex;justify-content:center;gap:10px;margin-bottom:44px;flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>/gallery.php?tag=all" 
         class="filter-pill <?= ($tagFilter === 'all') ? 'is-active' : '' ?>">
        All Memoirs
      </a>
      <?php foreach ($categories as $cat): ?>
        <a href="<?= BASE_URL ?>/gallery.php?tag=<?= urlencode($cat) ?>" 
           class="filter-pill <?= ($tagFilter === $cat) ? 'is-active' : '' ?>">
          <?= ucfirst(e($cat)) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Gallery Grid (100% Opacity Photography for Clear Customer Inspection) -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:24px;">
      <?php if (!empty($galleryImages)): ?>
        <?php foreach ($galleryImages as $img): ?>
          <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);overflow:hidden;box-shadow:var(--shadow-ambient);transition:transform 0.4s ease, box-shadow 0.4s ease;"
               onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='var(--shadow-hover)';"
               onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='var(--shadow-ambient)';">
            <div style="width:100%;aspect-ratio:4/3;overflow:hidden;background:#F8FAFC;">
              <img src="<?= e(get_image_url($img['image_url'])) ?>" 
                   alt="<?= e($img['alt_text'] ?: ($img['title'] ?? 'Travel Memoir')) ?>" 
                   style="width:100%;height:100%;object-fit:cover;opacity:1 !important;transition:transform 0.5s ease;"
                   loading="lazy"
                   onmouseover="this.style.transform='scale(1.05)'"
                   onmouseout="this.style.transform='scale(1)'">
            </div>
            <?php if (!empty($img['title']) || !empty($img['location'])): ?>
              <div style="padding:18px 20px;">
                <div style="font-size:0.75rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--color-gold-deep);font-weight:700;">
                  <?= e($img['location'] ?? 'Destination') ?>
                </div>
                <h4 style="font-size:1.05rem;color:var(--color-text-headline);margin-top:4px;">
                  <?= e($img['title'] ?? 'Memoir') ?>
                </h4>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="grid-column:1/-1;text-align:center;padding:70px 20px;border:1px dashed var(--color-border);border-radius:var(--radius-md);background:#FAFAFA;">
          <p style="color:var(--color-text-muted);">No images found in this gallery collection. Check back shortly for recent tour photos.</p>
        </div>
      <?php endif; ?>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
