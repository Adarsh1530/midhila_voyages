<?php
/**
 * MIDHILA VOYAGES — Dedicated Kerala Tourism Module
 * God's Own Country • The Five Sacred & Scenic Horizons
 * (Pilgrimage, Hill Stations, Backwaters, Beaches, Ayurveda & Wellness)
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$activeLang = current_lang();

// Get active category filter
$activeCategorySlug = isset($_GET['cat']) ? trim($_GET['cat']) : 'all';

// Fetch all 5 Kerala tourism categories
$categoriesStmt = $db->query("SELECT * FROM tourism_categories WHERE is_active = 1 ORDER BY display_order ASC");
$allCategories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Kerala destinations grouped by category
$destsStmt = $db->query("
    SELECT d.*, c.slug as category_slug, c.name_en as cat_name_en, c.name_ml as cat_name_ml, c.name_ta as cat_name_ta
    FROM destinations d
    LEFT JOIN tourism_categories c ON d.tourism_category_id = c.id
    WHERE d.is_published = 1 AND (d.is_kerala_focus = 1 OR d.region_state = 'Kerala')
    ORDER BY d.display_order ASC, d.name ASC
");
$keralaDestinations = $destsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Tour packages linked to Kerala or categories
$packagesStmt = $db->query("
    SELECT p.*, d.name as destination_name, d.slug as destination_slug, c.slug as category_slug,
           c.name_en as cat_name_en, c.name_ml as cat_name_ml, c.name_ta as cat_name_ta
    FROM packages p
    LEFT JOIN destinations d ON p.destination_id = d.id
    LEFT JOIN tourism_categories c ON p.tourism_category_id = c.id
    WHERE p.is_published = 1 AND (p.is_kerala_focus = 1 OR d.region_state = 'Kerala' OR p.is_pilgrimage = 1)
    ORDER BY p.display_order ASC, p.id DESC
");
$keralaPackages = $packagesStmt->fetchAll(PDO::FETCH_ASSOC);

// Filter destinations & packages if a specific category is active
$filteredDestinations = $keralaDestinations;
$filteredPackages = $keralaPackages;
if ($activeCategorySlug !== 'all') {
    $filteredDestinations = array_filter($keralaDestinations, function($d) use ($activeCategorySlug) {
        return ($d['category_slug'] === $activeCategorySlug);
    });
    $filteredPackages = array_filter($keralaPackages, function($p) use ($activeCategorySlug) {
        return ($p['category_slug'] === $activeCategorySlug);
    });
}

// Active category metadata if selected
$currentCategoryMeta = null;
if ($activeCategorySlug !== 'all') {
    foreach ($allCategories as $c) {
        if ($c['slug'] === $activeCategorySlug) {
            $currentCategoryMeta = $c;
            break;
        }
    }
}

$pageTitle = loc($currentCategoryMeta, 'name', __t('nav_kerala_tourism')) . ' | Midhila Voyages';
$pageDescription = loc($currentCategoryMeta, 'description', __t('kerala_subtext'));

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. KERALA TOURISM HERO CHAPTER -->
<section style="position:relative;background:#0F172A;color:#FFFFFF;padding:calc(var(--header-height, 86px) + 48px) 0 70px;overflow:hidden;">
  <div style="position:absolute;inset:0;background-image:url('https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1920&q=80');background-size:cover;background-position:center;opacity:0.25;filter:saturate(120%);pointer-events:none;"></div>
  
  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:920px;margin:0 auto;">
    <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(197,160,89,0.15);border:1px solid #C5A059;padding:6px 18px;border-radius:30px;font-size:0.8rem;letter-spacing:0.12em;text-transform:uppercase;color:#E6CA85;margin-bottom:20px;font-weight:700;">
      <span>🌴 <?= __t('nav_kerala_tourism') ?></span>
      <span>•</span>
      <span>God's Own Country</span>
    </div>

    <h1 style="font-family:'Cinzel',serif;font-size:clamp(2.2rem, 5vw, 3.4rem);line-height:1.2;font-weight:700;color:#FFFFFF;margin-bottom:18px;">
      <?= loc($currentCategoryMeta, 'name', __t('kerala_headline')) ?>
    </h1>

    <p style="font-size:clamp(1rem, 2vw, 1.15rem);color:#CBD5E1;line-height:1.75;max-width:780px;margin:0 auto 32px;">
      <?= loc($currentCategoryMeta, 'description', __t('kerala_subtext')) ?>
    </p>

    <div style="display:flex;justify-content:center;gap:14px;flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>/accommodation.php" class="btn-cinematic-primary" style="padding:14px 28px;font-size:0.95rem;">
        <span><?= __t('nav_accommodation') ?> &amp; Stays &rarr;</span>
      </a>
      <a href="<?= BASE_URL ?>/custom-tour.php" class="btn-cinematic-secondary" style="padding:14px 28px;font-size:0.95rem;background:rgba(255,255,255,0.08);color:#FFFFFF;border-color:rgba(255,255,255,0.3);">
        <span><?= __t('btn_customise_this') ?></span>
      </a>
    </div>
  </div>
</section>

<!-- 2. THE 5 CORE CATEGORY TABS -->
<section style="background:#FFFFFF;border-bottom:1px solid #E2E8F0;position:sticky;top:var(--header-height, 86px);z-index:40;box-shadow:0 4px 12px rgba(15,23,42,0.03);">
  <div class="container" style="padding:14px 20px;">
    <div style="display:flex;align-items:center;gap:10px;overflow-x:auto;padding-bottom:4px;-webkit-overflow-scrolling:touch;">
      <span style="font-size:0.75rem;font-weight:700;text-transform:uppercase;color:#64748B;letter-spacing:0.05em;white-space:nowrap;margin-right:6px;">
        <?= __t('kerala_5_categories') ?>:
      </span>
      
      <a href="<?= BASE_URL ?>/kerala-tourism.php?cat=all" 
         style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:24px;font-size:0.86rem;font-weight:700;text-decoration:none;white-space:nowrap;transition:all 0.2s;<?= ($activeCategorySlug === 'all') ? 'background:#0F172A;color:#FFFFFF;' : 'background:#F1F5F9;color:#334155;' ?>">
        <span>🌟 All Kerala</span>
      </a>

      <?php foreach ($allCategories as $c): ?>
        <?php 
          $isSelected = ($activeCategorySlug === $c['slug']);
          $icon = match($c['slug']) {
            'pilgrimage' => '🛕',
            'hill-stations' => '⛰️',
            'backwaters' => '⛵',
            'beaches' => '🏖️',
            'ayurveda-wellness' => '🌿',
            default => '✨'
          };
        ?>
        <a href="<?= BASE_URL ?>/kerala-tourism.php?cat=<?= urlencode($c['slug']) ?>" 
           style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:24px;font-size:0.86rem;font-weight:700;text-decoration:none;white-space:nowrap;transition:all 0.2s;<?= $isSelected ? 'background:#0F172A;color:#FFFFFF;box-shadow:0 2px 8px rgba(15,23,42,0.15);' : 'background:#F1F5F9;color:#334155;' ?>">
          <span><?= $icon ?></span>
          <span><?= loc($c, 'name') ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 3. FEATURED CATEGORIES SHOWCASE GRID (when 'all' is selected) -->
<?php if ($activeCategorySlug === 'all'): ?>
<section style="padding:60px 0 40px;background:#FAFAFA;">
  <div class="container">
    <div style="text-align:center;max-width:700px;margin:0 auto 40px;">
      <h2 style="font-family:'Cinzel',serif;font-size:2rem;color:#0F172A;margin-bottom:10px;">The Five Pillars of Kerala Experience</h2>
      <p style="font-size:0.92rem;color:#64748B;line-height:1.6;">Each handcrafted trail offers deep serenity, verified sanitised stays, and private transportation.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:24px;">
      <?php foreach ($allCategories as $c): ?>
        <div style="background:#FFFFFF;border-radius:14px;overflow:hidden;border:1px solid #E2E8F0;box-shadow:0 4px 15px rgba(0,0,0,0.04);display:flex;flex-direction:column;transition:transform 0.25s, box-shadow 0.25s;">
          <div style="position:relative;height:180px;background:#0F172A;overflow:hidden;">
            <img src="<?= e($c['featured_image']) ?>" alt="<?= e(loc($c, 'name')) ?>" style="width:100%;height:100%;object-fit:cover;">
            <div style="position:absolute;inset:0;background:linear-gradient(to top, rgba(15,23,42,0.8) 0%, transparent 60%);"></div>
            <div style="position:absolute;bottom:14px;left:16px;right:16px;">
              <h3 style="color:#FFFFFF;font-family:'Cinzel',serif;font-size:1.15rem;margin:0;"><?= loc($c, 'name') ?></h3>
            </div>
          </div>
          <div style="padding:20px;flex-grow:1;display:flex;flex-direction:column;justify-content:space-between;">
            <p style="font-size:0.86rem;color:#64748B;line-height:1.6;margin-bottom:16px;">
              <?= loc($c, 'description') ?>
            </p>
            <div style="display:flex;align-items:center;justify-content:space-between;border-top:1px solid #F1F5F9;padding-top:14px;">
              <a href="<?= BASE_URL ?>/kerala-tourism.php?cat=<?= urlencode($c['slug']) ?>" style="font-size:0.85rem;font-weight:700;color:#0F172A;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                <span>Explore Packages</span> &rarr;
              </a>
              <a href="<?= BASE_URL ?>/accommodation.php?cat=<?= urlencode($c['slug']) ?>" style="font-size:0.82rem;font-weight:600;color:#2563EB;text-decoration:none;">
                <span>View Stays</span>
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- 4. DESTINATIONS IN THIS CATEGORY -->
<section style="padding:60px 0;background:#FFFFFF;">
  <div class="container">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:32px;flex-wrap:wrap;gap:16px;">
      <div>
        <span style="font-size:0.75rem;font-weight:700;text-transform:uppercase;color:#C5A059;letter-spacing:0.08em;display:block;margin-bottom:6px;">
          <?= loc($currentCategoryMeta, 'name', 'Kerala') ?> Sanctuaries
        </span>
        <h2 style="font-family:'Cinzel',serif;font-size:2rem;color:#0F172A;margin:0;">
          Storied Destinations &amp; Regions
        </h2>
      </div>
      <a href="<?= BASE_URL ?>/accommodation.php" class="btn-cinematic-secondary" style="padding:9px 18px;font-size:0.86rem;">
        <span>Filter Accommodations by Region &rarr;</span>
      </a>
    </div>

    <?php if (empty($filteredDestinations)): ?>
      <div style="text-align:center;padding:50px 20px;background:#F8FAFC;border-radius:12px;border:1px dashed #CBD5E1;">
        <p style="font-size:1rem;color:#64748B;">No destinations listed under this filter yet.</p>
      </div>
    <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(260px, 1fr));gap:24px;">
        <?php foreach ($filteredDestinations as $d): ?>
          <div style="border-radius:12px;overflow:hidden;background:#FFFFFF;border:1px solid #E2E8F0;box-shadow:0 2px 10px rgba(0,0,0,0.03);display:flex;flex-direction:column;">
            <div style="position:relative;height:170px;background:#0F172A;overflow:hidden;">
              <img src="<?= e($d['featured_image'] ?: 'https://images.unsplash.com/photo-1596176530529-78163a4f7af2?auto=format&fit=crop&w=600&q=80') ?>" 
                   alt="<?= e(loc($d, 'name')) ?>" style="width:100%;height:100%;object-fit:cover;">
              <div style="position:absolute;top:12px;left:12px;background:rgba(15,23,42,0.85);color:#FFFFFF;padding:3px 10px;border-radius:6px;font-size:0.7rem;font-weight:700;backdrop-filter:blur(4px);">
                <?= e($d['cat_name_en'] ?: 'Kerala') ?>
              </div>
            </div>
            <div style="padding:18px;flex-grow:1;display:flex;flex-direction:column;justify-content:space-between;">
              <div>
                <h3 style="font-size:1.15rem;font-weight:700;color:#0F172A;margin-bottom:6px;"><?= loc($d, 'name') ?></h3>
                <p style="font-size:0.82rem;color:#64748B;line-height:1.55;margin-bottom:12px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                  <?= loc($d, 'description') ?>
                </p>
                <?php if (!empty($d['highlights'])): ?>
                  <div style="font-size:0.75rem;color:#334155;margin-bottom:14px;background:#F8FAFC;padding:8px 10px;border-radius:6px;border-left:2px solid #C5A059;">
                    <strong>Key Highlights:</strong> <?= e($d['highlights']) ?>
                  </div>
                <?php endif; ?>
              </div>
              <div style="display:flex;gap:8px;padding-top:12px;border-top:1px solid #F1F5F9;">
                <a href="<?= BASE_URL ?>/accommodation.php?destination_id=<?= (int)$d['id'] ?>" class="btn-cinematic-secondary" style="flex:1;text-align:center;padding:7px 10px;font-size:0.78rem;">
                  <span>Stays in <?= loc($d, 'name') ?></span>
                </a>
                <a href="<?= BASE_URL ?>/custom-tour.php?destination_id=<?= (int)$d['id'] ?>" class="btn-cinematic-primary" style="flex:1;text-align:center;padding:7px 10px;font-size:0.78rem;">
                  <span>Plan Yatra</span>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- 5. TOUR PACKAGES FOR THIS CATEGORY -->
<section style="padding:60px 0 80px;background:#F8FAFC;border-top:1px solid #E2E8F0;">
  <div class="container">
    <div style="text-align:center;max-width:760px;margin:0 auto 40px;">
      <span style="font-size:0.75rem;font-weight:700;text-transform:uppercase;color:#C5A059;letter-spacing:0.08em;display:block;margin-bottom:8px;">
        Curated Itineraries
      </span>
      <h2 style="font-family:'Cinzel',serif;font-size:2.2rem;color:#0F172A;margin-bottom:10px;">
        Kerala Handcrafted Tour Packages
      </h2>
      <p style="font-size:0.92rem;color:#64748B;line-height:1.6;">
        Every package includes verified accommodations, dedicated chauffeur-driven private vehicles, and elderly-friendly pacing.
      </p>
    </div>

    <?php if (empty($filteredPackages)): ?>
      <div style="text-align:center;padding:50px 20px;background:#FFFFFF;border-radius:12px;border:1px dashed #CBD5E1;">
        <p style="font-size:1.1rem;font-weight:600;color:#0F172A;margin-bottom:8px;">Custom itineraries available on request</p>
        <p style="font-size:0.86rem;color:#64748B;margin-bottom:20px;">We tailor exact routes and dates to your family or group requirements.</p>
        <a href="<?= BASE_URL ?>/custom-tour.php" class="btn-cinematic-primary">
          <span>Request Customised Itinerary &rarr;</span>
        </a>
      </div>
    <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:28px;">
        <?php foreach ($filteredPackages as $pkg): ?>
          <div style="background:#FFFFFF;border-radius:14px;overflow:hidden;border:1px solid #E2E8F0;box-shadow:0 4px 16px rgba(15,23,42,0.05);display:flex;flex-direction:column;">
            <div style="position:relative;height:210px;background:#0F172A;overflow:hidden;">
              <img src="<?= e($pkg['featured_image'] ?: 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=800&q=80') ?>" 
                   alt="<?= e(loc($pkg, 'title')) ?>" style="width:100%;height:100%;object-fit:cover;">
              <div style="position:absolute;top:12px;right:12px;background:rgba(15,23,42,0.9);color:#C5A059;padding:4px 10px;border-radius:6px;font-size:0.75rem;font-weight:700;backdrop-filter:blur(4px);border:1px solid #C5A059;">
                <?= (int)$pkg['duration_days'] ?> Days / <?= (int)$pkg['duration_nights'] ?> Nights
              </div>
              <div style="position:absolute;bottom:12px;left:12px;background:rgba(15,23,42,0.85);color:#FFFFFF;padding:3px 10px;border-radius:6px;font-size:0.72rem;font-weight:600;">
                📍 <?= e($pkg['destination_name'] ?: 'Kerala') ?>
              </div>
            </div>

            <div style="padding:22px;flex-grow:1;display:flex;flex-direction:column;justify-content:space-between;">
              <div>
                <h3 style="font-size:1.2rem;font-weight:700;color:#0F172A;line-height:1.35;margin-bottom:8px;">
                  <?= loc($pkg, 'title') ?>
                </h3>
                <p style="font-size:0.86rem;color:#64748B;line-height:1.6;margin-bottom:16px;">
                  <?= loc($pkg, 'short_description') ?>
                </p>

                <!-- Key Inclusions -->
                <?php if (!empty($pkg['inclusions'])): ?>
                  <div style="font-size:0.78rem;color:#334155;background:#F8FAFC;padding:10px 12px;border-radius:8px;margin-bottom:16px;">
                    <strong style="color:#0F172A;">Inclusions:</strong>
                    <div style="margin-top:4px;color:#64748B;"><?= e(mb_strimwidth($pkg['inclusions'], 0, 110, '...')) ?></div>
                  </div>
                <?php endif; ?>
              </div>

              <div>
                <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:16px;border-top:1px solid #F1F5F9;padding-top:14px;">
                  <div>
                    <span style="font-size:0.72rem;text-transform:uppercase;color:#64748B;font-weight:600;display:block;">Starting From</span>
                    <strong style="font-size:1.35rem;color:#059669;font-weight:800;"><?= format_price((float)$pkg['starting_price']) ?></strong>
                    <span style="font-size:0.75rem;color:#64748B;">/ person</span>
                  </div>
                  <a href="<?= BASE_URL ?>/package.php?id=<?= (int)$pkg['id'] ?>" class="btn-cinematic-secondary" style="padding:8px 14px;font-size:0.82rem;">
                    <span><?= __t('btn_view_details') ?> &rarr;</span>
                  </a>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                  <a href="<?= BASE_URL ?>/accommodation.php?destination_id=<?= (int)$pkg['destination_id'] ?>" class="btn-cinematic-secondary" style="text-align:center;padding:9px;font-size:0.8rem;">
                    <span>Stays</span>
                  </a>
                  <a href="<?= BASE_URL ?>/enquiry.php?package_id=<?= (int)$pkg['id'] ?>" class="btn-cinematic-primary" style="text-align:center;padding:9px;font-size:0.8rem;">
                    <span>Enquire</span>
                  </a>
                </div>
              </div>

            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<!-- 6. BOTTOM CALL TO ACTION: CUSTOMISED ACC & TOUR BOOKING -->
<section style="background:#0F172A;color:#FFFFFF;padding:60px 0;text-align:center;">
  <div class="container" style="max-width:760px;margin:0 auto;">
    <h2 style="font-family:'Cinzel',serif;font-size:2rem;color:#FFFFFF;margin-bottom:14px;">
      Looking for a Specialised Itinerary?
    </h2>
    <p style="font-size:0.95rem;color:#CBD5E1;line-height:1.7;margin-bottom:28px;">
      Whether you wish to coordinate elderly pilgrimage darshans at Guruvayur, a romantic private houseboat night in Alleppey, or a complete corporate team offsite in Munnar, our directors coordinate every detail directly.
    </p>
    <div style="display:flex;justify-content:center;gap:14px;flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>/accommodation.php" class="btn-cinematic-primary" style="padding:13px 26px;">
        <span><?= __t('nav_accommodation') ?> Finder &rarr;</span>
      </a>
      <a href="<?= BASE_URL ?>/corporate-events.php" class="btn-cinematic-secondary" style="padding:13px 26px;color:#FFFFFF;border-color:rgba(255,255,255,0.3);">
        <span><?= __t('nav_corporate_events') ?></span>
      </a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
