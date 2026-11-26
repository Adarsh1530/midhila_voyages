<?php
/**
 * MIDHILA VOYAGES — Privilege Settings Module
 * Role-Based Access Control, Capability Clearance & Check-In / Check-Out Permissions
 */

require_once __DIR__ . '/includes/admin-init.php';

// Strictly restricted to Super Admin only
require_role(['super_admin']);

$db = getDB();
$roles = get_all_roles();
$modules = get_admin_modules();

// Handle Saving Check In / Check Out Module Privileges
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_privileges'])) {
    if (verify_csrf_token()) {
        $newPrivs = [];
        $submittedPriv = $_POST['priv'] ?? [];

        foreach ($modules as $modKey => $mMeta) {
            // Super Admin always has 100% full master clearance
            $newPrivs[$modKey] = ['super_admin'];

            foreach (['travel_manage', 'payment_handle'] as $rKey) {
                if (!empty($submittedPriv[$modKey][$rKey])) {
                    $newPrivs[$modKey][] = $rKey;
                }
            }
        }

        update_setting('module_privileges', json_encode($newPrivs), 'security');
        log_activity($_SESSION['admin_user_id'] ?? null, 'privileges_updated', 'Updated console module privilege access matrix.');
        set_flash_message('success', 'Privilege settings saved successfully! Changes are now active across the admin console.');
        header('Location: ' . ADMIN_URL . '/privileges.php');
        exit;
    } else {
        set_flash_message('error', 'Security token expired. Please try again.');
    }
}

// Handle Reset to Defaults
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_defaults'])) {
    if (verify_csrf_token()) {
        $defaults = get_default_module_privileges();
        update_setting('module_privileges', json_encode($defaults), 'security');
        log_activity($_SESSION['admin_user_id'] ?? null, 'privileges_reset', 'Reset console module privileges to default roles.');
        set_flash_message('success', 'Privileges have been reset to default permissions.');
        header('Location: ' . ADMIN_URL . '/privileges.php');
        exit;
    }
}

$currentPrivileges = get_module_privileges();

$adminTitle = 'Privilege Settings | MIDHILA VOYAGES';
$adminHeading = 'Privilege Settings';

