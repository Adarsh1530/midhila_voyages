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

<!-- Module Permissions Matrix -->
<div class="admin-card" style="padding:0;overflow:hidden;margin-bottom:0;box-shadow:0 1px 4px rgba(0,0,0,0.05);max-width:960px;margin:0 auto;">
  <div style="padding:18px 22px;border-bottom:1px solid #E2E8F0;background:#FAFAFA;">
    <h4 style="font-size:1.1rem;color:#0F172A;font-weight:700;margin:0 0 4px 0;">Module Permissions Matrix</h4>
    <p style="font-size:0.84rem;color:#64748B;margin:0;">Clearance matrix detailing access levels for each administrative console section.</p>
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
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
