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
    SELECT p.*, d.name AS destination_name, d.name_ml AS destination_name_ml, d.name_ta AS destination_name_ta, d.region_state, 
           c.name AS category_name, c.name_ml AS category_name_ml, c.name_ta AS category_name_ta, c.slug AS category_slug
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
$destDisplayName = loc($dest, 'name');

$waDestMsg = "Hello MIDHILA VOYAGES, I am interested in visiting {$destDisplayName}. Please share available tour packages and customized itinerary options.";
$waDestUrl = build_whatsapp_url($agencyWhatsApp, $waDestMsg);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Leaflet Interactive Maps (OpenStreetMap Permitted Tiles) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
.osm-cat-btn {
  padding: 8px 18px;
  font-size: 0.82rem;
  font-weight: 600;
  border-radius: var(--radius-pill);
  border: 1px solid var(--color-border);
  background: #FFFFFF;
  color: var(--color-text-secondary);
  cursor: pointer;
  transition: all 0.2s ease;
}
.osm-cat-btn:hover, .osm-cat-btn.is-active {
  background: var(--color-navy);
  color: #FFFFFF;
  border-color: var(--color-navy);
}
.dest-place-card {
  background: #FFFFFF;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  padding: 14px 16px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.dest-place-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-card);
}
</style>

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
      <a href="<?= BASE_URL ?>/index.php" style="color:var(--color-text-secondary);"><?= e(__t('nav_home', 'Home')) ?></a> <span>/</span>
      <a href="<?= BASE_URL ?>/destinations.php" style="color:var(--color-text-secondary);"><?= e(__t('nav_destinations', 'Destinations')) ?></a> <span>/</span>
      <span style="color:var(--color-text-main);font-weight:700;"><?= e($destDisplayName) ?></span>
    </nav>

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap;">
      <span class="chapter-number">
        <?= strtoupper(e($dest['category'])) ?> &bull; <?= e($dest['region_state']) ?>, <?= e($dest['country']) ?>
      </span>
      <span style="font-size:0.8rem;background:#FAFAFA;border:1px solid var(--color-border);padding:4px 14px;border-radius:var(--radius-pill);color:var(--color-text-secondary);font-weight:600;">
        <?= count($packages) ?> <?= e(__t('all_itineraries', 'Active Itineraries')) ?>
      </span>
    </div>

    <h1 class="editorial-headline-large" style="margin-bottom:12px;">
      <?= e($destDisplayName) ?>
    </h1>
    
    <?php if (!empty($dest['tagline'])): ?>
      <p class="editorial-subtext" style="max-width:800px;"><?= e(loc($dest, 'tagline', $dest['tagline'])) ?></p>
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
               alt="<?= e($dest['alt_text'] ?: $destDisplayName) ?>" 
               style="width:100%;height:100%;object-fit:cover;opacity:1 !important;" 
               onerror="this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
        </div>

        <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:36px;box-shadow:var(--shadow-ambient);margin-bottom:40px;">
          <h2 style="font-size:1.6rem;margin-bottom:16px;"><?= e(__t('journey_synopsis', 'The Essence of')) ?> <?= e($destDisplayName) ?></h2>
          <div style="font-size:1.02rem;line-height:1.85;color:var(--color-text-body);">
            <?= nl2br(e(loc($dest, 'description'))) ?>
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
              <span><?= e(__t('btn_whatsapp_us', 'Enquire for')) ?> <?= e($destDisplayName) ?></span>
            </a>

            <a href="<?= BASE_URL ?>/custom-tour.php?destination_id=<?= (int)$dest['id'] ?>" class="btn-cinematic-primary" style="width:100%;justify-content:center;">
              <span><?= e(__t('btn_plan_trip', 'Plan Custom Journey')) ?></span>
            </a>
          </div>

        </div>
      </div>

    </div>

  </div>
</section>

