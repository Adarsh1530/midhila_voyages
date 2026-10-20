<?php
/**
 * MIDHILA VOYAGES — Destinations Listing
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Explore Destinations | Domestic & International Travel with Midhila Voyages';
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

<!-- Destinations Banner Header -->
<section style="position:relative;background-color:var(--color-navy-deep);padding-top:calc(var(--header-height) + 50px);padding-bottom:70px;color:#FFF;overflow:hidden;">
  <div style="position:absolute;inset:0;opacity:0.25;">
    <img src="https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1800&q=80" alt="World travel panorama" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
    <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(6,16,30,0.7) 0%, var(--color-navy-deep) 100%);"></div>
  </div>
  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:760px;">
    <div class="float-slow" style="display:inline-block;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.2em;color:var(--color-lavender);margin-bottom:12px;font-weight:700;background:rgba(196,181,253,0.12);padding:6px 16px;border-radius:var(--radius-pill);border:1px solid rgba(196,181,253,0.3);">✨ Boundless Horizons</div>
    <h1 class="editorial-headline" style="color:#FFF;font-size:clamp(2.4rem, 4vw, 3.6rem);margin-bottom:16px;text-shadow:0 0 20px rgba(196,181,253,0.25);">Curated Travel Destinations</h1>
    <p class="lead-text" style="color:#CBD5E0;">Explore enchanting landscapes across India and globally celebrated vacation paradises.</p>
  </div>
</section>

<!-- Filter Tabs & Listing Grid -->
<section class="section-padding" style="background-color:transparent;">
  <div class="container">
    
    <!-- Filter Tabs (Frosted Glass Pills) -->
    <div style="display:flex;justify-content:center;gap:12px;margin-bottom:48px;flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>/destinations.php?cat=all" 
         class="btn <?= ($filterCat === 'all') ? 'btn-vibrant-teal' : 'btn-outline-white' ?>" style="border-radius:var(--radius-pill);padding:10px 22px;">
        All Horizons (<?= count($destinations) ?>)
      </a>
      <a href="<?= BASE_URL ?>/destinations.php?cat=domestic" 
         class="btn <?= ($filterCat === 'domestic') ? 'btn-vibrant-coral' : 'btn-outline-white' ?>" style="border-radius:var(--radius-pill);padding:10px 22px;">
        Incredible India (Domestic)
      </a>
      <a href="<?= BASE_URL ?>/destinations.php?cat=international" 
         class="btn <?= ($filterCat === 'international') ? 'btn-vibrant-sunset' : 'btn-outline-white' ?>" style="border-radius:var(--radius-pill);padding:10px 22px;">
        Global Horizons (International)
      </a>
    </div>

    <!-- Destinations Grid -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(340px, 1fr));gap:30px;">
      <?php foreach ($destinations as $idx => $dest): ?>
        <?php
          $dSlug = strtolower($dest['slug'] ?? '');
          $dTagClass = 'dest-card__tag';
          $dThemeClass = '';
          if (strpos($dSlug, 'kerala') !== false) {
              $dTagClass .= ' tag-kerala';
              $dThemeClass = 'theme-kerala';
          } elseif (strpos($dSlug, 'tamil') !== false) {
              $dTagClass .= ' tag-tamilnadu';
              $dThemeClass = 'theme-tamilnadu';
          } elseif (strpos($dSlug, 'lakshadweep') !== false) {
              $dTagClass .= ' tag-lakshadweep';
              $dThemeClass = 'theme-lakshadweep';
          } elseif (strpos($dSlug, 'andaman') !== false) {
              $dTagClass .= ' tag-andaman';
              $dThemeClass = 'theme-andaman';
          } elseif (strpos($dSlug, 'varanasi') !== false || strpos($dSlug, 'kashi') !== false) {
              $dTagClass .= ' tag-varanasi';
              $dThemeClass = 'theme-varanasi';
          } elseif (strpos($dSlug, 'himalaya') !== false || strpos($dSlug, 'kashmir') !== false || strpos($dSlug, 'manali') !== false) {
              $dTagClass .= ' tag-himalayas';
              $dThemeClass = 'theme-himalayas';
          } elseif (strpos($dSlug, 'hyderabad') !== false) {
              $dTagClass .= ' tag-hyderabad';
              $dThemeClass = 'theme-hyderabad';
          } elseif (strpos($dSlug, 'prayag') !== false || strpos($dSlug, 'ayodhya') !== false) {
              $dTagClass .= ' tag-prayagraj';
              $dThemeClass = 'theme-prayagraj';
          }
        ?>
        <div class="card-perspective reveal-fade-up stagger-<?= ($idx % 3) + 1 ?> <?= e($dThemeClass) ?>">
          <a href="<?= BASE_URL ?>/destination.php?slug=<?= e($dest['slug']) ?>" class="dest-card card-3d glass-specular">
            <img src="<?= e(get_image_url($dest['featured_image'])) ?>" alt="<?= e($dest['alt_text'] ?: $dest['name']) ?>" class="dest-card__bg" loading="lazy" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
            <div class="dest-card__overlay"></div>
            
            <span class="<?= e($dTagClass) ?>"><?= strtoupper(e($dest['category'])) ?></span>

            <div class="dest-card__content">
              <div class="dest-card__location">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span><?= e($dest['region_state']) ?>, <?= e($dest['country']) ?></span>
              </div>
              <h3 class="dest-card__title"><?= e($dest['name']) ?></h3>
              <p class="dest-card__desc"><?= e($dest['tagline'] ?: substr($dest['description'], 0, 110) . '...') ?></p>
              
              <div class="dest-card__action">
                <span>Discover Destination</span>
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
              </div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
