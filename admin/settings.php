<?php
/**
 * MIDHILA VOYAGES — Global Site Settings & Agency Profile
 */

require_once __DIR__ . '/includes/admin-init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token()) {
        $settingsToUpdate = [
            'site_name' => ['val' => trim($_POST['site_name'] ?? 'MIDHILA VOYAGES'), 'group' => 'general'],
            'site_tagline' => ['val' => trim($_POST['site_tagline'] ?? ''), 'group' => 'general'],
            'primary_phone' => ['val' => trim($_POST['primary_phone'] ?? ''), 'group' => 'contact'],
            'secondary_phone' => ['val' => trim($_POST['secondary_phone'] ?? ''), 'group' => 'contact'],
            'whatsapp_number' => ['val' => trim($_POST['whatsapp_number'] ?? ''), 'group' => 'contact'],
            'primary_email' => ['val' => trim($_POST['primary_email'] ?? ''), 'group' => 'contact'],
            'office_address' => ['val' => trim($_POST['office_address'] ?? ''), 'group' => 'contact'],
            'working_hours' => ['val' => trim($_POST['working_hours'] ?? ''), 'group' => 'contact'],
            'facebook_url' => ['val' => trim($_POST['facebook_url'] ?? ''), 'group' => 'social'],
            'instagram_url' => ['val' => trim($_POST['instagram_url'] ?? ''), 'group' => 'social'],
            'youtube_url' => ['val' => trim($_POST['youtube_url'] ?? ''), 'group' => 'social'],
            'hero_headline' => ['val' => trim($_POST['hero_headline'] ?? ''), 'group' => 'hero'],
            'hero_subheading' => ['val' => trim($_POST['hero_subheading'] ?? ''), 'group' => 'hero'],
            'razorpay_key_id' => ['val' => trim($_POST['razorpay_key_id'] ?? ''), 'group' => 'payment'],
            'razorpay_key_secret' => ['val' => trim($_POST['razorpay_key_secret'] ?? ''), 'group' => 'payment'],
            'payment_mode' => ['val' => in_array($_POST['payment_mode'] ?? '', ['test', 'live']) ? $_POST['payment_mode'] : 'test', 'group' => 'payment'],
        ];

        foreach ($settingsToUpdate as $key => $item) {
            update_setting($key, $item['val'], $item['group']);
        }

        log_activity($_SESSION['admin_user_id'] ?? null, 'update_settings', 'Updated agency profile and site configuration.');
        set_flash_message('success', 'Agency settings updated successfully.');
        header('Location: ' . ADMIN_URL . '/settings.php');
        exit;
    }
}

// Fetch current values
$settings = [];
$stmt = $db->query("SELECT `setting_key`, `setting_value` FROM `site_settings`");
while ($r = $stmt->fetch()) {
    $settings[$r['setting_key']] = $r['setting_value'];
}

