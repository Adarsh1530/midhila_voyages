<?php
/**
 * MIDHILA VOYAGES — Multilingual Content Management Module (i18n)
 * Review and manage English, Malayalam (മലയാളം), and Tamil (தமிழ்) UI phrases and labels.
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

require_admin_login();
require_role(['super_admin', 'travel_manage']);

$db = getDB();

// Handle Edit or Add Translation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token()) {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'save_translation') {
            $key = trim($_POST['translation_key'] ?? '');
            $group = trim($_POST['group_name'] ?? 'general');
            $en = trim($_POST['en_text'] ?? '');
            $ml = trim($_POST['ml_text'] ?? '');
            $ta = trim($_POST['ta_text'] ?? '');

            if (!empty($key) && !empty($en)) {
                $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
                if ($driver === 'sqlite') {
                    $stmt = $db->prepare("
                        INSERT INTO `translations` (`translation_key`, `group_name`, `en_text`, `ml_text`, `ta_text`, `updated_at`)
                        VALUES (?, ?, ?, ?, ?, ?)
                        ON CONFLICT(`translation_key`) DO UPDATE SET
                            `group_name` = excluded.group_name,
                            `en_text` = excluded.en_text,
                            `ml_text` = excluded.ml_text,
                            `ta_text` = excluded.ta_text,
                            `updated_at` = excluded.updated_at
                    ");
                } else {
                    $stmt = $db->prepare("
                        INSERT INTO `translations` (`translation_key`, `group_name`, `en_text`, `ml_text`, `ta_text`, `updated_at`)
                        VALUES (?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            `group_name` = VALUES(`group_name`),
                            `en_text` = VALUES(`en_text`),
                            `ml_text` = VALUES(`ml_text`),
                            `ta_text` = VALUES(`ta_text`),
                            `updated_at` = VALUES(`updated_at`)
                    ");
                }
                $stmt->execute([$key, $group, $en, $ml, $ta, date('Y-m-d H:i:s')]);
                set_flash_message('success', "Translation phrase '{$key}' saved successfully.");
                header('Location: ' . ADMIN_URL . '/translations.php');
                exit;
            }
        }
    }
}

// Fetch dictionary from i18n
$coreDict = get_translation_dictionary();

// Sync any existing DB overrides
$dbTranslations = [];
try {
    $rows = $db->query("SELECT * FROM `translations`")->fetchAll();
    foreach ($rows as $r) {
        $dbTranslations[$r['translation_key']] = $r;
    }
} catch (Exception $e) {}

$search = trim($_GET['q'] ?? '');
$editKey = trim($_GET['edit'] ?? '');

$adminTitle = 'Translations (i18n) | MIDHILA VOYAGES';
$adminHeading = 'Multilingual Content Engine';

require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
  <div>
    <h3 style="font-size:1.25rem;color:var(--admin-navy);font-weight:700;">Multilingual Dictionary (i18n)</h3>
    <p style="font-size:0.86rem;color:var(--admin-muted);margin:0;">Manage localized UI phrases across English, Malayalam (മലയാളം), and Tamil (தமிழ்).</p>
  </div>
</div>

<div style="display:grid;grid-template-columns:1.6fr 1fr;gap:24px;">
  
  <!-- Left Column: Dictionary Phrases -->
  <div>
    <!-- Search Box -->
    <div class="admin-card" style="padding:16px 20px;margin-bottom:20px;">
      <form method="GET" action="" style="display:flex;gap:10px;">
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search translation keys or phrases..." style="flex-grow:1;padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
        <button type="submit" class="admin-btn admin-btn-primary">Search</button>
        <?php if ($search): ?>
          <a href="<?= ADMIN_URL ?>/translations.php" class="admin-btn admin-btn-outline">Clear</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Table of Phrases -->
    <div class="admin-table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Key &amp; English</th>
            <th>Malayalam (മലയാളം)</th>
            <th>Tamil (தமிழ்)</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $renderedCount = 0;
          foreach ($coreDict as $key => $langs): 
            $enVal = $dbTranslations[$key]['en_text'] ?? ($langs['en'] ?? '');
            $mlVal = $dbTranslations[$key]['ml_text'] ?? ($langs['ml'] ?? '');
            $taVal = $dbTranslations[$key]['ta_text'] ?? ($langs['ta'] ?? '');

            if ($search !== '' && stripos($key, $search) === false && stripos($enVal, $search) === false && stripos($mlVal, $search) === false && stripos($taVal, $search) === false) {
              continue;
            }
            $renderedCount++;
          ?>
            <tr>
              <td>
                <div style="font-family:monospace;font-size:0.75rem;color:var(--admin-gold);font-weight:700;"><?= e($key) ?></div>
                <div style="font-weight:600;color:#0F172A;font-size:0.88rem;margin-top:2px;"><?= e(truncate_words($enVal, 10)) ?></div>
              </td>
              <td style="font-size:0.85rem;color:#334155;">
                <?= e(truncate_words($mlVal ?: '—', 8)) ?>
              </td>
              <td style="font-size:0.85rem;color:#334155;">
                <?= e(truncate_words($taVal ?: '—', 8)) ?>
              </td>
              <td style="text-align:right;">
                <a href="<?= ADMIN_URL ?>/translations.php?edit=<?= urlencode($key) ?><?= $search ? '&q=' . urlencode($search) : '' ?>" class="admin-btn admin-btn-outline admin-btn-sm">
                  Edit
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if ($renderedCount === 0): ?>
            <tr>
              <td colspan="4" style="text-align:center;padding:36px;color:var(--admin-muted);">
                No translation keys match your search term.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Right Column: Edit Form -->
  <div>
    <?php
      $curEn = '';
      $curMl = '';
      $curTa = '';
      if ($editKey !== '' && isset($coreDict[$editKey])) {
        $curEn = $dbTranslations[$editKey]['en_text'] ?? ($coreDict[$editKey]['en'] ?? '');
        $curMl = $dbTranslations[$editKey]['ml_text'] ?? ($coreDict[$editKey]['ml'] ?? '');
        $curTa = $dbTranslations[$editKey]['ta_text'] ?? ($coreDict[$editKey]['ta'] ?? '');
      }
    ?>
    <div class="admin-card">
      <h3 style="font-size:1.15rem;color:#0F172A;font-weight:700;margin-bottom:6px;">
        <?= $editKey ? ('Edit Key: ' . e($editKey)) : 'Add Custom Translation Key' ?>
      </h3>
      <p style="font-size:0.82rem;color:#64748B;margin-bottom:18px;">
        Updates take effect immediately on public web pages and forms across all devices.
      </p>

      <form method="POST" action="">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_translation">

        <div style="margin-bottom:14px;">
          <label>Translation Key Identifier *</label>
          <input type="text" name="translation_key" required value="<?= e($editKey) ?>" <?= $editKey ? 'readonly style="background:#F1F5F9;"' : '' ?> placeholder="e.g. form_travel_type">
        </div>

        <div style="margin-bottom:14px;">
          <label>Category Group</label>
          <input type="text" name="group_name" value="<?= e($dbTranslations[$editKey]['group_name'] ?? 'ui') ?>" placeholder="e.g. navigation, forms, booking">
        </div>

        <div style="margin-bottom:14px;">
          <label>English Text *</label>
          <textarea name="en_text" rows="2" required placeholder="English version..."><?= e($curEn) ?></textarea>
        </div>

        <div style="margin-bottom:14px;">
          <label>Malayalam Text (മലയാളം)</label>
          <textarea name="ml_text" rows="2" placeholder="മലയാളം പരിഭാഷ..."><?= e($curMl) ?></textarea>
        </div>

        <div style="margin-bottom:18px;">
          <label>Tamil Text (தமிழ்)</label>
          <textarea name="ta_text" rows="2" placeholder="தமிழ் மொழிபெயர்ப்பு..."><?= e($curTa) ?></textarea>
        </div>

        <div style="display:flex;gap:10px;">
          <button type="submit" class="admin-btn admin-btn-primary">
            Save Translation
          </button>
          <?php if ($editKey): ?>
            <a href="<?= ADMIN_URL ?>/translations.php" class="admin-btn admin-btn-outline">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
