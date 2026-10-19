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
<section style="position:relative;background:linear-gradient(180deg, rgba(6,11,24,0.92) 0%, rgba(10,17,40,0.85) 100%);padding-top:calc(var(--header-height) + 50px);padding-bottom:70px;color:#FFF;overflow:hidden;border-bottom:1px solid rgba(196,181,253,0.18);">
  <div style="position:absolute;inset:0;opacity:0.25;">
    <img src="https://images.unsplash.com/photo-1514282401047-d79a71a590e8?auto=format&fit=crop&w=1800&q=80" alt="Travel panorama" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
    <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(6,11,24,0.7) 0%, rgba(10,17,40,0.92) 100%);"></div>
  </div>
  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:780px;">
    <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(196,181,253,0.12);backdrop-filter:blur(16px);border:1px solid rgba(196,181,253,0.3);padding:6px 16px;border-radius:var(--radius-pill);font-size:0.8rem;text-transform:uppercase;letter-spacing:0.18em;color:var(--color-lavender);margin-bottom:14px;font-weight:600;box-shadow:0 0 20px rgba(139,92,246,0.2);">
      <span>✦ Captured Moments</span>
    </div>
    <h1 class="editorial-headline" style="color:#FFF;font-size:clamp(2.4rem, 4.5vw, 3.8rem);margin-bottom:16px;">Travel Gallery</h1>
    <p class="lead-text" style="color:#CBD5E1;font-size:1.1rem;line-height:1.7;">Explore photographic vignettes of majestic horizons, sacred temple rituals, and secluded tropical sanctuaries curated by Midhila Voyages.</p>
  </div>
</section>

<!-- Filter Buttons & Gallery Grid -->
<section class="section-padding" style="background:transparent;position:relative;z-index:2;">
  <div class="container">
    
    <!-- Filter Tabs -->
    <div style="display:flex;justify-content:center;gap:12px;margin-bottom:48px;flex-wrap:wrap;">
      <button type="button" class="gallery-filter-btn is-active btn" data-filter="all" style="border-radius:var(--radius-pill);padding:9px 24px;font-size:0.88rem;background:linear-gradient(135deg, #7C3AED 0%, #4F46E5 100%);color:#FFF;border:1px solid rgba(255,255,255,0.35);box-shadow:0 6px 20px rgba(124,58,237,0.35), inset 0 1px 1px rgba(255,255,255,0.4);cursor:pointer;transition:all 0.3s ease;">
        All Photos (<?= count($gallery) ?>)
      </button>
      <?php foreach ($categories as $cat): ?>
        <button type="button" class="gallery-filter-btn btn" data-filter="<?= e($cat) ?>" style="border-radius:var(--radius-pill);padding:9px 24px;font-size:0.88rem;text-transform:capitalize;background:var(--glass-surface);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);color:var(--color-navy);border:1px solid var(--glass-border);box-shadow:var(--glass-inner-highlight), var(--shadow-sm);cursor:pointer;transition:all 0.3s ease;">
          <?= e($cat) ?>
        </button>
      <?php endforeach; ?>
    </div>

    <!-- Gallery Grid with 3D Specular Tilt -->
    <div class="gallery-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:28px;">
      <?php foreach ($gallery as $idx => $item): ?>
        <div class="gallery-card card-3d glass-specular reveal-fade-up stagger-<?= ($idx % 3) + 1 ?>" 
             data-category="<?= e($item['category']) ?>"
             data-full="<?= e(get_image_url($item['image_url'])) ?>"
             data-title="<?= e($item['title']) ?>"
             data-location="<?= e($item['location_name']) ?>"
             style="position:relative;height:340px;border-radius:20px;overflow:hidden;cursor:pointer;background:rgba(10,17,40,0.75);border:1px solid rgba(196,181,253,0.22);box-shadow:0 16px 36px -8px rgba(0,0,0,0.25), inset 0 1px 1px rgba(255,255,255,0.3);transition:transform 0.4s cubic-bezier(0.16,1,0.3,1), box-shadow 0.4s ease, border-color 0.4s ease;">
          
          <img src="<?= e(get_image_url($item['image_url'])) ?>" alt="<?= e($item['alt_text']) ?>" style="width:100%;height:100%;object-fit:cover;transition:transform 0.7s cubic-bezier(0.16,1,0.3,1);" loading="lazy" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
          
          <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(6,11,24,0) 35%, rgba(6,11,24,0.88) 100%);display:flex;flex-direction:column;justify-content:flex-end;padding:26px;color:#FFF;pointer-events:none;">
            <div style="display:inline-flex;align-items:center;gap:6px;margin-bottom:6px;">
              <span style="font-size:0.75rem;color:var(--color-periwinkle);text-transform:uppercase;letter-spacing:0.12em;font-weight:700;text-shadow:0 0 10px rgba(141,164,239,0.5);"><?= e($item['location_name']) ?></span>
            </div>
            <h3 style="font-size:1.25rem;color:#FFF;margin-bottom:6px;font-weight:700;text-shadow:0 2px 8px rgba(0,0,0,0.5);"><?= e($item['title']) ?></h3>
            <span style="font-size:0.8rem;color:var(--color-icy);display:inline-flex;align-items:center;gap:6px;font-weight:600;">
              <span>Expand Snapshot</span> &rarr;
            </span>
          </div>

        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- Lightbox Modal -->
