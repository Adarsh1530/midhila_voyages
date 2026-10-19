<?php
/**
 * MIDHILA VOYAGES — FAQ Management
 */

require_once __DIR__ . '/includes/admin-init.php';

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_faq'])) {
    if (verify_csrf_token()) {
        $faqId = (int)($_POST['faq_id'] ?? 0);
        $category = trim($_POST['category'] ?? 'General');
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        $order = (int)($_POST['display_order'] ?? 0);

        if (!empty($question) && !empty($answer)) {
            if ($faqId > 0) {
                $stmt = $db->prepare("UPDATE `faqs` SET `category` = ?, `question` = ?, `answer` = ?, `display_order` = ? WHERE `id` = ?");
                $stmt->execute([$category, $question, $answer, $order, $faqId]);
                set_flash_message('success', 'FAQ updated.');
            } else {
                $stmt = $db->prepare("INSERT INTO `faqs` (`category`, `question`, `answer`, `display_order`) VALUES (?, ?, ?, ?)");
                $stmt->execute([$category, $question, $answer, $order]);
                set_flash_message('success', 'FAQ added.');
            }
        }
    }
    header('Location: ' . ADMIN_URL . '/faqs.php');
    exit;
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delId = (int)$_GET['delete_id'];
    $stmt = $db->prepare("DELETE FROM `faqs` WHERE `id` = ?");
    $stmt->execute([$delId]);
    set_flash_message('success', 'FAQ deleted.');
    header('Location: ' . ADMIN_URL . '/faqs.php');
    exit;
}

$faqs = $db->query("SELECT * FROM `faqs` ORDER BY `category` ASC, `display_order` ASC")->fetchAll();

$editFaq = null;
if (isset($_GET['edit_id'])) {
    $stmt = $db->prepare("SELECT * FROM `faqs` WHERE `id` = ? LIMIT 1");
    $stmt->execute([(int)$_GET['edit_id']]);
    $editFaq = $stmt->fetch();
}

$adminTitle = 'Manage FAQs | MIDHILA VOYAGES';
$adminHeading = 'Frequently Asked Questions';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="display:grid;grid-template-columns:1fr 1.6fr;gap:28px;">
  
  <!-- Add / Edit FAQ -->
  <div class="admin-card">
    <h3 style="font-size:1.15rem;color:var(--admin-navy);margin-bottom:16px;border-bottom:1px solid var(--admin-border);padding-bottom:8px;">
      <?= $editFaq ? 'Edit FAQ Item' : 'Add New FAQ Item' ?>
    </h3>

    <form method="POST" action="<?= ADMIN_URL ?>/faqs.php">
      <?= csrf_field() ?>
      <input type="hidden" name="save_faq" value="1">
      <input type="hidden" name="faq_id" value="<?= (int)($editFaq['id'] ?? 0) ?>">

      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Category *</label>
        <select name="category" style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
          <?php foreach (['General', 'Booking', 'Pilgrimage', 'Payments', 'Documents'] as $cat): ?>
            <option value="<?= e($cat) ?>" <?= (($editFaq['category'] ?? '') === $cat) ? 'selected' : '' ?>><?= e($cat) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Question *</label>
        <input type="text" name="question" required placeholder="e.g. How do I customize my itinerary?" 
               value="<?= e($editFaq['question'] ?? '') ?>"
               style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
      </div>

      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Answer *</label>
        <textarea name="answer" rows="5" required placeholder="Write a clear, helpful answer..." 
                  style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;line-height:1.6;"><?= e($editFaq['answer'] ?? '') ?></textarea>
      </div>

      <div style="margin-bottom:20px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Display Order</label>
        <input type="number" name="display_order" value="<?= (int)($editFaq['display_order'] ?? 0) ?>" 
               style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
      </div>

      <div style="display:flex;gap:10px;">
        <button type="submit" class="admin-btn admin-btn-primary">
          <span><?= $editFaq ? 'Update FAQ' : 'Save FAQ' ?></span>
        </button>
        <?php if ($editFaq): ?>
          <a href="<?= ADMIN_URL ?>/faqs.php" class="admin-btn admin-btn-outline">Cancel</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- FAQs Table -->
  <div class="admin-card" style="padding:0;overflow:hidden;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Category</th>
          <th>Question</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($faqs as $faq): ?>
          <tr>
            <td>
              <span class="badge badge-neutral"><?= e($faq['category']) ?></span>
            </td>
            <td>
              <strong><?= e($faq['question']) ?></strong>
              <div style="font-size:0.8rem;color:var(--admin-muted);line-height:1.4;margin-top:2px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                <?= e($faq['answer']) ?>
              </div>
            </td>
            <td style="text-align:right;">
              <div style="display:inline-flex;gap:6px;">
                <a href="<?= ADMIN_URL ?>/faqs.php?edit_id=<?= (int)$faq['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm">Edit</a>
                <a href="<?= ADMIN_URL ?>/faqs.php?delete_id=<?= (int)$faq['id'] ?>" 
                   data-confirm-delete="Are you sure you want to delete this FAQ?"
                   class="admin-btn admin-btn-danger admin-btn-sm">Delete</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
