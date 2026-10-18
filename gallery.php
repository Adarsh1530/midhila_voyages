<?php
/**
 * MIDHILA VOYAGES — Travel Gallery
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Travel Gallery | Visual Journey Memoirs | Midhila Voyages';
$pageDescription = 'Immerse in visual memories of serene backwaters, ancient temple gopurams, misty tea mountains, and turquoise coral lagoons with Midhila Voyages.';

$db = getDB();
$stmt = $db->query("SELECT * FROM `gallery_images` WHERE `is_active` = 1 ORDER BY `display_order` ASC, `id` DESC");
$gallery = $stmt->fetchAll();

// Extract unique categories
$categories = array_unique(array_column($gallery, 'category'));

require_once __DIR__ . '/includes/header.php';
?>

<!-- Banner Header -->
<section style="position:relative;background-color:var(--color-navy-deep);padding-top:calc(var(--header-height) + 50px);padding-bottom:70px;color:#FFF;overflow:hidden;">
  <div style="position:absolute;inset:0;opacity:0.25;">
    <img src="https://images.unsplash.com/photo-1514282401047-d79a71a590e8?auto=format&fit=crop&w=1800&q=80" alt="Travel panorama" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
    <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(6,16,30,0.7) 0%, var(--color-navy-deep) 100%);"></div>
  </div>
  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:760px;">
    <div style="display:inline-block;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.2em;color:var(--color-gold);margin-bottom:12px;font-weight:600;">Captured Moments</div>
    <h1 class="editorial-headline" style="color:#FFF;font-size:clamp(2.4rem, 4vw, 3.6rem);margin-bottom:16px;">Travel Gallery</h1>
    <p class="lead-text" style="color:#CBD5E0;">Explore photographic vignettes of majestic horizons, sacred temple rituals, and secluded tropical sanctuaries.</p>
  </div>
</section>

<!-- Filter Buttons & Gallery Grid -->
<section class="section-padding" style="background-color:var(--color-ivory);">
  <div class="container">
    
    <!-- Filter Tabs -->
    <div style="display:flex;justify-content:center;gap:10px;margin-bottom:44px;flex-wrap:wrap;">
      <button type="button" class="gallery-filter-btn is-active btn btn-primary" data-filter="all" style="border-radius:var(--radius-pill);padding:8px 22px;font-size:0.85rem;">
        All Photos (<?= count($gallery) ?>)
      </button>
      <?php foreach ($categories as $cat): ?>
        <button type="button" class="gallery-filter-btn btn btn-outline-gold" data-filter="<?= e($cat) ?>" style="border-radius:var(--radius-pill);padding:8px 22px;font-size:0.85rem;text-transform:capitalize;">
          <?= e($cat) ?>
        </button>
      <?php endforeach; ?>
    </div>

    <!-- Gallery Grid -->
    <div class="gallery-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:24px;">
      <?php foreach ($gallery as $idx => $item): ?>
        <div class="gallery-card reveal-fade-up stagger-<?= ($idx % 3) + 1 ?>" 
             data-category="<?= e($item['category']) ?>"
             data-full="<?= e(get_image_url($item['image_url'])) ?>"
             data-title="<?= e($item['title']) ?>"
             data-location="<?= e($item['location_name']) ?>"
             style="position:relative;height:320px;border-radius:var(--radius-md);overflow:hidden;cursor:pointer;box-shadow:var(--shadow-md);background:var(--color-navy-card);">
          
          <img src="<?= e(get_image_url($item['image_url'])) ?>" alt="<?= e($item['alt_text']) ?>" style="width:100%;height:100%;object-fit:cover;transition:transform 0.6s cubic-bezier(0.16,1,0.3,1);" loading="lazy" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
          
          <div style="position:absolute;inset:0;background:linear-gradient(180deg, transparent 40%, rgba(6,16,30,0.88) 100%);display:flex;flex-direction:column;justify-content:flex-end;padding:24px;color:#FFF;">
            <span style="font-size:0.75rem;color:var(--color-gold);text-transform:uppercase;letter-spacing:0.12em;font-weight:600;"><?= e($item['location_name']) ?></span>
            <h3 style="font-size:1.2rem;color:#FFF;margin-top:2px;"><?= e($item['title']) ?></h3>
            <span style="font-size:0.75rem;color:var(--color-teal-bright);margin-top:4px;display:inline-flex;align-items:center;gap:4px;">
              <span>Click to Expand</span> &rarr;
            </span>
          </div>

        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- Lightbox Modal -->
<div id="galleryLightbox" class="lightbox-modal" style="position:fixed;inset:0;background:rgba(6,16,30,0.96);backdrop-filter:blur(10px);z-index:2000;display:none;align-items:center;justify-content:center;padding:24px;">
  <button class="lightbox__close" style="position:absolute;top:24px;right:24px;color:#FFF;font-size:2.2rem;background:none;border:none;cursor:pointer;" aria-label="Close Lightbox">&times;</button>
  <button class="lightbox__prev" style="position:absolute;left:24px;color:#FFF;font-size:2.4rem;background:none;border:none;cursor:pointer;" aria-label="Previous image">&#10094;</button>
  <button class="lightbox__next" style="position:absolute;right:24px;color:#FFF;font-size:2.4rem;background:none;border:none;cursor:pointer;" aria-label="Next image">&#10095;</button>
  
  <div style="max-width:960px;width:100%;text-align:center;">
    <img src="" alt="" class="lightbox__image" style="max-height:75vh;margin:0 auto;border-radius:var(--radius-sm);box-shadow:var(--shadow-xl);">
    <div style="margin-top:16px;">
      <h3 class="lightbox__title" style="color:#FFF;font-size:1.35rem;"></h3>
      <p class="lightbox__location" style="color:var(--color-gold);font-size:0.95rem;"></p>
    </div>
  </div>
</div>
<style>
.lightbox-modal.is-open { display: flex !important; }
</style>

<script src="<?= ASSETS_URL ?>/js/gallery.js" defer></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
