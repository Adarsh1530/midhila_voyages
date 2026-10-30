<?php
/**
 * MIDHILA VOYAGES — User Management System
 * Role-Based Access Control: Super Admin, Travel Manage, and Payment Handle
 */

require_once __DIR__ . '/includes/admin-init.php';

// Strictly restricted to Super Admin only
require_role(['super_admin']);

$db = getDB();
$roles = get_all_roles();

// Handle Form Submission: Create or Update User Account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
    if (verify_csrf_token()) {
        $userId   = (int)($_POST['user_id'] ?? 0);
        $fullName = trim($_POST['full_name'] ?? '');
        $username = strtolower(trim($_POST['username'] ?? ''));
        $username = preg_replace('/[^a-z0-9_.-]/', '', $username);
        $role     = trim($_POST['role'] ?? 'travel_manage');
        $email    = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validate allowed roles
        if (!array_key_exists($role, $roles)) {
            $role = 'travel_manage';
        }

        // Auto-generate internal fallback email if left empty
        if (empty($email)) {
            $email = $username . '@midhilavoyages.com';
        }

        if (empty($username) || empty($fullName)) {
            set_flash_message('error', 'Full Name and Username are mandatory.');
        } else {
            try {
                // Check if username already exists for another user
                $checkStmt = $db->prepare("SELECT `id` FROM `admins` WHERE `username` = ? AND `id` != ? LIMIT 1");
                $checkStmt->execute([$username, $userId]);
                if ($checkStmt->fetch()) {
                    set_flash_message('error', "Username '{$username}' is already taken by another user account.");
                    header('Location: ' . ADMIN_URL . '/users.php' . ($userId > 0 ? "?edit_id={$userId}" : ''));
                    exit;
                }

                // Check if email already exists for another user
                $checkEmail = $db->prepare("SELECT `id` FROM `admins` WHERE `email` = ? AND `id` != ? LIMIT 1");
                $checkEmail->execute([$email, $userId]);
                if ($checkEmail->fetch()) {
                    // Make unique if auto-generated
                    $email = $username . '_' . time() . '@midhilavoyages.com';
                }

                if ($userId > 0) {
                    // Update Existing User
                    if (!empty($password)) {
                        $hash = password_hash($password, PASSWORD_BCRYPT);
                        $stmt = $db->prepare("UPDATE `admins` SET `username` = ?, `full_name` = ?, `email` = ?, `role` = ?, `password_hash` = ? WHERE `id` = ?");
                        $stmt->execute([$username, $fullName, $email, $role, $hash, $userId]);
                    } else {
                        $stmt = $db->prepare("UPDATE `admins` SET `username` = ?, `full_name` = ?, `email` = ?, `role` = ? WHERE `id` = ?");
                        $stmt->execute([$username, $fullName, $email, $role, $userId]);
                    }

                    // Update session if editing self
                    if ($userId === (int)$_SESSION['admin_user_id']) {
                        $_SESSION['admin_username']  = $username;
                        $_SESSION['admin_full_name'] = $fullName;
                        $_SESSION['admin_role']      = $role;
                    }

                    log_activity($_SESSION['admin_user_id'], 'user_updated', "Updated user account '@{$username}' with role '{$role}'.");
                    set_flash_message('success', "User account '{$fullName}' (@{$username}) updated successfully.");
                } else {
                    // Create New User
                    if (empty($password)) {
                        set_flash_message('error', 'Password is required to create a new user account.');
                        header('Location: ' . ADMIN_URL . '/users.php');
                        exit;
                    }

                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("INSERT INTO `admins` (`username`, `full_name`, `email`, `role`, `password_hash`, `is_active`) VALUES (?, ?, ?, ?, ?, 1)");
                    $stmt->execute([$username, $fullName, $email, $role, $hash]);

                    log_activity($_SESSION['admin_user_id'], 'user_created', "Created new user login '@{$username}' with role '{$role}'.");
                    set_flash_message('success', "New user account '{$fullName}' (@{$username}) created. They can now log in using their Username and Password.");
                }
            } catch (Exception $e) {
                error_log("User Management Error: " . $e->getMessage());
                set_flash_message('error', 'Database error: ' . $e->getMessage());
            }
        }
    }
    header('Location: ' . ADMIN_URL . '/users.php');
    exit;
}