$adminTitle = 'Site Settings | MIDHILA VOYAGES';
$adminHeading = 'Agency Settings & Configuration';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="max-width:860px;margin:0 auto;">
  
  <div style="background:#EFF6FF;border:1px solid #BFDBFE;border-radius:10px;padding:16px 20px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;align-items:center;gap:10px;">
      <span style="font-size:1.3rem;">📝</span>
      <div>
        <strong style="color:#1E40AF;font-size:0.92rem;">Looking to edit headlines, stories, or page sections?</strong>
        <p style="font-size:0.82rem;color:#3B82F6;margin:0;">You can customize every section of the public website in the new visual editor.</p>
      </div>
    </div>
    <a href="<?= ADMIN_URL ?>/content.php" class="admin-btn admin-btn-sm" style="background:#2563EB;color:#FFFFFF !important;font-weight:700;">
      <span>Go to Page Sections Editor &rarr;</span>
    </a>
  </div>

  <div style="margin-bottom:24px;">
    <h3 style="font-size:1.3rem;color:var(--admin-navy);">Agency Profile &amp; Settings</h3>
    <p style="font-size:0.85rem;color:var(--admin-muted);">Manage verified contact details, WhatsApp integration parameters, and payment keys.</p>
  </div>

  <form method="POST" action="">
    <?= csrf_field() ?>

    <!-- 1. Agency Identity & Contact -->
    <div class="admin-card">
      <h4 style="font-size:1.1rem;color:var(--admin-navy);margin-bottom:16px;border-bottom:1px solid var(--admin-border);padding-bottom:8px;">1. Agency Contact Details</h4>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:16px;">
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Agency Name *</label>
          <input type="text" name="site_name" required value="<?= e($settings['site_name'] ?? 'MIDHILA VOYAGES') ?>" style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
        </div>
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Tagline</label>
          <input type="text" name="site_tagline" value="<?= e($settings['site_tagline'] ?? '') ?>" style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px;">
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Official WhatsApp (with country code) *</label>
          <input type="text" name="whatsapp_number" required placeholder="+911234567890" value="<?= e($settings['whatsapp_number'] ?? '+911234567890') ?>" style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;border-color:var(--admin-teal);">
        </div>
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Primary Phone *</label>
          <input type="text" name="primary_phone" required value="<?= e($settings['primary_phone'] ?? '+91 12345 67890') ?>" style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
        </div>
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Secondary Phone</label>
          <input type="text" name="secondary_phone" value="<?= e($settings['secondary_phone'] ?? '') ?>" style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:16px;">
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Primary Email *</label>
          <input type="email" name="primary_email" required value="<?= e($settings['primary_email'] ?? 'journeys@midhilavoyages.com') ?>" style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
        </div>
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Working Hours</label>
          <input type="text" name="working_hours" value="<?= e($settings['working_hours'] ?? 'Monday – Saturday: 9:00 AM – 7:30 PM (IST)') ?>" style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
        </div>
      </div>

      <div>
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Physical Office Address *</label>
        <textarea name="office_address" rows="2" required style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;"><?= e($settings['office_address'] ?? '') ?></textarea>
      </div>
    </div>

    <!-- 2. Homepage Hero Headline -->
    <div class="admin-card">
      <h4 style="font-size:1.1rem;color:var(--admin-navy);margin-bottom:16px;border-bottom:1px solid var(--admin-border);padding-bottom:8px;">2. Homepage Hero Content</h4>
      
      <div style="margin-bottom:16px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Main Editorial Headline</label>
        <input type="text" name="hero_headline" value="<?= e($settings['hero_headline'] ?? "The World Is Waiting. Let's Make It Yours.") ?>" style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
      </div>

      <div>
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Supporting Subheading</label>
        <textarea name="hero_subheading" rows="2" style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;"><?= e($settings['hero_subheading'] ?? '') ?></textarea>
      </div>
    </div>

    <!-- 3. Payment Gateway Configuration -->
    <div class="admin-card">
      <h4 style="font-size:1.1rem;color:var(--admin-navy);margin-bottom:16px;border-bottom:1px solid var(--admin-border);padding-bottom:8px;">3. Payment Gateway (Razorpay)</h4>
      
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Gateway Mode</label>
          <select name="payment_mode" style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;background:#FFF;">
            <option value="test" <?= (($settings['payment_mode'] ?? 'test') === 'test') ? 'selected' : '' ?>>Test Mode (Sandbox)</option>
            <option value="live" <?= (($settings['payment_mode'] ?? '') === 'live') ? 'selected' : '' ?>>Live Mode (Production)</option>
          </select>
        </div>
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Key ID</label>
          <input type="text" name="razorpay_key_id" value="<?= e($settings['razorpay_key_id'] ?? '') ?>" placeholder="rzp_test_..." style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
        </div>
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Key Secret</label>
          <input type="password" name="razorpay_key_secret" value="<?= e($settings['razorpay_key_secret'] ?? '') ?>" placeholder="••••••••" style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
        </div>
      </div>
    </div>

    <!-- 4. Social Media Links -->
    <div class="admin-card">
      <h4 style="font-size:1.1rem;color:var(--admin-navy);margin-bottom:16px;border-bottom:1px solid var(--admin-border);padding-bottom:8px;">4. Social Media Channels</h4>
      
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Facebook URL</label>
          <input type="url" name="facebook_url" value="<?= e($settings['facebook_url'] ?? '') ?>" placeholder="https://facebook.com/..." style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.88rem;">
        </div>
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Instagram URL</label>
          <input type="url" name="instagram_url" value="<?= e($settings['instagram_url'] ?? '') ?>" placeholder="https://instagram.com/..." style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.88rem;">
        </div>
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">YouTube Channel</label>
          <input type="url" name="youtube_url" value="<?= e($settings['youtube_url'] ?? '') ?>" placeholder="https://youtube.com/..." style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.88rem;">
        </div>
      </div>
    </div>

    <div style="margin-bottom:40px;">
      <button type="submit" class="admin-btn admin-btn-primary" style="padding:12px 32px;font-size:1rem;">
        <span>Save All Settings</span>
      </button>
    </div>

  </form>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
