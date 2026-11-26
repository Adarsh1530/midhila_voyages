<?php
/**
 * MIDHILA VOYAGES — Privilege Settings Module
 * Role-Based Access Control, Capability Clearance & Permission Policies
 */

require_once __DIR__ . '/includes/admin-init.php';

// Strictly restricted to Super Admin only
require_role(['super_admin']);

$db = getDB();
$roles = get_all_roles();

// Handle Saving Security & Privilege Policies
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_privilege_settings'])) {
    if (verify_csrf_token()) {
        $defaultRole   = trim($_POST['default_staff_role'] ?? 'travel_manage');
        $sessionTimeout = (int)($_POST['session_timeout_hours'] ?? 8);
        $deleteProtect  = isset($_POST['delete_protection']) ? 1 : 0;
        $waDirectMode   = isset($_POST['wa_direct_mode']) ? 1 : 0;

        if (array_key_exists($defaultRole, $roles)) {
            update_setting('default_staff_role', $defaultRole, 'security');
        }
        update_setting('session_timeout_hours', (string)$sessionTimeout, 'security');
        update_setting('privilege_delete_protection', (string)$deleteProtect, 'security');
        update_setting('privilege_wa_direct', (string)$waDirectMode, 'security');

        log_activity($_SESSION['admin_user_id'], 'privileges_updated', 'Updated system privilege and access control policies.');
        set_flash_message('success', 'Privilege settings and access policies updated successfully.');
        header('Location: ' . ADMIN_URL . '/privileges.php');
        exit;
    }
}

// Fetch active users and role distributions
$users = $db->query("SELECT `id`, `username`, `full_name`, `role` FROM `admins` WHERE `is_active` = 1")->fetchAll();
$roleCounts = [
    'super_admin'    => 0,
    'travel_manage'  => 0,
    'payment_handle' => 0,
];
foreach ($users as $u) {
    $r = $u['role'];
    if ($r === 'manager' || $r === 'editor') {
        $r = 'travel_manage';
    }
    if (isset($roleCounts[$r])) {
        $roleCounts[$r]++;
    }
}

$defaultRoleSetting = get_setting('default_staff_role', 'travel_manage');
$sessionTimeoutSetting = (int)get_setting('session_timeout_hours', '8');
$deleteProtectionSetting = (get_setting('privilege_delete_protection', '1') === '1');
$waDirectSetting = (get_setting('privilege_wa_direct', '1') === '1');

$adminTitle = 'Privilege Settings | MIDHILA VOYAGES';
$adminHeading = 'Privilege Settings';

require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- Module Switcher Tabs -->
<div style="display:flex;gap:10px;margin-bottom:24px;border-bottom:1px solid #E2E8F0;padding-bottom:14px;">
  <a href="<?= ADMIN_URL ?>/users.php" style="background:#FFFFFF;color:#475569;border:1px solid #CBD5E1;font-weight:600;padding:9px 18px;border-radius:8px;font-size:0.9rem;text-decoration:none;display:inline-flex;align-items:center;">
    <span>Staff Accounts &amp; Logins</span>
  </a>
  <a href="<?= ADMIN_URL ?>/privileges.php" style="background:#0F172A;color:#FFFFFF;font-weight:700;padding:9px 18px;border-radius:8px;font-size:0.9rem;text-decoration:none;display:inline-flex;align-items:center;">
    <span>Privilege Settings</span>
  </a>
</div>

<!-- TOP SUMMARY / STATS BAR -->
<div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:12px;padding:22px 24px;margin-bottom:28px;box-shadow:0 1px 3px rgba(0,0,0,0.03);">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
    <div>
      <h3 style="font-size:1.25rem;font-weight:700;color:#0F172A;margin:0 0 4px 0;">
        Access Control &amp; Role Clearance
      </h3>
      <p style="font-size:0.86rem;color:#64748B;margin:0;">
        Configure privilege tiers, operational permissions, and administrative capabilities across console modules.
      </p>
    </div>
    <div>
      <a href="<?= ADMIN_URL ?>/users.php" class="admin-btn admin-btn-primary" style="font-weight:700;padding:10px 18px;font-size:0.9rem;">
        Add New Staff Login
      </a>
    </div>
  </div>
</div>