// Handle Delete User
if (isset($_GET['delete_id'])) {
    $delId = (int)$_GET['delete_id'];
    if ($delId === (int)$_SESSION['admin_user_id']) {
        set_flash_message('error', 'Action rejected: You cannot delete your own active administrator login.');
    } else {
        // Prevent deleting the only remaining super_admin
        $userStmt = $db->prepare("SELECT `username`, `role` FROM `admins` WHERE `id` = ? LIMIT 1");
        $userStmt->execute([$delId]);
        $targetUser = $userStmt->fetch();

        if ($targetUser) {
            if ($targetUser['role'] === 'super_admin') {
                $superCount = (int)$db->query("SELECT COUNT(*) FROM `admins` WHERE `role` = 'super_admin'")->fetchColumn();
                if ($superCount <= 1) {
                    set_flash_message('error', 'Action rejected: You cannot delete the last remaining Super Administrator account.');
                    header('Location: ' . ADMIN_URL . '/users.php');
                    exit;
                }
            }

            $stmt = $db->prepare("DELETE FROM `admins` WHERE `id` = ?");
            $stmt->execute([$delId]);
            log_activity($_SESSION['admin_user_id'], 'user_deleted', "Deleted user account '@{$targetUser['username']}'.");
            set_flash_message('success', "User account '@{$targetUser['username']}' deleted permanently.");
        }
    }
    header('Location: ' . ADMIN_URL . '/users.php');
    exit;
}

// Fetch all active users
$users = $db->query("SELECT `id`, `username`, `full_name`, `email`, `role`, `last_login`, `is_active` FROM `admins` ORDER BY `id` ASC")->fetchAll();

// Role counts
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

// Check if Editing a User
$editUser = null;
if (isset($_GET['edit_id'])) {
    $stmt = $db->prepare("SELECT `id`, `username`, `full_name`, `email`, `role` FROM `admins` WHERE `id` = ? LIMIT 1");
    $stmt->execute([(int)$_GET['edit_id']]);
    $editUser = $stmt->fetch();
}

$adminTitle = 'User Management System | MIDHILA VOYAGES';
$adminHeading = 'User Management System';

require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- 1. SPLIT ROLE CLARITY CARDS -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px;margin-bottom:28px;">
  
  <!-- Role 1: Super Admin -->
  <div class="admin-card" style="padding:18px 20px;margin-bottom:0;border-left:4px solid #0F172A;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
      <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748B;letter-spacing:0.06em;">Primary Role</span>
      <span style="background:#0F172A;color:#FFFFFF;padding:3px 9px;border-radius:6px;font-size:0.72rem;font-weight:700;">SUPER ADMIN</span>
    </div>
    <div style="font-size:1.1rem;font-weight:700;color:#0F172A;margin-bottom:4px;">Full System Control</div>
    <p style="font-size:0.8rem;color:#64748B;line-height:1.45;margin-bottom:10px;">
      Unrestricted clearance to all modules: Payments Audit, Tour Catalog, Reservations, Offer Popup, and User Management.
    </p>
    <div style="font-size:0.78rem;font-weight:600;color:#0F172A;">
      Active Logins: <strong><?= $roleCounts['super_admin'] ?></strong>
    </div>
  </div>

  <!-- Role 2: Travel Manage -->
  <div class="admin-card" style="padding:18px 20px;margin-bottom:0;border-left:4px solid #2563EB;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
      <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748B;letter-spacing:0.06em;">Split Role</span>
      <span style="background:#EFF6FF;color:#1D4ED8;padding:3px 9px;border-radius:6px;font-size:0.72rem;font-weight:700;border:1px solid #BFDBFE;">TRAVEL MANAGE</span>
    </div>
    <div style="font-size:1.1rem;font-weight:700;color:#0F172A;margin-bottom:4px;">Tours &amp; Reservations</div>
    <p style="font-size:0.8rem;color:#64748B;line-height:1.45;margin-bottom:10px;">
      Manages Tour Packages, Destinations, Categories, Gallery, Offer Popup, Inquiries, and Bookings. (Restricted from Payments).
    </p>
    <div style="font-size:0.78rem;font-weight:600;color:#1D4ED8;">
      Active Logins: <strong><?= $roleCounts['travel_manage'] ?></strong>
    </div>
  </div>

  <!-- Role 3: Payment Handle -->
  <div class="admin-card" style="padding:18px 20px;margin-bottom:0;border-left:4px solid #059669;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
      <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748B;letter-spacing:0.06em;">Split Role</span>
      <span style="background:#ECFDF5;color:#047857;padding:3px 9px;border-radius:6px;font-size:0.72rem;font-weight:700;border:1px solid #A7F3D0;">PAYMENT HANDLE</span>
    </div>
    <div style="font-size:1.1rem;font-weight:700;color:#0F172A;margin-bottom:4px;">Financial &amp; Payment Audit</div>
    <p style="font-size:0.8rem;color:#64748B;line-height:1.45;margin-bottom:10px;">
      Audits Razorpay gateway transactions, captured revenue, reconciliation, and customer booking payment records. (Restricted from Catalog).
    </p>
    <div style="font-size:0.78rem;font-weight:600;color:#047857;">
      Active Logins: <strong><?= $roleCounts['payment_handle'] ?></strong>
    </div>
  </div>

