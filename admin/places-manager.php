<?php
/**
 * MIDHILA VOYAGES — Mapped Places & OpenStreetMap Manager
 * 
 * Centralised administration for OpenStreetMap retrieved and verified places:
 * - Hotels, Resorts, Restaurants, Cafes, and Tourist Attractions
 * - Filter by Destination, Category, and Approval Status
 * - Synchronize live places via Overpass API with rate limiting
 * - Edit pricing, room categories, facilities, and food options
 * - Approve, hide, or remove external records
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/overpass_service.php';

require_admin_login();
require_role(['super_admin', 'travel_manage']);

$db = getDB();
$adminId = $_SESSION['admin_user_id'] ?? null;

// Fetch destinations for filter
$destinations = $db->query("SELECT id, name, region_state FROM destinations ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Filters
$destFilter = isset($_GET['destination_id']) ? (int)$_GET['destination_id'] : 0;
$catFilter  = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : 'all'; // all, approved, pending, hidden
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

// -------------------------------------------------------------
// POST ACTIONS: SYNC, APPROVE, HIDE, DELETE, EDIT
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token()) {
        $action = $_POST['action'] ?? '';

        // 1. SYNC FROM OPENSTREETMAP
        if ($action === 'sync_osm') {
            $syncDestId = (int)($_POST['sync_destination_id'] ?? 0);
            if ($syncDestId > 0) {
                $res = OverpassService::getPlacesForDestination($syncDestId, 'all', true);
                if ($res['success']) {
                    $newCount = $res['newly_synced'] ?? count($res['places']);
                    set_flash_message('success', "Synced successfully! {$newCount} places retrieved and updated from OpenStreetMap.");
                    log_activity($adminId, 'osm_sync', "Synchronized OSM places for destination ID #{$syncDestId}");
                } else {
                    set_flash_message('warning', $res['error'] ?? 'Overpass sync returned local cache.');
                }
            } else {
                set_flash_message('error', 'Please choose a destination to synchronize.');
            }
            header('Location: ' . ADMIN_URL . '/places-manager.php' . ($syncDestId ? '?destination_id=' . $syncDestId : ''));
            exit;
        }

        // 2. APPROVE / HIDE / VERIFY TOGGLE
        if ($action === 'update_status') {
            $placeId = (int)($_POST['place_id'] ?? 0);
            $newStatus = (int)($_POST['new_status'] ?? 1); // 1 = approved, -1 = hidden, 0 = pending
            if ($placeId > 0) {
                $upd = $db->prepare("UPDATE external_places SET is_approved = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $upd->execute([$newStatus, $placeId]);
                set_flash_message('success', 'Place status updated.');
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }

        if ($action === 'toggle_verify') {
            $placeId = (int)($_POST['place_id'] ?? 0);
            $val = (int)($_POST['is_verified'] ?? 0);
            if ($placeId > 0) {
                $upd = $db->prepare("UPDATE external_places SET is_verified = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $upd->execute([$val ? 1 : 0, $placeId]);
                set_flash_message('success', 'Verification badge updated.');
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }

        // 3. EDIT PLACE DETAILS
        if ($action === 'edit_place') {
            $placeId = (int)($_POST['place_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $nameMl = trim($_POST['name_ml'] ?? '');
            $nameTa = trim($_POST['name_ta'] ?? '');
            $category = trim($_POST['category'] ?? 'hotel');
            $subType = trim($_POST['sub_type'] ?? '');
            $price = (float)($_POST['indicative_price'] ?? 0.0);
            $priceUnit = trim($_POST['price_unit'] ?? 'per room / night');
            $address = trim($_POST['address'] ?? '');
            $phone = trim($_POST['contact_phone'] ?? '');
            $website = trim($_POST['website'] ?? '');
            $facilities = trim($_POST['facilities'] ?? '');
            $foodOptions = trim($_POST['food_options'] ?? '');
            $roomCats = trim($_POST['room_categories'] ?? '');

            // Convert comma-separated to JSON array
            $facArr = array_values(array_filter(array_map('trim', explode(',', $facilities))));
            $foodArr = array_values(array_filter(array_map('trim', explode(',', $foodOptions))));
            $roomArr = array_values(array_filter(array_map('trim', explode(',', $roomCats))));

            if ($placeId > 0 && !empty($name)) {
                $upd = $db->prepare("
                    UPDATE external_places SET
                        name = ?,
                        name_ml = NULLIF(?, ''),
                        name_ta = NULLIF(?, ''),
                        category = ?,
                        sub_type = ?,
                        indicative_price = ?,
                        price_unit = ?,
                        address = NULLIF(?, ''),
                        contact_phone = NULLIF(?, ''),
                        website = NULLIF(?, ''),
                        facilities = ?,
                        food_options = ?,
                        room_categories = ?,
                        is_verified = 1,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                $upd->execute([
                    $name, $nameMl, $nameTa, $category, $subType,
                    $price, $priceUnit, $address, $phone, $website,
                    !empty($facArr) ? json_encode($facArr) : null,
                    !empty($foodArr) ? json_encode($foodArr) : null,
                    !empty($roomArr) ? json_encode($roomArr) : null,
                    $placeId,
                ]);
                set_flash_message('success', 'Place details updated successfully.');
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }

        // 4. DELETE / REMOVE PLACE
        if ($action === 'delete_place') {
            $placeId = (int)($_POST['place_id'] ?? 0);
            if ($placeId > 0) {
                $del = $db->prepare("DELETE FROM external_places WHERE id = ?");
                $del->execute([$placeId]);
                set_flash_message('success', 'Record removed from directory.');
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
}

// -------------------------------------------------------------
// QUERY SUMMARY STATS
// -------------------------------------------------------------
$totalPlaces = (int)$db->query("SELECT COUNT(*) FROM external_places")->fetchColumn();
$totalHotels = (int)$db->query("SELECT COUNT(*) FROM external_places WHERE category IN ('hotel', 'resort')")->fetchColumn();
$totalDining = (int)$db->query("SELECT COUNT(*) FROM external_places WHERE category IN ('restaurant', 'cafe')")->fetchColumn();
$totalAttractions = (int)$db->query("SELECT COUNT(*) FROM external_places WHERE category = 'attraction'")->fetchColumn();
$totalApproved = (int)$db->query("SELECT COUNT(*) FROM external_places WHERE is_approved = 1")->fetchColumn();

// -------------------------------------------------------------
// BUILD FILTERED LIST
// -------------------------------------------------------------
$whereClauses = ["1=1"];
$params = [];

if ($destFilter > 0) {
    $whereClauses[] = "p.destination_id = ?";
    $params[] = $destFilter;
}

if ($catFilter !== 'all') {
    if ($catFilter === 'hotel_resort') {
        $whereClauses[] = "p.category IN ('hotel', 'resort')";
    } elseif ($catFilter === 'dining') {
        $whereClauses[] = "p.category IN ('restaurant', 'cafe')";
    } else {
        $whereClauses[] = "p.category = ?";
        $params[] = $catFilter;
    }
}

if ($statusFilter === 'approved') {
    $whereClauses[] = "p.is_approved = 1";
} elseif ($statusFilter === 'pending') {
    $whereClauses[] = "p.is_approved = 0";
} elseif ($statusFilter === 'hidden') {
    $whereClauses[] = "p.is_approved = -1";
}

if (!empty($searchQuery)) {
    $whereClauses[] = "(p.name LIKE ? OR p.address LIKE ? OR p.sub_type LIKE ?)";
    $like = '%' . $searchQuery . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$whereSql = implode(" AND ", $whereClauses);

// Pagination
$page = max(1, (int)($_GET['p'] ?? 1));
$limit = 30;
$offset = ($page - 1) * $limit;

$countStmt = $db->prepare("SELECT COUNT(*) FROM external_places p WHERE {$whereSql}");
$countStmt->execute($params);
$filteredCount = (int)$countStmt->fetchColumn();
$totalPages = ceil($filteredCount / $limit);

$listStmt = $db->prepare("
    SELECT p.*, d.name AS destination_name
    FROM external_places p
    LEFT JOIN destinations d ON p.destination_id = d.id
    WHERE {$whereSql}
    ORDER BY p.is_verified DESC, p.is_approved DESC, p.id DESC
    LIMIT {$limit} OFFSET {$offset}
");
$listStmt->execute($params);
$placesList = $listStmt->fetchAll(PDO::FETCH_ASSOC);

$adminTitle = 'OpenStreetMap Places & Dining | MIDHILA VOYAGES';
$adminHeading = 'OpenStreetMap Places & Dining Manager';

require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- Statistics Overview -->
<div class="admin-stats-grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));margin-bottom:24px;">
  <div class="admin-stat-card">
    <div>
      <div class="admin-stat-label">Total Mapped</div>
      <div class="admin-stat-value"><?= number_format($totalPlaces) ?></div>
      <div class="admin-stat-sub">Across destinations</div>
    </div>
    <div style="font-size:2rem;color:var(--admin-navy);">🗺️</div>
  </div>

  <div class="admin-stat-card">
    <div>
      <div class="admin-stat-label">Hotels &amp; Resorts</div>
      <div class="admin-stat-value" style="color:#2563EB;"><?= number_format($totalHotels) ?></div>
      <div class="admin-stat-sub">Verified stays</div>
    </div>
    <div style="font-size:2rem;color:#2563EB;">🏨</div>
  </div>

  <div class="admin-stat-card">
    <div>
      <div class="admin-stat-label">Dining &amp; Cafes</div>
      <div class="admin-stat-value" style="color:#D97706;"><?= number_format($totalDining) ?></div>
      <div class="admin-stat-sub">Restaurants &amp; bistros</div>
    </div>
    <div style="font-size:2rem;color:#D97706;">🍽️</div>
  </div>

  <div class="admin-stat-card">
    <div>
      <div class="admin-stat-label">Attractions</div>
      <div class="admin-stat-value" style="color:#059669;"><?= number_format($totalAttractions) ?></div>
      <div class="admin-stat-sub">Viewpoints &amp; sacred sites</div>
    </div>
    <div style="font-size:2rem;color:#059669;">🏞️</div>
  </div>

  <div class="admin-stat-card">
    <div>
      <div class="admin-stat-label">Active &amp; Approved</div>
      <div class="admin-stat-value" style="color:#059669;"><?= number_format($totalApproved) ?></div>
      <div class="admin-stat-sub">Visible to customers</div>
    </div>
    <div style="font-size:2rem;color:#059669;">✓</div>
  </div>
</div>

<!-- Controls & Actions Toolbar -->
<div class="admin-card" style="margin-bottom:24px;padding:20px;">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
    
    <!-- Sync from OSM Button -->
    <form method="POST" action="" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="sync_osm">
      <select name="sync_destination_id" required style="padding:9px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;background:#FFFFFF;font-weight:600;min-width:200px;">
        <option value="">-- Choose Destination to Sync --</option>
        <?php foreach ($destinations as $d): ?>
          <option value="<?= (int)$d['id'] ?>" <?= ($destFilter === (int)$d['id']) ? 'selected' : '' ?>>
            <?= e($d['name']) ?> (<?= e($d['region_state']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="admin-btn admin-btn-primary" style="padding:9px 18px;font-size:0.88rem;display:inline-flex;align-items:center;gap:8px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
        <span>Sync from OpenStreetMap</span>
      </button>
    </form>

    <!-- Filters Form -->
    <form method="GET" action="" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
      <select name="destination_id" onchange="this.form.submit()" style="padding:9px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.86rem;background:#FFFFFF;">
        <option value="0">All Destinations</option>
        <?php foreach ($destinations as $d): ?>
          <option value="<?= (int)$d['id'] ?>" <?= ($destFilter === (int)$d['id']) ? 'selected' : '' ?>>
            <?= e($d['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select name="category" onchange="this.form.submit()" style="padding:9px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.86rem;background:#FFFFFF;">
        <option value="all" <?= ($catFilter === 'all') ? 'selected' : '' ?>>All Categories</option>
        <option value="hotel_resort" <?= ($catFilter === 'hotel_resort') ? 'selected' : '' ?>>Hotels &amp; Resorts</option>
        <option value="dining" <?= ($catFilter === 'dining') ? 'selected' : '' ?>>Dining &amp; Cafes</option>
        <option value="attraction" <?= ($catFilter === 'attraction') ? 'selected' : '' ?>>Attractions</option>
      </select>

      <select name="status" onchange="this.form.submit()" style="padding:9px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.86rem;background:#FFFFFF;">
        <option value="all" <?= ($statusFilter === 'all') ? 'selected' : '' ?>>All Statuses</option>
        <option value="approved" <?= ($statusFilter === 'approved') ? 'selected' : '' ?>>Approved Only</option>
        <option value="pending" <?= ($statusFilter === 'pending') ? 'selected' : '' ?>>Pending Review</option>
        <option value="hidden" <?= ($statusFilter === 'hidden') ? 'selected' : '' ?>>Hidden</option>
      </select>

      <div class="admin-search-box" style="min-width:180px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" name="q" value="<?= e($searchQuery) ?>" placeholder="Search places...">
      </div>

      <button type="submit" class="admin-btn admin-btn-outline" style="padding:9px 14px;">Filter</button>
      <?php if ($destFilter || $catFilter !== 'all' || $statusFilter !== 'all' || !empty($searchQuery)): ?>
        <a href="<?= ADMIN_URL ?>/places-manager.php" class="admin-btn admin-btn-outline" style="padding:9px 12px;color:#DC2626;">Reset</a>
      <?php endif; ?>
    </form>

  </div>
</div>

<!-- Places Table -->
<div class="admin-card" style="padding:0;overflow:hidden;">
  <div style="padding:18px 24px;border-bottom:1px solid #E2E8F0;display:flex;justify-content:space-between;align-items:center;">
    <h3 style="font-size:1.05rem;color:#0F172A;font-weight:700;">
      Mapped Places Directory (Showing <?= count($placesList) ?> of <?= $filteredCount ?>)
    </h3>
    <span style="font-size:0.8rem;color:#64748B;">
      Page <?= $page ?> of <?= max(1, $totalPages) ?>
    </span>
  </div>

  <?php if (empty($placesList)): ?>
    <div style="padding:60px 20px;text-align:center;color:#64748B;">
      <div style="font-size:2.5rem;margin-bottom:12px;">🗺️</div>
      <h4 style="font-size:1.1rem;color:#0F172A;margin-bottom:6px;">No mapped places found</h4>
      <p style="font-size:0.88rem;max-width:500px;margin:0 auto 16px;">
        Try clearing filters or choose a destination and click <strong>"Sync from OpenStreetMap"</strong> to retrieve local establishments.
      </p>
    </div>
  <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width:280px;">Place Name &amp; Localized</th>
            <th>Destination</th>
            <th>Category</th>
            <th>Coordinates &amp; Map</th>
            <th>Pricing &amp; Details</th>
            <th>Status</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($placesList as $p): ?>
            <?php 
              $facList = !empty($p['facilities']) ? json_decode($p['facilities'], true) : [];
              $osmMapUrl = "https://www.openstreetmap.org/?mlat={$p['latitude']}&mlon={$p['longitude']}#map=16/{$p['latitude']}/{$p['longitude']}";
            ?>
            <tr>
              <!-- Name -->
              <td>
                <div style="font-weight:700;color:#0F172A;font-size:0.95rem;display:flex;align-items:center;gap:6px;">
                  <span><?= e($p['name']) ?></span>
                  <?php if ($p['is_verified']): ?>
                    <span title="Midhila Verified Property" style="color:#059669;font-size:0.85rem;">✓</span>
                  <?php endif; ?>
                </div>
                <?php if (!empty($p['name_ml']) || !empty($p['name_ta'])): ?>
                  <div style="font-size:0.75rem;color:#64748B;margin-top:2px;">
                    <?php if (!empty($p['name_ml'])): ?><span style="margin-right:8px;">ML: <?= e($p['name_ml']) ?></span><?php endif; ?>
                    <?php if (!empty($p['name_ta'])): ?><span>TA: <?= e($p['name_ta']) ?></span><?php endif; ?>
                  </div>
                <?php endif; ?>
                <?php if (!empty($p['address'])): ?>
                  <div style="font-size:0.75rem;color:#94A3B8;margin-top:4px;max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    📍 <?= e($p['address']) ?>
                  </div>
                <?php endif; ?>
              </td>

              <!-- Destination -->
              <td>
                <span style="font-weight:600;font-size:0.85rem;color:#334155;">
                  <?= e($p['destination_name'] ?: 'Destination #' . $p['destination_id']) ?>
                </span>
              </td>

              <!-- Category -->
              <td>
                <?php if ($p['category'] === 'hotel' || $p['category'] === 'resort'): ?>
                  <span class="badge badge-info" style="font-weight:700;">🏨 <?= ucfirst(e($p['category'])) ?></span>
                <?php elseif ($p['category'] === 'restaurant' || $p['category'] === 'cafe'): ?>
                  <span class="badge badge-warning" style="font-weight:700;">🍽️ <?= ucfirst(e($p['category'])) ?></span>
                <?php else: ?>
                  <span class="badge badge-success" style="font-weight:700;">🏞️ <?= ucfirst(e($p['category'])) ?></span>
                <?php endif; ?>
                <div style="font-size:0.72rem;color:#94A3B8;margin-top:3px;text-transform:capitalize;">
                  <?= e(str_replace('_', ' ', $p['sub_type'] ?: 'standard')) ?>
                </div>
              </td>

              <!-- Coordinates & OSM Link -->
              <td>
                <div style="font-size:0.8rem;font-family:monospace;color:#475569;">
                  <?= round($p['latitude'], 4) ?>, <?= round($p['longitude'], 4) ?>
                </div>
                <a href="<?= e($osmMapUrl) ?>" target="_blank" rel="noopener noreferrer" style="font-size:0.72rem;color:#2563EB;display:inline-flex;align-items:center;gap:3px;margin-top:2px;font-weight:600;">
                  <span>View on OSM</span> &nearr;
                </a>
              </td>

              <!-- Pricing & Details -->
              <td>
                <?php if ($p['indicative_price'] > 0): ?>
                  <div style="font-weight:700;color:#0F172A;font-size:0.88rem;">
                    ₹<?= number_format($p['indicative_price']) ?>
                  </div>
                  <div style="font-size:0.72rem;color:#64748B;">
                    <?= e($p['price_unit'] ?: 'per night') ?>
                  </div>
                <?php else: ?>
                  <span style="font-size:0.78rem;color:#94A3B8;">On Enquiry</span>
                <?php endif; ?>

                <?php if (!empty($facList)): ?>
                  <div style="display:flex;gap:4px;flex-wrap:wrap;margin-top:4px;">
                    <?php foreach (array_slice($facList, 0, 2) as $f): ?>
                      <span style="font-size:0.68rem;background:#F1F5F9;padding:2px 6px;border-radius:4px;color:#475569;"><?= e($f) ?></span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </td>

              <!-- Status -->
              <td>
                <?php if ($p['is_approved'] == 1): ?>
                  <span class="badge badge-success">Approved</span>
                <?php elseif ($p['is_approved'] == -1): ?>
                  <span class="badge badge-danger">Hidden</span>
                <?php else: ?>
                  <span class="badge badge-warning">Pending</span>
                <?php endif; ?>
              </td>

              <!-- Actions -->
              <td style="text-align:right;">
                <div style="display:inline-flex;gap:6px;align-items:center;">
                  
                  <!-- Approve / Hide Form -->
                  <form method="POST" action="" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="place_id" value="<?= (int)$p['id'] ?>">
                    <?php if ($p['is_approved'] == 1): ?>
                      <input type="hidden" name="new_status" value="-1">
                      <button type="submit" class="admin-btn admin-btn-outline" style="padding:4px 8px;font-size:0.75rem;" title="Hide from customer view">
                        Hide
                      </button>
                    <?php else: ?>
                      <input type="hidden" name="new_status" value="1">
                      <button type="submit" class="admin-btn admin-btn-outline" style="padding:4px 8px;font-size:0.75rem;color:#059669;border-color:#A7F3D0;" title="Approve place">
                        Approve
                      </button>
                    <?php endif; ?>
                  </form>

                  <!-- Toggle Verify Form -->
                  <form method="POST" action="" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle_verify">
                    <input type="hidden" name="place_id" value="<?= (int)$p['id'] ?>">
                    <input type="hidden" name="is_verified" value="<?= $p['is_verified'] ? 0 : 1 ?>">
                    <button type="submit" class="admin-btn admin-btn-outline" style="padding:4px 8px;font-size:0.75rem;<?= $p['is_verified'] ? 'color:#D97706;' : '' ?>" title="Toggle Verified Badge">
                      <?= $p['is_verified'] ? '★ Verified' : '☆ Verify' ?>
                    </button>
                  </form>

                  <!-- Edit Trigger -->
                  <button type="button" class="admin-btn admin-btn-outline" style="padding:4px 8px;font-size:0.75rem;" onclick='openEditModal(<?= json_encode($p) ?>)'>
                    Edit
                  </button>

                  <!-- Delete Form -->
                  <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Remove this mapped place permanently?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete_place">
                    <input type="hidden" name="place_id" value="<?= (int)$p['id'] ?>">
                    <button type="submit" class="admin-btn admin-btn-outline" style="padding:4px 8px;font-size:0.75rem;color:#DC2626;" title="Delete Record">
                      ✕
                    </button>
                  </form>

                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <div style="padding:16px 24px;border-top:1px solid #E2E8F0;display:flex;justify-content:space-between;align-items:center;background:#FAFAFA;">
        <span style="font-size:0.84rem;color:#64748B;">
          Showing <?= $offset + 1 ?> to <?= min($offset + $limit, $filteredCount) ?> of <?= $filteredCount ?> places
        </span>
        <div style="display:flex;gap:6px;">
          <?php for ($i = 1; $i <= min($totalPages, 10); $i++): ?>
            <?php 
              $qParams = $_GET;
              $qParams['p'] = $i;
              $pageUrl = '?' . http_build_query($qParams);
            ?>
            <a href="<?= e($pageUrl) ?>" class="admin-btn admin-btn-outline" style="padding:4px 10px;font-size:0.8rem;<?= ($i === $page) ? 'background:#0F172A;color:#FFFFFF;border-color:#0F172A;' : '' ?>">
              <?= $i ?>
            </a>
          <?php endfor; ?>
        </div>
      </div>
    <?php endif; ?>

  <?php endif; ?>
</div>

<!-- EDIT PLACE MODAL -->
<div id="editPlaceModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:#FFFFFF;border-radius:14px;max-width:680px;width:100%;max-height:90vh;overflow-y:auto;padding:28px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);border:1px solid #E2E8F0;">
    
    <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #E2E8F0;padding-bottom:12px;margin-bottom:18px;">
      <h3 style="font-size:1.15rem;font-weight:700;color:#0F172A;margin:0;">Edit Place &amp; Pricing</h3>
      <button type="button" onclick="closeEditModal()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:#94A3B8;">&times;</button>
    </div>

    <form method="POST" action="">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="edit_place">
      <input type="hidden" name="place_id" id="edit_place_id" value="">

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
        <div>
          <label style="font-size:0.8rem;font-weight:700;color:#334155;display:block;margin-bottom:4px;">Place Name (EN) *</label>
          <input type="text" name="name" id="edit_name" required style="width:100%;padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
        </div>
        <div>
          <label style="font-size:0.8rem;font-weight:700;color:#334155;display:block;margin-bottom:4px;">Category *</label>
          <select name="category" id="edit_category" style="width:100%;padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;background:#FFFFFF;">
            <option value="hotel">Hotel</option>
            <option value="resort">Resort</option>
            <option value="restaurant">Restaurant</option>
            <option value="cafe">Cafe</option>
            <option value="attraction">Tourist Attraction</option>
          </select>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
        <div>
          <label style="font-size:0.8rem;font-weight:700;color:#334155;display:block;margin-bottom:4px;">Name (Malayalam)</label>
          <input type="text" name="name_ml" id="edit_name_ml" placeholder="മലയാളം പേര്" style="width:100%;padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
        </div>
        <div>
          <label style="font-size:0.8rem;font-weight:700;color:#334155;display:block;margin-bottom:4px;">Name (Tamil)</label>
          <input type="text" name="name_ta" id="edit_name_ta" placeholder="தமிழ் பெயர்" style="width:100%;padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
        <div>
          <label style="font-size:0.8rem;font-weight:700;color:#334155;display:block;margin-bottom:4px;">Indicative Price (₹)</label>
          <input type="number" step="0.01" name="indicative_price" id="edit_price" style="width:100%;padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
        </div>
        <div>
          <label style="font-size:0.8rem;font-weight:700;color:#334155;display:block;margin-bottom:4px;">Price Unit</label>
          <input type="text" name="price_unit" id="edit_price_unit" placeholder="per room / night" style="width:100%;padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
        </div>
      </div>

      <div style="margin-bottom:14px;">
        <label style="font-size:0.8rem;font-weight:700;color:#334155;display:block;margin-bottom:4px;">Address / Location</label>
        <input type="text" name="address" id="edit_address" style="width:100%;padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
        <div>
          <label style="font-size:0.8rem;font-weight:700;color:#334155;display:block;margin-bottom:4px;">Contact Phone</label>
          <input type="text" name="contact_phone" id="edit_phone" placeholder="+91..." style="width:100%;padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
        </div>
        <div>
          <label style="font-size:0.8rem;font-weight:700;color:#334155;display:block;margin-bottom:4px;">Website URL</label>
          <input type="url" name="website" id="edit_website" placeholder="https://..." style="width:100%;padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
        </div>
      </div>

      <div style="margin-bottom:14px;">
        <label style="font-size:0.8rem;font-weight:700;color:#334155;display:block;margin-bottom:4px;">Room Categories (Comma-separated)</label>
        <input type="text" name="room_categories" id="edit_room_categories" placeholder="Deluxe Room, Valley View Cottage, Luxury Suite" style="width:100%;padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
      </div>

      <div style="margin-bottom:14px;">
        <label style="font-size:0.8rem;font-weight:700;color:#334155;display:block;margin-bottom:4px;">Facilities (Comma-separated)</label>
        <input type="text" name="facilities" id="edit_facilities" placeholder="Free Wi-Fi, Swimming Pool, Parking, Room Service" style="width:100%;padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
      </div>

      <div style="margin-bottom:20px;">
        <label style="font-size:0.8rem;font-weight:700;color:#334155;display:block;margin-bottom:4px;">Food Options / Cuisine (Comma-separated)</label>
        <input type="text" name="food_options" id="edit_food_options" placeholder="Pure Vegetarian, South Indian, Buffet Breakfast" style="width:100%;padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
      </div>

      <div style="display:flex;justify-content:flex-end;gap:10px;">
        <button type="button" onclick="closeEditModal()" class="admin-btn admin-btn-outline">Cancel</button>
        <button type="submit" class="admin-btn admin-btn-primary">Save Changes</button>
      </div>
    </form>

  </div>
</div>

<script>
function openEditModal(place) {
  document.getElementById('edit_place_id').value = place.id;
  document.getElementById('edit_name').value = place.name || '';
  document.getElementById('edit_name_ml').value = place.name_ml || '';
  document.getElementById('edit_name_ta').value = place.name_ta || '';
  document.getElementById('edit_category').value = place.category || 'hotel';
  document.getElementById('edit_price').value = place.indicative_price || 0;
  document.getElementById('edit_price_unit').value = place.price_unit || '';
  document.getElementById('edit_address').value = place.address || '';
  document.getElementById('edit_phone').value = place.contact_phone || '';
  document.getElementById('edit_website').value = place.website || '';

  // Parse JSON or comma
  var fac = '';
  try {
    var arr = JSON.parse(place.facilities);
    if (Array.isArray(arr)) fac = arr.join(', ');
  } catch(e) { fac = place.facilities || ''; }
  document.getElementById('edit_facilities').value = fac;

  var food = '';
  try {
    var arr2 = JSON.parse(place.food_options);
    if (Array.isArray(arr2)) food = arr2.join(', ');
  } catch(e) { food = place.food_options || ''; }
  document.getElementById('edit_food_options').value = food;

  var rooms = '';
  try {
    var arr3 = JSON.parse(place.room_categories);
    if (Array.isArray(arr3)) rooms = arr3.join(', ');
  } catch(e) { rooms = place.room_categories || ''; }
  document.getElementById('edit_room_categories').value = rooms;

  var modal = document.getElementById('editPlaceModal');
  modal.style.display = 'flex';
}

function closeEditModal() {
  document.getElementById('editPlaceModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