<!-- 1. THREE PRIVILEGE TIERS -->
<div style="margin-bottom:28px;">
  <h4 style="font-size:1.05rem;color:#0F172A;font-weight:700;margin-bottom:14px;">Configured Privilege Tiers</h4>
  
  <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:18px;">
    
    <!-- Tier 1: Super Admin -->
    <div class="admin-card" style="padding:22px 24px;margin-bottom:0;border-top:4px solid #0F172A;box-shadow:0 1px 4px rgba(0,0,0,0.04);">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
        <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748B;letter-spacing:0.06em;">Tier 1 Privilege</span>
        <span style="background:#0F172A;color:#FFFFFF;padding:3px 9px;border-radius:6px;font-size:0.72rem;font-weight:700;">SUPER ADMIN</span>
      </div>
      <div style="font-size:1.15rem;font-weight:700;color:#0F172A;margin-bottom:6px;">Full System Control</div>
      <p style="font-size:0.84rem;color:#475569;line-height:1.5;margin-bottom:14px;">
        Unrestricted master clearance. Manages all tour packages, stays, destinations, inquiries, bookings, staff credentials, and privilege settings.
      </p>
      <div style="border-top:1px solid #F1F5F9;padding-top:12px;display:flex;justify-content:space-between;align-items:center;font-size:0.8rem;">
        <span style="color:#64748B;">Clearance Level:</span>
        <strong style="color:#0F172A;">100% Unrestricted</strong>
      </div>
      <div style="padding-top:6px;display:flex;justify-content:space-between;align-items:center;font-size:0.8rem;">
        <span style="color:#64748B;">Active Staff Assigned:</span>
        <strong style="color:#0F172A;"><?= $roleCounts['super_admin'] ?> User<?= $roleCounts['super_admin'] === 1 ? '' : 's' ?></strong>
      </div>
    </div>

    <!-- Tier 2: Travel Manage -->
    <div class="admin-card" style="padding:22px 24px;margin-bottom:0;border-top:4px solid #2563EB;box-shadow:0 1px 4px rgba(0,0,0,0.04);">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
        <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748B;letter-spacing:0.06em;">Tier 2 Privilege</span>
        <span style="background:#EFF6FF;color:#1D4ED8;padding:3px 9px;border-radius:6px;font-size:0.72rem;font-weight:700;border:1px solid #BFDBFE;">TRAVEL MANAGE</span>
      </div>
      <div style="font-size:1.15rem;font-weight:700;color:#0F172A;margin-bottom:6px;">Tours &amp; Content Operations</div>
      <p style="font-size:0.84rem;color:#475569;line-height:1.5;margin-bottom:14px;">
        Manages Tour Packages, Hotels &amp; Stays, Destinations, Kerala Tourism Pillars, Photo Gallery, Offer Banner, and responds to customer inquiries.
      </p>
      <div style="border-top:1px solid #F1F5F9;padding-top:12px;display:flex;justify-content:space-between;align-items:center;font-size:0.8rem;">
        <span style="color:#64748B;">Clearance Level:</span>
        <strong style="color:#2563EB;">Website &amp; Catalog Lead</strong>
      </div>
      <div style="padding-top:6px;display:flex;justify-content:space-between;align-items:center;font-size:0.8rem;">
        <span style="color:#64748B;">Active Staff Assigned:</span>
        <strong style="color:#2563EB;"><?= $roleCounts['travel_manage'] ?> User<?= $roleCounts['travel_manage'] === 1 ? '' : 's' ?></strong>
      </div>
    </div>

    <!-- Tier 3: Payment Handle / Front Desk -->
    <div class="admin-card" style="padding:22px 24px;margin-bottom:0;border-top:4px solid #059669;box-shadow:0 1px 4px rgba(0,0,0,0.04);">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
        <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748B;letter-spacing:0.06em;">Tier 3 Privilege</span>
        <span style="background:#ECFDF5;color:#047857;padding:3px 9px;border-radius:6px;font-size:0.72rem;font-weight:700;border:1px solid #A7F3D0;">PAYMENT HANDLE</span>
      </div>
      <div style="font-size:1.15rem;font-weight:700;color:#0F172A;margin-bottom:6px;">Bookings &amp; Desk Operations</div>
      <p style="font-size:0.84rem;color:#475569;line-height:1.5;margin-bottom:14px;">
        Reviews customer booking reservations, accesses guest contact details, prints travel vouchers, and coordinates guest WhatsApp updates.
      </p>
      <div style="border-top:1px solid #F1F5F9;padding-top:12px;display:flex;justify-content:space-between;align-items:center;font-size:0.8rem;">
        <span style="color:#64748B;">Clearance Level:</span>
        <strong style="color:#059669;">Bookings &amp; Guest Desk</strong>
      </div>
      <div style="padding-top:6px;display:flex;justify-content:space-between;align-items:center;font-size:0.8rem;">
        <span style="color:#64748B;">Active Staff Assigned:</span>
        <strong style="color:#059669;"><?= $roleCounts['payment_handle'] ?> User<?= $roleCounts['payment_handle'] === 1 ? '' : 's' ?></strong>
      </div>
    </div>

  </div>