<div id="galleryLightbox" class="lightbox-modal" style="position:fixed;inset:0;background:rgba(6,11,24,0.85);backdrop-filter:blur(24px) saturate(180%);-webkit-backdrop-filter:blur(24px) saturate(180%);z-index:2000;display:none;align-items:center;justify-content:center;padding:24px;">
  <button class="lightbox__close" style="position:absolute;top:28px;right:28px;color:#FFF;font-size:2.2rem;background:rgba(255,255,255,0.1);border:1px solid rgba(196,181,253,0.3);width:46px;height:46px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;backdrop-filter:blur(12px);transition:all 0.2s;" aria-label="Close Lightbox">&times;</button>
  <button class="lightbox__prev" style="position:absolute;left:28px;color:#FFF;font-size:2.2rem;background:rgba(255,255,255,0.1);border:1px solid rgba(196,181,253,0.3);width:50px;height:50px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;backdrop-filter:blur(12px);transition:all 0.2s;" aria-label="Previous image">&#10094;</button>
  <button class="lightbox__next" style="position:absolute;right:28px;color:#FFF;font-size:2.2rem;background:rgba(255,255,255,0.1);border:1px solid rgba(196,181,253,0.3);width:50px;height:50px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;backdrop-filter:blur(12px);transition:all 0.2s;" aria-label="Next image">&#10095;</button>
  
  <div style="max-width:1020px;width:100%;text-align:center;background:rgba(10,17,40,0.65);padding:24px;border-radius:24px;border:1px solid rgba(196,181,253,0.28);box-shadow:0 30px 60px rgba(0,0,0,0.5), inset 0 1px 1px rgba(255,255,255,0.3);">
    <img src="" alt="" class="lightbox__image" style="max-height:72vh;margin:0 auto;border-radius:14px;box-shadow:0 12px 36px rgba(0,0,0,0.4);border:1px solid rgba(196,181,253,0.2);">
    <div style="margin-top:18px;">
      <h3 class="lightbox__title" style="color:#FFF;font-size:1.4rem;font-weight:700;margin-bottom:4px;"></h3>
      <p class="lightbox__location" style="color:var(--color-periwinkle);font-size:0.95rem;font-weight:600;"></p>
    </div>
  </div>
</div>
<style>
.lightbox-modal.is-open { display: flex !important; }
.gallery-card:hover {
  transform: translateY(-8px) scale(1.02);
  border-color: rgba(141,164,239,0.6) !important;
  box-shadow: 0 24px 50px -10px rgba(139,92,246,0.35), inset 0 1px 1px rgba(255,255,255,0.5) !important;
}
.gallery-card:hover img {
  transform: scale(1.08);
}
.gallery-filter-btn:hover {
  transform: translateY(-2px);
  border-color: rgba(141,164,239,0.7) !important;
}
.gallery-filter-btn.is-active {
  background: linear-gradient(135deg, #7C3AED 0%, #4F46E5 100%) !important;
  color: #FFF !important;
  border-color: rgba(255,255,255,0.5) !important;
  box-shadow: 0 6px 20px rgba(124,58,237,0.4), inset 0 1px 1px rgba(255,255,255,0.5) !important;
}
</style>

<script src="<?= ASSETS_URL ?>/js/gallery.js" defer></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