<!-- Interactive OpenStreetMap Directory & Grounded Travel Map -->
<div class="section-divider-hairline"></div>
<section class="section-padding" style="background:#FAFAFA;border-bottom:1px solid var(--color-border);">
  <div class="container">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:28px;flex-wrap:wrap;gap:16px;">
      <div>
        <span class="chapter-number">OPENSTREETMAP VERIFIED DIRECTORY</span>
        <h2 class="editorial-headline-large" style="font-size:2rem;margin-top:6px;">
          Explore <?= e($destDisplayName) ?> on the Map
        </h2>
        <p class="editorial-subtext" style="font-size:0.95rem;max-width:700px;margin-top:6px;">
          Live mapped hotels, certified resorts, regional dining spots, and cultural landmarks in <?= e($destDisplayName) ?>.
        </p>
      </div>

      <!-- Category Filter Pills -->
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <button type="button" class="osm-cat-btn is-active" onclick="filterDestCategory('all', this)">All Highlights</button>
        <button type="button" class="osm-cat-btn" onclick="filterDestCategory('hotel_resort', this)">Stays &amp; Resorts</button>
        <button type="button" class="osm-cat-btn" onclick="filterDestCategory('dining', this)">Dining &amp; Cafes</button>
        <button type="button" class="osm-cat-btn" onclick="filterDestCategory('attraction', this)">Attractions</button>
      </div>
    </div>

    <!-- Map & Places Split View -->
    <div style="display:grid;grid-template-columns:1.6fr 1fr;gap:28px;align-items:start;">
      <!-- Leaflet Canvas -->
      <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);overflow:hidden;box-shadow:var(--shadow-card);position:relative;">
        <div id="destLeafletMap" style="height:520px;width:100%;position:relative;z-index:1;"></div>
      </div>

      <!-- Places List Sidebar -->
      <div>
        <div style="background:#FFFFFF;border:1px solid var(--color-border);border-radius:var(--radius-md);padding:24px;box-shadow:var(--shadow-ambient);">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid var(--color-border);padding-bottom:12px;">
            <h3 style="font-size:1.1rem;font-weight:700;margin:0;color:var(--color-text-main);">
              Verified Spots (<span id="destPlacesCount">...</span>)
            </h3>
            <a href="<?= BASE_URL ?>/custom-tour.php?destination_id=<?= (int)$dest['id'] ?>" class="btn-cinematic-primary" style="padding:6px 14px;font-size:0.78rem;">
              <span>Plan Custom Trip &rarr;</span>
            </a>
          </div>

          <!-- Places Scroll List -->
          <div id="destPlacesList" style="max-height:430px;overflow-y:auto;display:flex;flex-direction:column;gap:10px;padding-right:4px;">
            <div style="text-align:center;padding:30px 10px;color:var(--color-text-muted);font-size:0.88rem;">
              Loading verified places...
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- AI Grounded Planner Banner -->
    <div style="margin-top:28px;background:#FFFFFF;border:1.5px solid var(--color-gold);border-radius:var(--radius-md);padding:24px 28px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:18px;box-shadow:var(--shadow-card);">
      <div style="max-width:700px;">
        <div style="font-size:0.75rem;text-transform:uppercase;color:var(--color-gold-deep);font-weight:700;letter-spacing:0.08em;margin-bottom:4px;">
          AI TRAVEL ASSISTANT &bull; GROUNDED ON VERIFIED PLACES
        </div>
        <h4 style="font-size:1.25rem;font-weight:700;color:var(--color-navy);margin-bottom:6px;">
          Want a Custom Day-by-Day Itinerary for <?= e($destDisplayName) ?>?
        </h4>
        <p style="font-size:0.88rem;color:var(--color-text-body);line-height:1.6;margin:0;">
          Our AI system uses only verified accommodations, real GPS-mapped dining, and approved attractions to build a tailored proposal. Room availability is verified directly with providers by Midhila Voyages directors.
        </p>
      </div>
      <a href="<?= BASE_URL ?>/custom-tour.php?destination_id=<?= (int)$dest['id'] ?>" class="btn-cinematic-primary" style="padding:12px 22px;">
        <span>Create Tailored Itinerary &rarr;</span>
      </a>
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
          <?= e($destDisplayName) ?> <?= e(__t('nav_packages', 'Itineraries')) ?>
        </h2>
      </div>

      <a href="<?= BASE_URL ?>/packages.php?destination=<?= urlencode($dest['slug']) ?>" class="btn-cinematic-outline">
        <span><?= e(__t('btn_filter', 'View All Filters')) ?></span>
      </a>
    </div>

    <?php if (empty($packages)): ?>
      <div style="padding:60px 20px;border:1px dashed var(--color-border);border-radius:var(--radius-md);text-align:center;background:#FAFAFA;">
        <p style="color:var(--color-text-muted);margin-bottom:16px;">We design bespoke private itineraries for <?= e($destDisplayName) ?> on request.</p>
        <a href="<?= e($waDestUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-solid"><?= e(__t('btn_whatsapp_us', 'Chat with Our Specialist')) ?></a>
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

