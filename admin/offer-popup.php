<?php
/**
 * MIDHILA VOYAGES — Promotional Offer Popup Management
 * Control live popup banners, ongoing discounts & tour flyers for website visitors
 */

require_once __DIR__ . '/includes/admin-init.php';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token()) {
        $offerFields = [
            'popup_offer_active' => ['group' => 'offer_popup', 'default' => '1'],
            'popup_offer_badge' => ['group' => 'offer_popup', 'default' => 'EXCLUSIVE ONGOING OFFER'],
            'popup_offer_title' => ['group' => 'offer_popup', 'default' => 'Divine Destinations • 5-Day Sacred Yatra'],
            'popup_offer_subtitle' => ['group' => 'offer_popup', 'default' => 'Experience Rameswaram, Madurai, Dhanushkodi & Thanjavur at ₹6,500/person. Sanitized AC transport & curated stays included.'],
            'popup_offer_price' => ['group' => 'offer_popup', 'default' => '₹6,500'],
            'popup_offer_price_sub' => ['group' => 'offer_popup', 'default' => 'per person • 5D / 4N'],
            'popup_offer_dates' => ['group' => 'offer_popup', 'default' => 'Upcoming Tour Dates: 23.12.26 to 27.12.26'],
            'popup_offer_image' => ['group' => 'offer_popup', 'default' => 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1200&q=80'],
            'popup_offer_btn_text' => ['group' => 'offer_popup', 'default' => 'Claim Offer on WhatsApp'],
            'popup_offer_link' => ['group' => 'offer_popup', 'default' => ''],
            'popup_offer_frequency' => ['group' => 'offer_popup', 'default' => 'session'],
        ];

        // Handle Image Upload if provided
        if (!empty($_FILES['popup_offer_file']['name'])) {
            $up = upload_image($_FILES['popup_offer_file'], 'offers', 5);
            if ($up['success'] && !empty($up['path'])) {
                update_setting('popup_offer_image', $up['path'], 'offer_popup');
            }
        }

        foreach ($offerFields as $key => $meta) {
            if (isset($_POST[$key])) {
                $val = trim((string)$_POST[$key]);
                update_setting($key, $val, $meta['group']);
            }
        }

        log_activity($_SESSION['admin_user_id'] ?? null, 'update_offer_popup', 'Updated promotional offer popup settings.');
        set_flash_message('success', 'Offer popup updated successfully! All visitors will see your updated ongoing promotion.');
        header('Location: ' . ADMIN_URL . '/offer-popup.php');
        exit;
    }
}

// Fetch current site settings
$settings = [];
try {
    $stmt = $db->query("SELECT `setting_key`, `setting_value` FROM `site_settings`");
    while ($r = $stmt->fetch()) {
        $settings[$r['setting_key']] = $r['setting_value'];
    }
} catch (Exception $e) {}

function val(string $key, string $default = ''): string {
    global $settings;
    return $settings[$key] ?? $default;
}

