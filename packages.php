<?php
/**
 * MIDHILA VOYAGES — Tour Packages Catalog
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Tour Packages | Domestic, International & Pilgrimage Itineraries | Midhila Voyages';
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
        $sql .= " ORDER BY p.is_price_on_request ASC, p.starting_price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.is_price_on_request ASC, p.starting_price DESC";
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

<!-- Banner Header -->
<section style="position:relative;background-color:var(--color-navy-deep);padding-top:calc(var(--header-height) + 50px);padding-bottom:70px;color:#FFF;overflow:hidden;">
  <div style="position:absolute;inset:0;opacity:0.25;">
    <img src="https://images.unsplash.com/photo-1506665531195-3566af2b4dfa?auto=format&fit=crop&w=1800&q=80" alt="Exotic landscape" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
    <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(6,16,30,0.7) 0%, var(--color-navy-deep) 100%);"></div>
  </div>
  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:760px;">
    <div class="float-slow" style="display:inline-block;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.2em;color:var(--color-lavender);margin-bottom:12px;font-weight:700;background:rgba(196,181,253,0.12);padding:6px 16px;border-radius:var(--radius-pill);border:1px solid rgba(196,181,253,0.3);">✨ Tailored Expeditions</div>
    <h1 class="editorial-headline" style="color:#FFF;font-size:clamp(2.4rem, 4vw, 3.6rem);margin-bottom:16px;text-shadow:0 0 20px rgba(196,181,253,0.25);">Curated Tour Packages</h1>
    <p class="lead-text" style="color:#CBD5E0;">Explore our diverse portfolio of domestic holidays, international vacations, and sacred pilgrimage circuits.</p>
  </div>
</section>

<!-- Filter & Search Controls (Frosted Glass Bar) -->
<section style="background:rgba(255, 255, 255, 0.72);backdrop-filter:blur(20px) saturate(180%);border-bottom:1px solid rgba(196, 181, 253, 0.35);padding:24px 0;box-shadow:inset 0 1px 1px 0 rgba(255,255,255,0.8), 0 8px 24px rgba(10, 17, 40, 0.05);position:relative;z-index:10;">
  <div class="container">
    <form action="<?= BASE_URL ?>/packages.php" method="GET" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(190px, 1fr)) auto;gap:14px;align-items:end;">
      
      <!-- Destination Filter -->
      <div>
        <label style="display:block;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Destination</label>
        <select name="destination" style="width:100%;padding:10px 14px;border:1.5px solid rgba(196, 181, 253, 0.4);border-radius:var(--radius-md);font-size:0.9rem;background:rgba(255,255,255,0.85);outline:none;">
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
        <label style="display:block;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Experience Style</label>
        <select name="category" style="width:100%;padding:10px 14px;border:1.5px solid rgba(196, 181, 253, 0.4);border-radius:var(--radius-md);font-size:0.9rem;background:rgba(255,255,255,0.85);outline:none;">
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
        <label style="display:block;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Keyword</label>
        <input type="text" name="q" value="<?= e($searchQuery) ?>" placeholder="e.g. Houseboat, Desert, Temple" style="width:100%;padding:10px 14px;border:1.5px solid rgba(196, 181, 253, 0.4);border-radius:var(--radius-md);font-size:0.9rem;background:rgba(255,255,255,0.85);outline:none;">
      </div>

      <!-- Sorting -->
      <div>
        <label style="display:block;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;color:var(--color-navy);margin-bottom:6px;">Sort By</label>
        <select name="sort" style="width:100%;padding:10px 14px;border:1.5px solid rgba(196, 181, 253, 0.4);border-radius:var(--radius-md);font-size:0.9rem;background:rgba(255,255,255,0.85);outline:none;">
          <option value="featured" <?= ($sort === 'featured') ? 'selected' : '' ?>>Featured First</option>
          <option value="price_asc" <?= ($sort === 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
          <option value="price_desc" <?= ($sort === 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
          <option value="duration_asc" <?= ($sort === 'duration_asc') ? 'selected' : '' ?>>Duration: Shortest</option>
          <option value="duration_desc" <?= ($sort === 'duration_desc') ? 'selected' : '' ?>>Duration: Longest</option>
        </select>
      </div>

      <!-- Action Buttons -->
      <div style="display:flex;gap:8px;">
        <button type="submit" class="btn btn-primary" style="padding:10px 20px;">
          <span>Filter</span>
        </button>
        <a href="<?= BASE_URL ?>/packages.php" class="btn btn-outline-gold" style="padding:10px 16px;">
          <span>Reset</span>
        </a>
      </div>

    </form>
  </div>
</section>

<!-- Package Listing Grid -->
<section class="section-padding" style="background-color:transparent;">
  <div class="container">
    
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
      <p style="font-size:0.92rem;color:var(--color-text-muted);">
        Showing <strong><?= count($packages) ?></strong> active tour package<?= (count($packages) !== 1) ? 's' : '' ?>
      </p>
    </div>

    <?php if (empty($packages)): ?>
      <div class="glass-card" style="text-align:center;padding:70px 20px;border-radius:var(--radius-lg);">
        <h3 style="font-size:1.4rem;color:var(--color-navy);margin-bottom:8px;">No packages match your criteria</h3>
        <p style="color:var(--color-text-muted);margin-bottom:24px;">Try loosening your filters or enquire with our specialists for a bespoke itinerary.</p>
        <a href="<?= BASE_URL ?>/packages.php" class="btn btn-primary">Clear Filters</a>
      </div>
    <?php else: ?>
      <div class="package-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:28px;">
        <?php foreach ($packages as $package): ?>
          <?php include __DIR__ . '/includes/package-card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
