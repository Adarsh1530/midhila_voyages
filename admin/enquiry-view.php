<?php
/**
 * MIDHILA VOYAGES — Detailed Enquiry Inspection & Follow-Up Notes
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

require_admin_login();

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT * FROM `enquiries` WHERE `id` = ? LIMIT 1");
$stmt->execute([$id]);
$enquiry = $stmt->fetch();

if (!$enquiry) {
    set_flash_message('error', 'Enquiry record not found.');
    header('Location: ' . ADMIN_URL . '/enquiries.php');
    exit;
}

// Handle Status Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (verify_csrf_token()) {
        $newStatus = $_POST['status'] ?? 'new';
        $now = date('Y-m-d H:i:s');
        $upd = $db->prepare("UPDATE `enquiries` SET `status` = ?, `updated_at` = ? WHERE `id` = ?");
        $upd->execute([$newStatus, $now, $id]);
        log_activity($_SESSION['admin_user_id'] ?? null, 'update_enquiry_status', "Updated enquiry #{$enquiry['reference_no']} status to {$newStatus}");
        set_flash_message('success', 'Enquiry status updated.');
        header('Location: ' . ADMIN_URL . '/enquiry-view.php?id=' . $id);
        exit;
    }
}

// Handle Adding Internal Note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
    if (verify_csrf_token()) {
        $noteText = trim($_POST['note_text'] ?? '');
        if (!empty($noteText)) {
            $insNote = $db->prepare("INSERT INTO `enquiry_notes` (`enquiry_id`, `admin_id`, `note`) VALUES (?, ?, ?)");
            $insNote->execute([$id, $_SESSION['admin_user_id'], $noteText]);
            set_flash_message('success', 'Internal note recorded.');
            header('Location: ' . ADMIN_URL . '/enquiry-view.php?id=' . $id);
            exit;
        }
    }
}

// Fetch Notes
$notesStmt = $db->prepare("
    SELECT n.*, a.full_name AS author_name 
    FROM `enquiry_notes` n
    JOIN `admins` a ON n.admin_id = a.id
    WHERE n.enquiry_id = ?
    ORDER BY n.id DESC
");
$notesStmt->execute([$id]);
$notes = $notesStmt->fetchAll();

$cleanPhone = preg_replace('/[^0-9]/', '', $enquiry['phone']);
$waFollowUp = "Hello " . $enquiry['full_name'] . ", greetings from Midhila Voyages! I am following up on your travel enquiry (#" . $enquiry['reference_no'] . ") for " . ($enquiry['destination_name'] ?: 'your vacation') . ". Please let me know a convenient time to discuss your itinerary.";
$waUrl = build_whatsapp_url($cleanPhone, $waFollowUp);

$adminTitle = 'Enquiry #' . e($enquiry['reference_no']) . ' | MIDHILA VOYAGES';
$adminHeading = 'Enquiry Details: ' . e($enquiry['reference_no']);

require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="margin-bottom:20px;">
  <a href="<?= ADMIN_URL ?>/enquiries.php" class="admin-btn admin-btn-outline">&larr; Back to Enquiries</a>
</div>

<div style="display:grid;grid-template-columns:2fr 1.2fr;gap:28px;">
  
  <!-- Left Column: Full Lead Preferences -->
  <div>
    
    <div class="admin-card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;border-bottom:1px solid var(--admin-border);padding-bottom:14px;">
        <div>
          <span style="font-size:0.75rem;text-transform:uppercase;color:var(--admin-muted);font-weight:700;">Reference</span>
          <h3 style="font-size:1.35rem;color:var(--admin-navy);"><?= e($enquiry['reference_no']) ?></h3>
        </div>

        <form method="POST" action="" style="display:flex;align-items:center;gap:8px;">
          <?= csrf_field() ?>
          <input type="hidden" name="update_status" value="1">
          <select name="status" style="padding:6px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.85rem;background:#FFF;">
            <option value="new" <?= ($enquiry['status'] === 'new') ? 'selected' : '' ?>>New</option>
            <option value="in_progress" <?= ($enquiry['status'] === 'in_progress') ? 'selected' : '' ?>>In Progress</option>
            <option value="contacted" <?= ($enquiry['status'] === 'contacted') ? 'selected' : '' ?>>Contacted</option>
            <option value="converted" <?= ($enquiry['status'] === 'converted') ? 'selected' : '' ?>>Converted</option>
            <option value="closed" <?= ($enquiry['status'] === 'closed') ? 'selected' : '' ?>>Closed</option>
          </select>
          <button type="submit" class="admin-btn admin-btn-outline admin-btn-sm">Update Status</button>
        </form>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
        <div>
          <span style="font-size:0.75rem;color:var(--admin-muted);text-transform:uppercase;">Traveller Name</span>
          <div style="font-size:1.05rem;font-weight:700;color:var(--admin-navy);"><?= e($enquiry['full_name']) ?></div>
        </div>
        <div>
          <span style="font-size:0.75rem;color:var(--admin-muted);text-transform:uppercase;">Phone / WhatsApp</span>
          <div style="font-size:1.05rem;font-weight:600;"><?= e($enquiry['phone']) ?></div>
        </div>
        <div>
          <span style="font-size:0.75rem;color:var(--admin-muted);text-transform:uppercase;">Email</span>
          <div style="font-size:0.95rem;"><?= e($enquiry['email'] ?: 'Not Provided') ?></div>
        </div>
        <div>
          <span style="font-size:0.75rem;color:var(--admin-muted);text-transform:uppercase;">Contact Preference</span>
          <div style="font-size:0.95rem;text-transform:capitalize;font-weight:600;color:var(--admin-teal);"><?= e($enquiry['preferred_contact_method']) ?></div>
        </div>
      </div>

      <div style="background:var(--admin-bg);padding:20px;border-radius:8px;border:1px solid var(--admin-border);margin-bottom:24px;">
        <h4 style="font-size:0.95rem;color:var(--admin-navy);margin-bottom:12px;">Trip Parameters</h4>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:16px;">
          <div>
            <span style="font-size:0.75rem;color:var(--admin-muted);">Destination:</span>
            <div style="font-weight:600;color:var(--admin-navy);"><?= e($enquiry['destination_name'] ?: 'Not Specified') ?></div>
          </div>
          <div>
            <span style="font-size:0.75rem;color:var(--admin-muted);">Package:</span>
            <div style="font-weight:600;color:var(--admin-gold);"><?= e($enquiry['package_title'] ?: 'Custom Plan') ?></div>
          </div>
          <div>
            <span style="font-size:0.75rem;color:var(--admin-muted);">Travel Style:</span>
            <div><?= e($enquiry['travel_category'] ?: 'General') ?></div>
          </div>
          <div>
            <span style="font-size:0.75rem;color:var(--admin-muted);">Travel Dates:</span>
            <div><?= e($enquiry['departure_date'] ?: 'Flexible') ?> to <?= e($enquiry['return_date'] ?: 'Flexible') ?></div>
          </div>
          <div>
            <span style="font-size:0.75rem;color:var(--admin-muted);">Travellers:</span>
            <div><?= (int)$enquiry['adults_count'] ?> Adult(s), <?= (int)$enquiry['children_count'] ?> Child(ren)</div>
          </div>
          <div>
            <span style="font-size:0.75rem;color:var(--admin-muted);">Stay Category:</span>
            <div><?= e($enquiry['accommodation_preference'] ?: 'Standard') ?></div>
          </div>
          <div>
            <span style="font-size:0.75rem;color:var(--admin-muted);">Budget Range:</span>
            <div><?= e($enquiry['budget_range'] ?: 'Standard') ?></div>
          </div>
        </div>
      </div>

      <?php if (!empty($enquiry['travel_requirements'])): ?>
        <div style="margin-bottom:20px;">
          <span style="font-size:0.75rem;color:var(--admin-muted);text-transform:uppercase;font-weight:700;">Special Requirements &amp; Notes:</span>
          <div style="background:#FFF;padding:16px;border-radius:6px;border:1px solid var(--admin-border);margin-top:6px;font-size:0.95rem;line-height:1.7;">
            <?= nl2br(e($enquiry['travel_requirements'])) ?>
          </div>
        </div>
      <?php endif; ?>

      <div style="display:flex;gap:12px;margin-top:24px;">
        <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer" class="admin-btn" style="background:#25D366;color:#FFF;padding:10px 20px;">
          <span>Open WhatsApp Chat</span> &nearr;
        </a>
        <a href="tel:<?= preg_replace('/[^0-9+]/', '', $enquiry['phone']) ?>" class="admin-btn admin-btn-outline" style="padding:10px 20px;">
          <span>Call Traveller</span>
        </a>
      </div>

    </div>

  </div>

  <!-- Right Column: Internal Follow-up Notes Thread -->
  <div>
    
    <div class="admin-card">
      <h4 style="font-size:1.1rem;color:var(--admin-navy);margin-bottom:14px;border-bottom:1px solid var(--admin-border);padding-bottom:8px;">Internal Staff Notes</h4>
      
      <!-- Add Note Form -->
      <form method="POST" action="" style="margin-bottom:24px;">
        <?= csrf_field() ?>
        <input type="hidden" name="add_note" value="1">
        <textarea name="note_text" rows="3" required placeholder="Add follow-up notes (e.g. Called client at 3 PM, requested Munnar 4-star quote, sent proposal on WhatsApp)..." 
                  style="width:100%;padding:10px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.88rem;margin-bottom:10px;outline:none;"></textarea>
        <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm">Add Note</button>
      </form>

      <!-- Existing Notes List -->
      <div style="display:flex;flex-direction:column;gap:12px;">
        <?php if (empty($notes)): ?>
          <p style="font-size:0.85rem;color:var(--admin-muted);text-align:center;padding:20px;">No internal follow-up notes recorded yet.</p>
        <?php else: ?>
          <?php foreach ($notes as $note): ?>
            <div style="background:var(--admin-bg);padding:12px;border-radius:6px;border:1px solid var(--admin-border);font-size:0.86rem;">
              <div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:0.75rem;color:var(--admin-muted);">
                <strong><?= e($note['author_name']) ?></strong>
                <span><?= date('d M Y, h:i A', strtotime($note['created_at'])) ?></span>
              </div>
              <div style="line-height:1.5;color:var(--admin-navy);"><?= nl2br(e($note['note'])) ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </div>

  </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
