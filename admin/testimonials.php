<?php
/**
 * MIDHILA VOYAGES — Testimonials Management
 */

require_once __DIR__ . '/includes/admin-init.php';

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_testimonial'])) {
    if (verify_csrf_token()) {
        $name = trim($_POST['client_name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $tour = trim($_POST['tour_taken'] ?? '');
        $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
        $feedback = trim($_POST['feedback'] ?? '');
        $travelDate = trim($_POST['travel_date'] ?? '');
        $isFeatured = !empty($_POST['is_featured']) ? 1 : 0;
        $isVerified = !empty($_POST['is_verified']) ? 1 : 0;

        if (!empty($name) && !empty($feedback)) {
            $stmt = $db->prepare("
                INSERT INTO `testimonials` (`client_name`, `location`, `tour_taken`, `rating`, `feedback`, `travel_date`, `is_verified`, `is_featured`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $location, $tour, $rating, $feedback, $travelDate, $isVerified, $isFeatured]);
            set_flash_message('success', 'Testimonial added.');
        }
    }
    header('Location: ' . ADMIN_URL . '/testimonials.php');
    exit;
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delId = (int)$_GET['delete_id'];
    $stmt = $db->prepare("DELETE FROM `testimonials` WHERE `id` = ?");
    $stmt->execute([$delId]);
    set_flash_message('success', 'Testimonial removed.');
    header('Location: ' . ADMIN_URL . '/testimonials.php');
    exit;
}

$testimonials = $db->query("SELECT * FROM `testimonials` ORDER BY `id` DESC")->fetchAll();

$adminTitle = 'Testimonials | MIDHILA VOYAGES';
$adminHeading = 'Customer Testimonials';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="display:grid;grid-template-columns:1fr 1.8fr;gap:28px;">
  
  <!-- Add Testimonial -->
  <div class="admin-card">
    <h3 style="font-size:1.15rem;color:var(--admin-navy);margin-bottom:16px;border-bottom:1px solid var(--admin-border);padding-bottom:8px;">Add Verified Review</h3>
    
    <form method="POST" action="">
      <?= csrf_field() ?>
      <input type="hidden" name="save_testimonial" value="1">

      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Guest Name *</label>
        <input type="text" name="client_name" required placeholder="e.g. Narayanan K. & Family" style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
      </div>

      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Guest Location</label>
        <input type="text" name="location" placeholder="e.g. Kottayam, Kerala" style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
      </div>

      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Tour Taken</label>
        <input type="text" name="tour_taken" placeholder="e.g. Tamil Nadu Divine Yatra" style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Rating (1 to 5)</label>
          <select name="rating" style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
            <option value="5">★★★★★ (5 Stars)</option>
            <option value="4">★★★★☆ (4 Stars)</option>
            <option value="3">★★★☆☆ (3 Stars)</option>
          </select>
        </div>
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Travel Date</label>
          <input type="text" name="travel_date" placeholder="e.g. February 2026" style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
        </div>
      </div>

      <div style="margin-bottom:16px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Feedback / Review Text *</label>
        <textarea name="feedback" rows="4" required placeholder="Guest's verified feedback..." style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;"></textarea>
      </div>

      <div style="display:flex;gap:16px;margin-bottom:20px;">
        <label style="display:flex;align-items:center;gap:6px;font-size:0.85rem;font-weight:600;">
          <input type="checkbox" name="is_verified" value="1" checked>
          <span>Verified Tour</span>
        </label>
        <label style="display:flex;align-items:center;gap:6px;font-size:0.85rem;font-weight:600;">
          <input type="checkbox" name="is_featured" value="1" checked>
          <span>Feature on Home</span>
        </label>
      </div>

      <button type="submit" class="admin-btn admin-btn-primary" style="width:100%;">Save Testimonial</button>
    </form>
  </div>

  <!-- Testimonials List -->
  <div class="admin-card" style="padding:0;overflow:hidden;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Guest</th>
          <th>Tour</th>
          <th>Feedback</th>
          <th>Rating</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($testimonials as $t): ?>
          <tr>
            <td>
              <strong><?= e($t['client_name']) ?></strong>
              <div style="font-size:0.75rem;color:var(--admin-muted);"><?= e($t['location'] ?: 'Guest') ?></div>
            </td>
            <td><?= e($t['tour_taken'] ?: '-') ?></td>
            <td>
              <div style="font-size:0.85rem;line-height:1.5;max-width:320px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                <?= e($t['feedback']) ?>
              </div>
            </td>
            <td style="color:var(--admin-gold);font-size:0.9rem;"><?= str_repeat('★', (int)$t['rating']) ?></td>
            <td style="text-align:right;">
              <a href="<?= ADMIN_URL ?>/testimonials.php?delete_id=<?= (int)$t['id'] ?>" 
                 data-confirm-delete="Are you sure you want to delete this customer review from <?= e($t['client_name']) ?>?"
                 class="admin-btn admin-btn-danger admin-btn-sm">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