$adminTitle = 'Offer Popup | MIDHILA VOYAGES';
$adminHeading = 'Promotional Offer Popup';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="max-width:1040px;margin:0 auto;padding-bottom:100px;">

  <!-- Top Status & Actions Banner -->
  <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:12px;padding:22px 26px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
    <div style="display:flex;align-items:center;gap:14px;">
      <div style="width:44px;height:44px;border-radius:10px;background:#FEF3C7;color:#D97706;display:flex;align-items:center;justify-content:center;font-size:1.4rem;">
        🎉
      </div>
      <div>
        <h3 style="font-size:1.25rem;font-weight:700;color:#0F172A;margin-bottom:2px;">Ongoing Offer Promotional Popup</h3>
        <p style="font-size:0.84rem;color:#64748B;margin:0;">Automatically presents seasonal deals, packages, and flyers to attract visitors.</p>
      </div>
    </div>

    <div style="display:flex;align-items:center;gap:12px;">
      <a href="<?= BASE_URL ?>/index.php?offer=1" target="_blank" class="admin-btn admin-btn-sm" style="background:#0F172A;color:#FFFFFF;font-weight:700;display:inline-flex;align-items:center;gap:6px;box-shadow:0 4px 10px rgba(15,23,42,0.15);">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg>
        <span>Test Live Popup ↗</span>
      </a>
    </div>
  </div>

  <form method="POST" action="" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">
      
      <!-- Form Controls -->
      <div class="admin-card" style="padding:28px;">
        
        <!-- Offer Status Toggle -->
        <div style="background:#F8FAFC;border:1.5px solid #E2E8F0;border-radius:10px;padding:18px 20px;margin-bottom:24px;">
          <label style="display:block;font-size:0.86rem;font-weight:700;color:#0F172A;margin-bottom:8px;">Popup Display Status</label>
          <div style="display:flex;gap:20px;align-items:center;">
            <label style="display:inline-flex;align-items:center;gap:8px;font-size:0.92rem;font-weight:600;color:#059669;cursor:pointer;">
              <input type="radio" name="popup_offer_active" value="1" <?= (val('popup_offer_active', '1') === '1') ? 'checked' : '' ?> style="accent-color:#059669;">
              <span>🟢 Active (Enabled on Website)</span>
            </label>
            <label style="display:inline-flex;align-items:center;gap:8px;font-size:0.92rem;font-weight:600;color:#64748B;cursor:pointer;">
              <input type="radio" name="popup_offer_active" value="0" <?= (val('popup_offer_active', '1') === '0') ? 'checked' : '' ?> style="accent-color:#64748B;">
              <span>⚪ Inactive (Temporarily Hidden)</span>
            </label>
          </div>
          <span style="font-size:0.76rem;color:#64748B;display:block;margin-top:6px;">When set to Active, visitors opening the website will see this promotional offer popup.</span>
        </div>

        <!-- Promotional Poster / Image -->
        <div style="margin-bottom:24px;border-bottom:1px solid #F1F5F9;padding-bottom:20px;">
          <h5 style="font-size:0.95rem;font-weight:700;color:#0F172A;margin-bottom:12px;">1. Offer Promotional Image / Flyer</h5>
          
          <div style="margin-bottom:14px;">
            <label style="display:block;font-size:0.82rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Upload Image File (JPG, PNG or WebP)</label>
            <input type="file" name="popup_offer_file" accept="image/*" style="width:100%;padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;background:#FFF;">
            <span style="font-size:0.76rem;color:#64748B;display:block;margin-top:4px;">Upload your promotional poster or banner from your computer (Max 5MB).</span>
          </div>

          <div>
            <label style="display:block;font-size:0.82rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Or Paste Image URL</label>
            <input type="text" name="popup_offer_image" value="<?= e(val('popup_offer_image', 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1200&q=80')) ?>" placeholder="https://..." style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
          </div>
        </div>

        <!-- Offer Copy & Details -->
        <div style="margin-bottom:24px;border-bottom:1px solid #F1F5F9;padding-bottom:20px;">
          <h5 style="font-size:0.95rem;font-weight:700;color:#0F172A;margin-bottom:12px;">2. Offer Headline &amp; Pricing</h5>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:16px;">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Badge Tag</label>
              <input type="text" name="popup_offer_badge" value="<?= e(val('popup_offer_badge', 'EXCLUSIVE ONGOING OFFER')) ?>" placeholder="e.g. SPECIAL OFFER" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Main Offer Headline *</label>
              <input type="text" name="popup_offer_title" value="<?= e(val('popup_offer_title', 'Divine Destinations • 5-Day Sacred Yatra')) ?>" required style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:16px;">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Featured Price (e.g. ₹6,500)</label>
              <input type="text" name="popup_offer_price" value="<?= e(val('popup_offer_price', '₹6,500')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.95rem;font-weight:700;color:#059669;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Price Subtitle / Duration</label>
              <input type="text" name="popup_offer_price_sub" value="<?= e(val('popup_offer_price_sub', 'per person • 5D / 4N')) ?>" placeholder="e.g. per person" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
            </div>
          </div>

          <div style="margin-bottom:16px;">
            <label style="display:block;font-size:0.82rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Upcoming Tour Dates / Booking Note</label>
            <input type="text" name="popup_offer_dates" value="<?= e(val('popup_offer_dates', 'Upcoming Tour Dates: 23.12.26 to 27.12.26')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
          </div>

          <div>
            <label style="display:block;font-size:0.82rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Offer Description &amp; Highlights</label>
            <textarea name="popup_offer_subtitle" rows="3" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;line-height:1.6;"><?= e(val('popup_offer_subtitle', 'Experience Rameswaram, Madurai, Dhanushkodi & Thanjavur at ₹6,500/person. Sanitized AC transport & curated stays included.')) ?></textarea>
          </div>
        </div>

        <!-- Action Button & WhatsApp Configuration -->
        <div>
          <h5 style="font-size:0.95rem;font-weight:700;color:#0F172A;margin-bottom:12px;">3. Call to Action Button &amp; Display Settings</h5>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:16px;">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Button Label</label>
              <input type="text" name="popup_offer_btn_text" value="<?= e(val('popup_offer_btn_text', 'Claim Offer on WhatsApp')) ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Popup Frequency</label>
              <select name="popup_offer_frequency" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;background:#FFF;">
                <option value="session" <?= (val('popup_offer_frequency', 'session') === 'session') ? 'selected' : '' ?>>Show once per session (Recommended)</option>
                <option value="always" <?= (val('popup_offer_frequency', 'session') === 'always') ? 'selected' : '' ?>>Show on every page visit (Aggressive)</option>
              </select>
            </div>
          </div>

          <div>
            <label style="display:block;font-size:0.82rem;font-weight:700;color:#1E293B;margin-bottom:6px;">Custom Destination URL (Optional)</label>
            <input type="url" name="popup_offer_link" value="<?= e(val('popup_offer_link', '')) ?>" placeholder="Leave blank to automatically open WhatsApp chat" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
            <span style="font-size:0.76rem;color:#64748B;display:block;margin-top:4px;">If left blank, clicking the button instantly opens WhatsApp chat with your agency with the offer pre-filled.</span>
          </div>
        </div>

      </div>

      <!-- Live Visual Mockup Preview -->
      <div style="position:sticky;top:90px;">
        <div style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#64748B;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between;">
          <span>Live Visitor Preview</span>
          <span style="font-size:0.72rem;background:#E2E8F0;padding:2px 8px;border-radius:999px;color:#0F172A;">Interactive</span>
        </div>

        <div style="background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 20px 45px -10px rgba(15,23,42,0.18), 0 0 0 1px rgba(197,160,89,0.35);border:1px solid #E2E8F0;">
          <div style="position:relative;height:160px;background:#0F172A;overflow:hidden;">
            <?php 
              $previewImg = val('popup_offer_image', 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1200&q=80');
              if (!empty($previewImg) && !preg_match('#^https?://#i', $previewImg)) {
                  $previewImg = BASE_URL . '/' . ltrim($previewImg, '/');
              }
            ?>
            <img src="<?= e($previewImg) ?>" alt="Offer Preview" style="width:100%;height:100%;object-fit:cover;display:block;">
            <div style="position:absolute;bottom:10px;left:12px;background:rgba(15,23,42,0.85);backdrop-filter:blur(4px);color:#FBBF24;padding:4px 10px;border-radius:999px;font-size:0.68rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;border:1px solid rgba(251,191,36,0.35);">
              <?= e(val('popup_offer_badge', 'EXCLUSIVE ONGOING OFFER')) ?>
            </div>
          </div>

          <div style="padding:18px 20px;text-align:center;">
            <div style="font-family:'Cinzel',serif;font-size:1.1rem;font-weight:700;color:#0F172A;line-height:1.25;margin-bottom:6px;">
              <?= e(val('popup_offer_title', 'Divine Destinations • 5-Day Sacred Yatra')) ?>
            </div>

            <div style="font-size:1.4rem;font-weight:800;color:#059669;margin-bottom:4px;">
              <?= e(val('popup_offer_price', '₹6,500')) ?>
            </div>

            <div style="font-size:0.75rem;background:#F8FAFC;border:1px solid #E2E8F0;padding:3px 10px;border-radius:4px;display:inline-block;color:#475569;font-weight:600;margin-bottom:10px;">
              <?= e(val('popup_offer_dates', 'Upcoming Tour Dates: 23.12.26 to 27.12.26')) ?>
            </div>

            <p style="font-size:0.78rem;color:#64748B;line-height:1.5;margin-bottom:14px;max-height:60px;overflow:hidden;">
              <?= e(val('popup_offer_subtitle', 'Experience Rameswaram, Madurai, Dhanushkodi & Thanjavur at ₹6,500/person.')) ?>
            </p>

            <div style="background:#25D366;color:#FFF;padding:10px 16px;border-radius:8px;font-size:0.86rem;font-weight:700;display:flex;align-items:center;justify-content:center;gap:6px;box-shadow:0 4px 12px rgba(37,211,102,0.3);">
              <span><?= e(val('popup_offer_btn_text', 'Claim Offer on WhatsApp')) ?></span>
            </div>
          </div>
        </div>

        <div style="text-align:center;margin-top:12px;">
          <a href="<?= BASE_URL ?>/index.php?offer=1" target="_blank" style="font-size:0.82rem;font-weight:700;color:#2563EB;display:inline-flex;align-items:center;gap:4px;">
            <span>Open live website test view &rarr;</span>
          </a>
        </div>
      </div>

    </div>

    <!-- Sticky Floating Bottom Save Bar -->
    <div class="content-save-bar" style="position:fixed;bottom:0;left:260px;right:0;background:#FFFFFF;border-top:1px solid #E2E8F0;padding:16px 32px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 -4px 16px rgba(15,23,42,0.06);z-index:90;">
      <div style="display:flex;align-items:center;gap:10px;">
        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#10B981;"></span>
        <span style="font-size:0.85rem;color:#475569;font-weight:500;">Live updates are automatically reflected on the public website</span>
      </div>

      <div style="display:flex;align-items:center;gap:12px;">
        <button type="submit" class="admin-btn admin-btn-primary" style="padding:11px 32px;font-size:0.95rem;font-weight:700;display:inline-flex;align-items:center;gap:8px;box-shadow:0 4px 12px rgba(15,23,42,0.15);">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
          <span>Save Offer Settings</span>
        </button>
      </div>
    </div>

    <style>
      @media (max-width: 992px) {
        .content-save-bar { left: 0 !important; padding: 14px 20px !important; }
      }
    </style>

  </form>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
