<?php
/**
 * MIDHILA VOYAGES — Tour Packages Catalog
 * Pure White Editorial Hospitality Canvas
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Tour Packages | Curated Domestic, International & Sacred Yatras | Midhila Voyages';
$pageDescription = 'Browse all bespoke tour packages by Midhila Voyages. Filter by destination, category, duration, and budget for hassle-free travel planning.';

$db = getDB();

// Get filter inputs
$categoryFilter = $_GET['category'] ?? '';
$destinationFilter = $_GET['destination'] ?? '';
$searchQuery = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'featured';

// Fetch categories and destinations for filter UI
$cats = $db->query("SELECT * FROM `package_categories` WHERE `is_active` = 1 ORDER BY `display_order` ASC")->fetchAll();
$dests = $db->query("SELECT `id`, `name`, `slug` FROM `destinations` WHERE `is_published` = 1 ORDER BY `name` ASC")->fetchAll();

// Build query
$sql = "
    SELECT p.*, d.name AS destination_name, d.region_state, d.slug AS destination_slug,
           c.name AS category_name, c.slug AS category_slug
    FROM `packages` p
    JOIN `destinations` d ON p.destination_id = d.id
    JOIN `package_categories` c ON p.category_id = c.id
    WHERE p.is_published = 1
";
$params = [];

if (!empty($categoryFilter)) {
    $sql .= " AND (c.slug = ? OR c.name = ?)";
    $params[] = $categoryFilter;
    $params[] = $categoryFilter;
}

if (!empty($destinationFilter)) {
    $sql .= " AND (d.slug = ? OR d.name = ?)";
    $params[] = $destinationFilter;
    $params[] = $destinationFilter;
}

if (!empty($searchQuery)) {
    $sql .= " AND (p.title LIKE ? OR p.short_description LIKE ? OR d.name LIKE ?)";
    $term = "%{$searchQuery}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

// Sorting logic
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY p.is_price_on_request ASC, p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.is_price_on_request ASC, p.price DESC";
        break;
    case 'duration_asc':
        $sql .= " ORDER BY p.duration_days ASC";
        break;
    case 'duration_desc':
        $sql .= " ORDER BY p.duration_days DESC";
        break;
    default:
        $sql .= " ORDER BY p.is_featured DESC, p.display_order ASC, p.id DESC";
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$packages = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Editorial Catalog Hero (Pure White with 35% Subtle Destination Underlayer) -->
<section style="position:relative;background:#FFFFFF;padding-top:calc(var(--header-height) + 50px);padding-bottom:50px;overflow:hidden;">
  <div class="cinematic-bg-layer cinematic-bg-mask-radial" aria-hidden="true">
    <img src="https://images.unsplash.com/photo-1506665531195-3566af2b4dfa?auto=format&fit=crop&w=1920&q=80" 
         alt="" 
         class="cinematic-bg-layer__img" 
         style="opacity:0.25;">
  </div>
  
  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:820px;">
    <h1 class="editorial-headline-large" style="margin-bottom:16px;">
      Signature Tour Packages
    </h1>
    <p class="editorial-subtext" style="margin:0 auto;">
      Every itinerary curated with verified private accommodations, chauffeured transfers, and transparent pricing.
    </p>
  </div>
</section>

<!-- Filter & Search Controls (Non-Sticky, Relative Position) -->
<section style="background:#FFFFFF;padding-bottom:20px;position:relative;z-index:5;">
  <div class="container">
    <div class="filter-bar">
      <form action="<?= BASE_URL ?>/packages.php" method="GET" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)) auto;gap:16px;align-items:end;">
        
        <!-- Destination Filter -->
        <div>
          <label class="editorial-label">Destination</label>
          <select name="destination" class="editorial-select">
            <option value="">All Destinations</option>
            <?php foreach ($dests as $d): ?>
              <option value="<?= e($d['slug']) ?>" <?= ($destinationFilter === $d['slug'] || $destinationFilter === $d['name']) ? 'selected' : '' ?>>
                <?= e($d['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Category Filter -->
        <div>
          <label class="editorial-label">Travel Style</label>
          <select name="category" class="editorial-select">
            <option value="">All Categories</option>
            <?php foreach ($cats as $c): ?>
              <option value="<?= e($c['slug']) ?>" <?= ($categoryFilter === $c['slug'] || $categoryFilter === $c['name']) ? 'selected' : '' ?>>
                <?= e($c['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Keyword Search -->
        <div>
          <label class="editorial-label">Keyword</label>
          <input type="text" name="q" value="<?= e($searchQuery) ?>" placeholder="e.g. Rameswaram, Kerala" class="editorial-input">
        </div>

        <!-- Sorting -->
        <div>
          <label class="editorial-label">Sort Order</label>
          <select name="sort" class="editorial-select">
            <option value="featured" <?= ($sort === 'featured') ? 'selected' : '' ?>>Featured First</option>
            <option value="price_asc" <?= ($sort === 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_desc" <?= ($sort === 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
            <option value="duration_asc" <?= ($sort === 'duration_asc') ? 'selected' : '' ?>>Duration: Shortest</option>
            <option value="duration_desc" <?= ($sort === 'duration_desc') ? 'selected' : '' ?>>Duration: Longest</option>
          </select>
        </div>

        <!-- Action Buttons -->
        <div style="display:flex;gap:10px;">
          <button type="submit" class="btn-cinematic-primary" style="padding:13px 22px;">
            <span>Apply</span>
          </button>
          <a href="<?= BASE_URL ?>/packages.php" class="btn-cinematic-outline" style="padding:12px 18px;">
            <span>Reset</span>
          </a>
        </div>

      </form>
    </div>
  </div>
</section>

<!-- Package Listing Grid (Pure White, 100% Full Opacity Photos) -->
<section style="background:#FFFFFF;padding-bottom:100px;position:relative;">
  <div class="container">
    
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;font-size:0.88rem;color:var(--color-text-muted);">
      <div>
        Showing <strong><?= count($packages) ?></strong> handcrafted itinerary<?= (count($packages) !== 1) ? 's' : '' ?>
      </div>
      <div>
        Need custom dates? <a href="<?= BASE_URL ?>/enquiry.php" style="color:var(--color-gold-deep);font-weight:700;">Request Custom Plan &rarr;</a>
      </div>
    </div>

    <?php if (empty($packages)): ?>
      <div style="text-align:center;padding:80px 20px;border:1px dashed var(--color-border);border-radius:var(--radius-md);background:#FAFAFA;">
        <h3 style="font-size:1.35rem;margin-bottom:8px;">No packages match your specific criteria</h3>
        <p style="color:var(--color-text-muted);margin-bottom:24px;">Try broadening your filter criteria or enquire directly with our travel specialists.</p>
        <a href="<?= BASE_URL ?>/packages.php" class="btn-cinematic-primary">Reset All Filters</a>
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