</div>

<!-- 2. MAIN USER MANAGEMENT WORKSPACE -->
<div style="display:grid;grid-template-columns:minmax(320px, 1fr) 1.8fr;gap:28px;align-items:start;">
  
  <!-- Add / Edit User Form Card -->
  <div class="admin-card" style="margin-bottom:0;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
    <div style="border-bottom:1px solid #E2E8F0;padding-bottom:14px;margin-bottom:18px;">
      <h3 style="font-size:1.15rem;color:#0F172A;font-weight:700;margin-bottom:4px;">
        <?= $editUser ? 'Edit User Credentials & Role' : 'Add New User Login' ?>
      </h3>
      <p style="font-size:0.82rem;color:#64748B;margin:0;">
        Users sign into the portal using their <strong>Username</strong> and <strong>Password</strong>.
      </p>
    </div>

    <form method="POST" action="<?= ADMIN_URL ?>/users.php">
      <?= csrf_field() ?>
      <input type="hidden" name="save_user" value="1">
      <input type="hidden" name="user_id" value="<?= (int)($editUser['id'] ?? 0) ?>">

      <div class="admin-form-group">
        <label for="fullNameInput">Full Name *</label>
        <input type="text" id="fullNameInput" name="full_name" required placeholder="e.g. Travel Operations Lead" 
               value="<?= e($editUser['full_name'] ?? '') ?>">
      </div>

      <div class="admin-form-group">
        <label for="usernameInput">Login Username *</label>
        <input type="text" id="usernameInput" name="username" required placeholder="e.g. travel_manager" 
               value="<?= e($editUser['username'] ?? '') ?>"
               autocomplete="off"
               pattern="[A-Za-z0-9_.-]+"
               title="Only letters, numbers, underscores, dashes, and periods allowed.">
        <div style="font-size:0.75rem;color:#64748B;margin-top:4px;">This username is entered on the login screen.</div>
      </div>

      <div class="admin-form-group">
        <label for="roleSelect">Split Access Role *</label>
        <select id="roleSelect" name="role" style="font-weight:600;">
          <?php foreach ($roles as $rKey => $rMeta): ?>
            <option value="<?= e($rKey) ?>" <?= (($editUser['role'] ?? 'travel_manage') === $rKey) ? 'selected' : '' ?>>
              <?= e($rMeta['label']) ?> — (<?= e($rMeta['tag']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <div style="font-size:0.75rem;color:#64748B;margin-top:4px;">Determines which sections and privileges the user receives upon login.</div>
      </div>

      <div class="admin-form-group">
        <label for="passwordInput">
          Login Password <?= $editUser ? '<span style="font-weight:400;color:#64748B;">(Leave blank to keep current)</span>' : '*' ?>
        </label>
        <input type="password" id="passwordInput" name="password" <?= $editUser ? '' : 'required' ?> placeholder="••••••••" autocomplete="new-password">
        <div style="font-size:0.75rem;color:#64748B;margin-top:4px;">Used with the username above to sign in.</div>
      </div>

      <div class="admin-form-group">
        <label for="emailInput">Email Address <span style="font-weight:400;color:#64748B;">(Optional / Notifications)</span></label>
        <input type="email" id="emailInput" name="email" placeholder="user@midhilavoyages.com" 
               value="<?= e($editUser['email'] ?? '') ?>">
      </div>

      <div style="display:flex;gap:10px;margin-top:24px;border-top:1px solid #F1F5F9;padding-top:16px;">
        <button type="submit" class="admin-btn admin-btn-primary" style="flex-grow:1;justify-content:center;">
          <span><?= $editUser ? 'Update User Credentials' : 'Create User Account' ?></span>
        </button>
        <?php if ($editUser): ?>
          <a href="<?= ADMIN_URL ?>/users.php" class="admin-btn admin-btn-outline">Cancel</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Users Table Card -->
  <div class="admin-card" style="padding:0;overflow:hidden;margin-bottom:0;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
    <div style="padding:18px 22px;border-bottom:1px solid #E2E8F0;display:flex;justify-content:space-between;align-items:center;background:#FAFAFA;">
      <div>
        <h4 style="font-size:1rem;color:#0F172A;font-weight:700;margin:0;">Authorized User Accounts &amp; Logins</h4>
        <div style="font-size:0.78rem;color:#64748B;margin-top:2px;">All users authorized to access Midhila Voyages console</div>
      </div>
      <div style="font-size:0.8rem;color:#475569;font-weight:600;">
        Total Users: <?= count($users) ?>
      </div>
    </div>

    <table class="admin-table">
      <thead>
        <tr>
          <th>User &amp; Login</th>
          <th>Access Role</th>
          <th>Allowed Scope</th>
          <th>Last Login</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <?php 
            $uRole = $u['role'];
            if ($uRole === 'manager' || $uRole === 'editor') {
                $uRole = 'travel_manage';
            }
            $meta = $roles[$uRole] ?? $roles['travel_manage'];
            $isSelf = ((int)$u['id'] === (int)$_SESSION['admin_user_id']);
          ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:34px;height:34px;border-radius:50%;background:#F1F5F9;color:#0F172A;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.82rem;border:1px solid #CBD5E1;">
                  <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                </div>
                <div>
                  <strong style="color:#0F172A;font-size:0.9rem;"><?= e($u['full_name']) ?></strong>
                  <?php if ($isSelf): ?>
                    <span style="background:#E2E8F0;color:#0F172A;font-size:0.68rem;padding:2px 6px;border-radius:4px;font-weight:700;margin-left:4px;">YOU</span>
                  <?php endif; ?>
                  <div style="font-size:0.78rem;color:#2563EB;font-weight:600;">@<?= e($u['username']) ?></div>
                </div>
              </div>
            </td>
            <td>
              <span style="background:<?= $meta['badge_bg'] ?>;color:<?= $meta['badge_fg'] ?>;padding:3px 8px;border-radius:6px;font-size:0.74rem;font-weight:700;display:inline-block;letter-spacing:0.02em;">
                <?= e($meta['badge']) ?>
              </span>
            </td>
            <td>
              <span style="font-size:0.78rem;color:#475569;line-height:1.3;display:block;">
                <?= e($meta['tag']) ?>
              </span>
            </td>
            <td style="font-size:0.78rem;color:#64748B;white-space:nowrap;">
              <?= $u['last_login'] ? date('d M Y, h:i A', strtotime($u['last_login'])) : '<span style="color:#94A3B8;">Never logged in</span>' ?>
            </td>
            <td style="text-align:right;white-space:nowrap;">
              <div style="display:inline-flex;gap:6px;">
                <a href="<?= ADMIN_URL ?>/users.php?edit_id=<?= (int)$u['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm" style="padding:5px 10px;font-size:0.78rem;">Edit</a>
                <?php if (!$isSelf): ?>
                  <a href="<?= ADMIN_URL ?>/users.php?delete_id=<?= (int)$u['id'] ?>" 
                     data-confirm-delete="Are you sure you want to permanently delete user account '@<?= e($u['username']) ?>' (<?= e($u['full_name']) ?>)?"
                     class="admin-btn admin-btn-danger admin-btn-sm" style="padding:5px 10px;font-size:0.78rem;">Delete</a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