<!-- Leaflet & OSM Interactive Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="<?= ASSETS_URL ?>/js/osm-leaflet-map.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var destId = <?= (int)$dest['id'] ?>;
  var destLat = <?= (float)($dest['latitude'] ?: 10.0889) ?>;
  var destLng = <?= (float)($dest['longitude'] ?: 77.0595) ?>;

  var mapInstance = new MidhilaMap({
    containerId: 'destLeafletMap',
    destinationId: destId,
    latitude: destLat,
    longitude: destLng,
    zoom: 13,
    onSelectPlace: function(place) {
      window.location.href = '<?= BASE_URL ?>/custom-tour.php?destination_id=' + destId;
    }
  });

  mapInstance.onPlacesLoaded = function(places) {
    var countEl = document.getElementById('destPlacesCount');
    var listEl = document.getElementById('destPlacesList');

    countEl.innerText = places.length;
    if (places.length === 0) {
      listEl.innerHTML = '<div style="text-align:center;padding:24px 10px;color:var(--color-text-muted);font-size:0.85rem;">No mapped places currently recorded for this filter.</div>';
      return;
    }

    listEl.innerHTML = '';
    places.forEach(function(p) {
      var card = document.createElement('div');
      card.className = 'dest-place-card';

      var badgeBg = (p.category === 'hotel' || p.category === 'resort') ? '#EFF6FF;color:#1E40AF;' : ((p.category === 'restaurant' || p.category === 'cafe') ? '#FFFBEB;color:#92400E;' : '#ECFDF5;color:#065F46;');
      var priceHtml = (p.indicative_price > 0)
        ? '<div style="font-weight:700;color:var(--color-text-main);font-size:0.82rem;">₹' + Number(p.indicative_price).toLocaleString('en-IN') + ' <span style="font-size:0.72rem;font-weight:400;color:var(--color-text-muted);">' + (p.price_unit || '') + '</span></div>'
        : '<div style="font-size:0.75rem;color:var(--color-text-muted);">Verified on Enquiry</div>';

      card.innerHTML = '<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px;">'
        + '<div style="font-weight:700;font-size:0.88rem;color:var(--color-text-main);line-height:1.3;">' + p.name + '</div>'
        + '<span style="font-size:0.68rem;padding:2px 8px;border-radius:12px;font-weight:700;background:' + badgeBg + ';text-transform:capitalize;">' + (p.category || 'Spot') + '</span>'
        + '</div>'
        + (p.address ? '<div style="font-size:0.75rem;color:var(--color-text-muted);margin-bottom:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">📍 ' + p.address + '</div>' : '')
        + '<div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px;border-top:1px solid var(--color-border);padding-top:6px;">'
        + priceHtml
        + '<a href="<?= BASE_URL ?>/custom-tour.php?destination_id=' + destId + '" style="font-size:0.75rem;color:var(--color-gold-deep);font-weight:700;">+ Choose &rarr;</a>'
        + '</div>';

      listEl.appendChild(card);
    });
  };

  window.filterDestCategory = function(cat, btn) {
    document.querySelectorAll('.osm-cat-btn').forEach(function(b) { b.classList.remove('is-active'); });
    if (btn) btn.classList.add('is-active');
    if (mapInstance) {
      mapInstance.filterCategory(cat);
    }
  };
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