require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="max-width:1120px;margin:0 auto;padding-bottom:80px;">

  <!-- Form Header & Quick Controls -->
  <form method="POST" action="<?= ADMIN_URL ?>/privileges.php" id="privilegesForm">
    <?= csrf_field() ?>

    <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:12px;padding:22px 26px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;box-shadow:0 1px 3px rgba(0,0,0,0.03);">
      <div>
        <h3 style="font-size:1.25rem;font-weight:700;color:#0F172A;margin:0 0 4px 0;">
          Privilege Access Control
        </h3>
        <p style="font-size:0.86rem;color:#64748B;margin:0;">
          Check In to grant access or Check Out to revoke access for each admin console menu option.
        </p>
      </div>

      <div style="display:flex;gap:12px;align-items:center;">
        <button type="submit" name="save_privileges" value="1" class="admin-btn admin-btn-primary" style="font-weight:700;padding:11px 24px;font-size:0.92rem;display:inline-flex;align-items:center;gap:8px;box-shadow:0 4px 12px rgba(15,23,42,0.15);">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
          <span>Save Privilege Settings</span>
        </button>
      </div>
    </div>

    <!-- Main Privilege Permissions Matrix Card -->
    <div class="admin-card" style="padding:0;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.05);border-radius:12px;">
      
      <div style="padding:18px 24px;border-bottom:1px solid #E2E8F0;background:#FAFAFA;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <div>
          <h4 style="font-size:1.05rem;color:#0F172A;font-weight:700;margin:0 0 2px 0;">Module Permissions Matrix</h4>
          <p style="font-size:0.8rem;color:#64748B;margin:0;">Interactive Check In / Check Out options mapped directly to the sidebar navigation menu.</p>
        </div>

        <div style="display:flex;gap:10px;align-items:center;">
          <span style="font-size:0.78rem;color:#64748B;font-weight:600;">Quick Action:</span>
          <button type="button" onclick="toggleAllRole('travel_manage', true)" class="admin-btn admin-btn-outline" style="padding:4px 10px;font-size:0.75rem;">Check In All (Travel)</button>
          <button type="button" onclick="toggleAllRole('payment_handle', true)" class="admin-btn admin-btn-outline" style="padding:4px 10px;font-size:0.75rem;">Check In All (Payment)</button>
        </div>
      </div>

      <div style="overflow-x:auto;">
        <table class="admin-table" style="margin-bottom:0;">
          <thead>
            <tr style="background:#F8FAFC;">
              <th style="width:34%;padding:16px 22px;font-size:0.82rem;font-weight:700;text-transform:uppercase;color:#475569;letter-spacing:0.04em;">Console Menu</th>
              
              <!-- Super Admin Column -->
              <th style="width:22%;text-align:center;padding:16px 18px;">
                <div style="display:inline-flex;align-items:center;gap:6px;background:#0F172A;color:#FFFFFF;padding:4px 12px;border-radius:6px;font-size:0.75rem;font-weight:700;letter-spacing:0.05em;">
                  <span>SUPER ADMIN</span>
                </div>
                <div style="font-size:0.72rem;color:#64748B;margin-top:4px;font-weight:600;">Master Clearance (Full Access)</div>
              </th>

              <!-- Travel Manage Column -->
              <th style="width:22%;text-align:center;padding:16px 18px;">
                <div style="display:inline-flex;align-items:center;gap:6px;background:#EFF6FF;color:#1D4ED8;padding:4px 12px;border-radius:6px;font-size:0.75rem;font-weight:700;border:1px solid #BFDBFE;">
                  <span>TRAVEL MANAGE</span>
                </div>
                <div style="margin-top:6px;display:flex;justify-content:center;gap:8px;">
                  <button type="button" onclick="toggleAllRole('travel_manage', true)" style="background:none;border:none;color:#2563EB;font-size:0.72rem;font-weight:700;cursor:pointer;padding:0;text-decoration:underline;">All In</button>
                  <span style="color:#CBD5E1;font-size:0.72rem;">|</span>
                  <button type="button" onclick="toggleAllRole('travel_manage', false)" style="background:none;border:none;color:#64748B;font-size:0.72rem;font-weight:700;cursor:pointer;padding:0;text-decoration:underline;">All Out</button>
                </div>
              </th>

              <!-- Payment Handle Column -->
              <th style="width:22%;text-align:center;padding:16px 18px;">
                <div style="display:inline-flex;align-items:center;gap:6px;background:#ECFDF5;color:#047857;padding:4px 12px;border-radius:6px;font-size:0.75rem;font-weight:700;border:1px solid #A7F3D0;">
                  <span>PAYMENT HANDLE</span>
                </div>
                <div style="margin-top:6px;display:flex;justify-content:center;gap:8px;">
                  <button type="button" onclick="toggleAllRole('payment_handle', true)" style="background:none;border:none;color:#059669;font-size:0.72rem;font-weight:700;cursor:pointer;padding:0;text-decoration:underline;">All In</button>
                  <span style="color:#CBD5E1;font-size:0.72rem;">|</span>
                  <button type="button" onclick="toggleAllRole('payment_handle', false)" style="background:none;border:none;color:#64748B;font-size:0.72rem;font-weight:700;cursor:pointer;padding:0;text-decoration:underline;">All Out</button>
                </div>
              </th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($modules as $modKey => $mMeta): ?>
              <?php 
                $tmChecked = in_array('travel_manage', $currentPrivileges[$modKey] ?? [], true);
                $phChecked = in_array('payment_handle', $currentPrivileges[$modKey] ?? [], true);
              ?>
              <tr style="border-bottom:1px solid #F1F5F9;transition:background 0.15s ease;">
                
                <!-- 1. Menu Name & Description -->
                <td style="padding:16px 22px;vertical-align:middle;">
                  <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:36px;height:36px;border-radius:8px;background:#F8FAFC;border:1px solid #E2E8F0;display:flex;align-items:center;justify-content:center;color:#0F172A;flex-shrink:0;">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <?php if ($modKey === 'dashboard'): ?>
                          <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline>
                        <?php elseif ($modKey === 'enquiries'): ?>
                          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        <?php elseif ($modKey === 'bookings'): ?>
                          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>
                        <?php elseif ($modKey === 'packages'): ?>
                          <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <?php elseif ($modKey === 'destinations'): ?>
                          <circle cx="12" cy="10" r="3"></circle><path d="M12 21.7C17.3 17 20 13 20 10a8 8 0 1 0-16 0c0 3 2.7 6.9 8 11.7z"></path>
                        <?php elseif ($modKey === 'offer_popup'): ?>
                          <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        <?php elseif ($modKey === 'gallery'): ?>
                          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline>
                        <?php elseif ($modKey === 'users'): ?>
                          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <?php else: ?>
                          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        <?php endif; ?>
                      </svg>
                    </div>
                    <div>
                      <div style="font-weight:700;font-size:0.95rem;color:#0F172A;"><?= e($mMeta['name']) ?></div>
                      <div style="font-size:0.77rem;color:#64748B;line-height:1.4;"><?= e($mMeta['desc']) ?></div>
                    </div>
                  </div>
                </td>

                <!-- 2. Super Admin Checkbox (Locked Full Access) -->
                <td style="text-align:center;vertical-align:middle;padding:16px 18px;background:rgba(15,23,42,0.015);">
                  <div style="display:inline-flex;align-items:center;gap:8px;background:#F1F5F9;padding:7px 14px;border-radius:999px;border:1px solid #CBD5E1;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0F172A" stroke-width="3">
                      <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span style="font-size:0.78rem;font-weight:800;color:#0F172A;letter-spacing:0.02em;">Check In</span>
                  </div>
                  <input type="hidden" name="priv[<?= e($modKey) ?>][super_admin]" value="1">
                </td>

                <!-- 3. Travel Manage Checkbox (Check In / Check Out) -->
                <td style="text-align:center;vertical-align:middle;padding:16px 18px;">
                  <label class="priv-toggle-box" style="display:inline-flex;align-items:center;gap:10px;cursor:pointer;padding:8px 14px;border-radius:10px;border:1.5px solid <?= $tmChecked ? '#3B82F6' : '#E2E8F0' ?>;background:<?= $tmChecked ? '#EFF6FF' : '#FFFFFF' ?>;transition:all 0.2s ease;">
                    <input type="checkbox" 
                           name="priv[<?= e($modKey) ?>][travel_manage]" 
                           value="1" 
                           data-role="travel_manage"
                           <?= $tmChecked ? 'checked' : '' ?> 
                           onchange="updateToggleStyle(this)"
                           style="width:18px;height:18px;accent-color:#2563EB;cursor:pointer;">
                    <span class="priv-label-text" style="font-size:0.82rem;font-weight:700;color:<?= $tmChecked ? '#1D4ED8' : '#64748B' ?>;">
                      <?= $tmChecked ? 'Check In' : 'Check Out' ?>
                    </span>
                  </label>
                </td>

                <!-- 4. Payment Handle Checkbox (Check In / Check Out) -->
                <td style="text-align:center;vertical-align:middle;padding:16px 18px;">
                  <label class="priv-toggle-box" style="display:inline-flex;align-items:center;gap:10px;cursor:pointer;padding:8px 14px;border-radius:10px;border:1.5px solid <?= $phChecked ? '#10B981' : '#E2E8F0' ?>;background:<?= $phChecked ? '#ECFDF5' : '#FFFFFF' ?>;transition:all 0.2s ease;">
                    <input type="checkbox" 
                           name="priv[<?= e($modKey) ?>][payment_handle]" 
                           value="1" 
                           data-role="payment_handle"
                           <?= $phChecked ? 'checked' : '' ?> 
                           onchange="updateToggleStyle(this)"
                           style="width:18px;height:18px;accent-color:#059669;cursor:pointer;">
                    <span class="priv-label-text" style="font-size:0.82rem;font-weight:700;color:<?= $phChecked ? '#047857' : '#64748B' ?>;">
                      <?= $phChecked ? 'Check In' : 'Check Out' ?>
                    </span>
                  </label>
                </td>

              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Bottom Action Footer -->
      <div style="padding:18px 24px;background:#F8FAFC;border-top:1px solid #E2E8F0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
        <div style="font-size:0.8rem;color:#64748B;">
          <strong>Tip:</strong> Checked In modules are visible in the staff member's navigation menu and granted clearance.
        </div>

        <div style="display:flex;gap:12px;align-items:center;">
          <button type="submit" name="save_privileges" value="1" class="admin-btn admin-btn-primary" style="font-weight:700;padding:11px 26px;font-size:0.92rem;display:inline-flex;align-items:center;gap:8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
            <span>Save Privilege Settings</span>
          </button>
        </div>
      </div>

    </div>
  </form>

  <!-- Reset Defaults Card -->
  <div style="margin-top:24px;background:#FFFFFF;border:1px dashed #CBD5E1;border-radius:10px;padding:16px 22px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;">
    <div>
      <div style="font-size:0.88rem;font-weight:700;color:#0F172A;">Reset Privilege Settings to Factory Defaults</div>
      <div style="font-size:0.78rem;color:#64748B;">Restores recommended role clearances (Travel Manager handles packages &amp; content; Payment Handler handles reservations).</div>
    </div>
    <form method="POST" action="<?= ADMIN_URL ?>/privileges.php" onsubmit="return confirm('Reset all privilege matrix clearances to factory defaults?');">
      <?= csrf_field() ?>
      <button type="submit" name="reset_defaults" value="1" class="admin-btn admin-btn-outline" style="font-size:0.82rem;font-weight:600;color:#EF4444;border-color:#FCA5A5;">
        Reset to Defaults
      </button>
    </form>
  </div>

</div>

<script>
function updateToggleStyle(chk) {
  const box = chk.closest('.priv-toggle-box');
  const label = box ? box.querySelector('.priv-label-text') : null;
  const isTravel = chk.getAttribute('data-role') === 'travel_manage';
  
  if (chk.checked) {
    if (box) {
      box.style.borderColor = isTravel ? '#3B82F6' : '#10B981';
      box.style.background = isTravel ? '#EFF6FF' : '#ECFDF5';
    }
    if (label) {
      label.textContent = 'Check In';
      label.style.color = isTravel ? '#1D4ED8' : '#047857';
    }
  } else {
    if (box) {
      box.style.borderColor = '#E2E8F0';
      box.style.background = '#FFFFFF';
    }
    if (label) {
      label.textContent = 'Check Out';
      label.style.color = '#64748B';
    }
  }
}

function toggleAllRole(roleName, shouldCheck) {
  const checkboxes = document.querySelectorAll(`input[type="checkbox"][data-role="${roleName}"]`);
  checkboxes.forEach(chk => {
    chk.checked = shouldCheck;
    updateToggleStyle(chk);
  });
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