</div>

<!-- 2. PERMISSIONS MATRIX TABLE & POLICY FORM -->
<div style="display:grid;grid-template-columns:1.8fr 1.2fr;gap:28px;align-items:start;">
  
  <!-- Left: Permissions Matrix -->
  <div class="admin-card" style="padding:0;overflow:hidden;margin-bottom:0;box-shadow:0 1px 4px rgba(0,0,0,0.05);">
    <div style="padding:18px 22px;border-bottom:1px solid #E2E8F0;background:#FAFAFA;">
      <h4 style="font-size:1rem;color:#0F172A;font-weight:700;margin:0 0 2px 0;">Module Permissions Matrix</h4>
      <p style="font-size:0.8rem;color:#64748B;margin:0;">Clearance matrix detailing access levels for each administrative console section.</p>
    </div>

    <table class="admin-table">
      <thead>
        <tr>
          <th>Console Module</th>
          <th>Super Admin</th>
          <th>Travel Manage</th>
          <th>Payment Handle</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <strong>Operations Dashboard</strong>
            <div style="font-size:0.75rem;color:#64748B;">Overview KPI counts &amp; quick action cards</div>
          </td>
          <td><span class="badge badge-success">Full Access</span></td>
          <td><span class="badge badge-success">Full Access</span></td>
          <td><span class="badge badge-success">Full Access</span></td>
        </tr>
        <tr>
          <td>
            <strong>Customer Inquiries</strong>
            <div style="font-size:0.75rem;color:#64748B;">Trip inquiries &amp; 1-tap WhatsApp chat</div>
          </td>
          <td><span class="badge badge-success">Full Control</span></td>
          <td><span class="badge badge-info">View &amp; Reply</span></td>
          <td><span class="badge badge-info">View &amp; Reply</span></td>
        </tr>
        <tr>
          <td>
            <strong>Tour Bookings</strong>
            <div style="font-size:0.75rem;color:#64748B;">Customer reservations &amp; printable vouchers</div>
          </td>
          <td><span class="badge badge-success">Full Control</span></td>
          <td><span class="badge badge-info">View &amp; Status</span></td>
          <td><span class="badge badge-success">Full Access</span></td>
        </tr>
        <tr>
          <td>
            <strong>Tour Packages</strong>
            <div style="font-size:0.75rem;color:#64748B;">Create, edit, pricing, &amp; itineraries</div>
          </td>
          <td><span class="badge badge-success">Full Control</span></td>
          <td><span class="badge badge-success">Full Control</span></td>
          <td><span class="badge badge-neutral">Restricted</span></td>
        </tr>
        <tr>
          <td>
            <strong>Destinations &amp; Places</strong>
            <div style="font-size:0.75rem;color:#64748B;">Kerala spots, backwaters, &amp; temples</div>
          </td>
          <td><span class="badge badge-success">Full Control</span></td>
          <td><span class="badge badge-success">Full Control</span></td>
          <td><span class="badge badge-neutral">Restricted</span></td>
        </tr>
        <tr>
          <td>
            <strong>Offer Popup Banner</strong>
            <div style="font-size:0.75rem;color:#64748B;">Seasonal banner, headline &amp; toggle</div>
          </td>
          <td><span class="badge badge-success">Full Control</span></td>
          <td><span class="badge badge-success">Full Control</span></td>
          <td><span class="badge badge-neutral">Restricted</span></td>
        </tr>
        <tr>
          <td>
            <strong>Photo Gallery</strong>
            <div style="font-size:0.75rem;color:#64748B;">Photo library upload (&le; 500 KB)</div>
          </td>
          <td><span class="badge badge-success">Full Control</span></td>
          <td><span class="badge badge-success">Full Control</span></td>
          <td><span class="badge badge-neutral">Restricted</span></td>
        </tr>
        <tr>
          <td>
            <strong>Staff Passwords &amp; Logins</strong>
            <div style="font-size:0.75rem;color:#64748B;">User account creation &amp; credentials</div>
          </td>
          <td><span class="badge badge-success">Full Control</span></td>
          <td><span class="badge badge-neutral">Restricted</span></td>
          <td><span class="badge badge-neutral">Restricted</span></td>
        </tr>
        <tr>
          <td>
            <strong>Privilege Settings Module</strong>
            <div style="font-size:0.75rem;color:#64748B;">Security policies &amp; clearance tiers</div>
          </td>
          <td><span class="badge badge-success">Full Control</span></td>
          <td><span class="badge badge-neutral">Restricted</span></td>
          <td><span class="badge badge-neutral">Restricted</span></td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Right: Privilege Policy Settings Form -->
  <div class="admin-card" style="padding:24px 26px;margin-bottom:0;box-shadow:0 1px 4px rgba(0,0,0,0.05);">
    <h4 style="font-size:1.05rem;color:#0F172A;font-weight:700;margin:0 0 4px 0;">Privilege Policy Settings</h4>
    <p style="font-size:0.82rem;color:#64748B;margin:0 0 20px 0;">Define global access control defaults for newly registered accounts.</p>

    <form method="POST" action="<?= ADMIN_URL ?>/privileges.php">
      <?= csrf_field() ?>
      <input type="hidden" name="save_privilege_settings" value="1">

      <div class="admin-form-group" style="margin-bottom:18px;">
        <label style="font-weight:700;color:#0F172A;font-size:0.86rem;margin-bottom:6px;">Default Role For New Staff</label>
        <select name="default_staff_role" style="font-weight:600;padding:9px 12px;border:1.5px solid #CBD5E1;border-radius:8px;width:100%;">
          <?php foreach ($roles as $rKey => $rMeta): ?>
            <option value="<?= e($rKey) ?>" <?= ($defaultRoleSetting === $rKey) ? 'selected' : '' ?>>
              <?= e($rMeta['label']) ?> (<?= e($rMeta['tag']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <span style="font-size:0.75rem;color:#64748B;display:block;margin-top:4px;">Pre-selected role assigned when registering a new staff member.</span>
      </div>

      <div class="admin-form-group" style="margin-bottom:18px;">
        <label style="font-weight:700;color:#0F172A;font-size:0.86rem;margin-bottom:6px;">Session Security Timeout</label>
        <select name="session_timeout_hours" style="font-weight:600;padding:9px 12px;border:1.5px solid #CBD5E1;border-radius:8px;width:100%;">
          <option value="2" <?= ($sessionTimeoutSetting === 2) ? 'selected' : '' ?>>2 Hours (High Security)</option>
          <option value="8" <?= ($sessionTimeoutSetting === 8) ? 'selected' : '' ?>>8 Hours (Standard Working Day)</option>
          <option value="24" <?= ($sessionTimeoutSetting === 24) ? 'selected' : '' ?>>24 Hours (Extended Session)</option>
          <option value="168" <?= ($sessionTimeoutSetting === 168) ? 'selected' : '' ?>>7 Days (Remember Me)</option>
        </select>
        <span style="font-size:0.75rem;color:#64748B;display:block;margin-top:4px;">Period of inactivity before requiring staff re-authentication.</span>
      </div>

      <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:14px;margin-bottom:18px;">
        <label style="display:inline-flex;align-items:flex-start;gap:8px;cursor:pointer;font-size:0.86rem;font-weight:600;color:#0F172A;">
          <input type="checkbox" name="delete_protection" value="1" <?= $deleteProtectionSetting ? 'checked' : '' ?> style="margin-top:3px;accent-color:#0F172A;">
          <div>
            <span>Delete Protection for Records</span>
            <div style="font-size:0.75rem;color:#64748B;font-weight:400;margin-top:2px;">
              Only Super Admin can permanently delete inquiries, packages, or bookings. Other staff roles can only view and update statuses.
            </div>
          </div>
        </label>
      </div>

      <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:14px;margin-bottom:24px;">
        <label style="display:inline-flex;align-items:flex-start;gap:8px;cursor:pointer;font-size:0.86rem;font-weight:600;color:#0F172A;">
          <input type="checkbox" name="wa_direct_mode" value="1" <?= $waDirectSetting ? 'checked' : '' ?> style="margin-top:3px;accent-color:#0F172A;">
          <div>
            <span>Direct WhatsApp Communication</span>
            <div style="font-size:0.75rem;color:#64748B;font-weight:400;margin-top:2px;">
              Enables 1-tap WhatsApp chat across all staff tiers using agency contact number 6282948227.
            </div>
          </div>
        </label>
      </div>

      <button type="submit" class="admin-btn admin-btn-primary" style="width:100%;justify-content:center;padding:11px;font-weight:700;">
        Save Privilege Settings
      </button>
    </form>
  </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
