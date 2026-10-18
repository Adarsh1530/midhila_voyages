<?php
/**
 * MIDHILA VOYAGES — Admin Staff & Users Management
 */

require_once __DIR__ . '/includes/admin-init.php';

$db = getDB();

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
    if (verify_csrf_token()) {
        $userId = (int)($_POST['user_id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = in_array($_POST['role'] ?? '', ['super_admin', 'manager', 'editor']) ? $_POST['role'] : 'editor';
        $password = trim($_POST['password'] ?? '');

        if (!empty($username) && !empty($fullName) && !empty($email)) {
            try {
                if ($userId > 0) {
                    if (!empty($password)) {
                        $hash = password_hash($password, PASSWORD_BCRYPT);
                        $stmt = $db->prepare("UPDATE `admins` SET `username` = ?, `full_name` = ?, `email` = ?, `role` = ?, `password_hash` = ? WHERE `id` = ?");
                        $stmt->execute([$username, $fullName, $email, $role, $hash, $userId]);
                    } else {
                        $stmt = $db->prepare("UPDATE `admins` SET `username` = ?, `full_name` = ?, `email` = ?, `role` = ? WHERE `id` = ?");
                        $stmt->execute([$username, $fullName, $email, $role, $userId]);
                    }
                    set_flash_message('success', "Staff account '{$username}' updated.");
                } else {
                    if (empty($password)) {
                        set_flash_message('error', 'Password is required for new accounts.');
                    } else {
                        $hash = password_hash($password, PASSWORD_BCRYPT);
                        $stmt = $db->prepare("INSERT INTO `admins` (`username`, `full_name`, `email`, `role`, `password_hash`) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$username, $fullName, $email, $role, $hash]);
                        set_flash_message('success', "New staff account '{$username}' created.");
                    }
                }
            } catch (Exception $e) {
                set_flash_message('error', 'Error: Username or email already in use.');
            }
        }
    }
    header('Location: ' . ADMIN_URL . '/users.php');
    exit;
}

// Handle Delete (Cannot delete self)
if (isset($_GET['delete_id'])) {
    $delId = (int)$_GET['delete_id'];
    if ($delId === (int)$_SESSION['admin_user_id']) {
        set_flash_message('error', 'You cannot delete your own active administrator account.');
    } else {
        $stmt = $db->prepare("DELETE FROM `admins` WHERE `id` = ?");
        $stmt->execute([$delId]);
        set_flash_message('success', 'User account deleted.');
    }
    header('Location: ' . ADMIN_URL . '/users.php');
    exit;
}

$users = $db->query("SELECT `id`, `username`, `full_name`, `email`, `role`, `last_login`, `is_active` FROM `admins` ORDER BY `id` ASC")->fetchAll();

$editUser = null;
if (isset($_GET['edit_id'])) {
    $stmt = $db->prepare("SELECT `id`, `username`, `full_name`, `email`, `role` FROM `admins` WHERE `id` = ? LIMIT 1");
    $stmt->execute([(int)$_GET['edit_id']]);
    $editUser = $stmt->fetch();
}

$adminTitle = 'Staff Accounts | MIDHILA VOYAGES';
$adminHeading = 'Staff & User Management';

require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="display:grid;grid-template-columns:1fr 1.6fr;gap:28px;">
  
  <!-- Add / Edit Staff Account -->
  <div class="admin-card">
    <h3 style="font-size:1.15rem;color:var(--admin-navy);margin-bottom:16px;border-bottom:1px solid var(--admin-border);padding-bottom:8px;">
      <?= $editUser ? 'Edit Staff Account' : 'Add New Staff Account' ?>
    </h3>

    <form method="POST" action="<?= ADMIN_URL ?>/users.php">
      <?= csrf_field() ?>
      <input type="hidden" name="save_user" value="1">
      <input type="hidden" name="user_id" value="<?= (int)($editUser['id'] ?? 0) ?>">

      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Full Name *</label>
        <input type="text" name="full_name" required placeholder="e.g. Maya Varma" 
               value="<?= e($editUser['full_name'] ?? '') ?>"
               style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
      </div>

      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Username *</label>
        <input type="text" name="username" required placeholder="e.g. mayavarma" 
               value="<?= e($editUser['username'] ?? '') ?>"
               style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
      </div>

      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Email Address *</label>
        <input type="email" name="email" required placeholder="maya@midhilavoyages.com" 
               value="<?= e($editUser['email'] ?? '') ?>"
               style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
      </div>

      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Role Level</label>
        <select name="role" style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;background:#FFF;">
          <option value="super_admin" <?= (($editUser['role'] ?? '') === 'super_admin') ? 'selected' : '' ?>>Super Administrator</option>
          <option value="manager" <?= (($editUser['role'] ?? '') === 'manager') ? 'selected' : '' ?>>Travel Manager</option>
          <option value="editor" <?= (($editUser['role'] ?? '') === 'editor') ? 'selected' : '' ?>>Content Editor</option>
        </select>
      </div>

      <div style="margin-bottom:20px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">
          Password <?= $editUser ? '<span style="font-weight:400;color:var(--admin-muted);">(Leave blank to keep current)</span>' : '*' ?>
        </label>
        <input type="password" name="password" <?= $editUser ? '' : 'required' ?> placeholder="••••••••" 
               style="width:100%;padding:9px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
      </div>

      <div style="display:flex;gap:10px;">
        <button type="submit" class="admin-btn admin-btn-primary">
          <span><?= $editUser ? 'Update Account' : 'Create Account' ?></span>
        </button>
        <?php if ($editUser): ?>
          <a href="<?= ADMIN_URL ?>/users.php" class="admin-btn admin-btn-outline">Cancel</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Users Table -->
  <div class="admin-card" style="padding:0;overflow:hidden;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Name &amp; User</th>
          <th>Role</th>
          <th>Email</th>
          <th>Last Login</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td>
              <strong><?= e($u['full_name']) ?></strong>
              <div style="font-size:0.75rem;color:var(--admin-muted);">@<?= e($u['username']) ?></div>
            </td>
            <td>
              <span class="badge badge-neutral"><?= strtoupper(str_replace('_', ' ', e($u['role']))) ?></span>
            </td>
            <td><?= e($u['email']) ?></td>
            <td style="font-size:0.8rem;color:var(--admin-muted);">
              <?= $u['last_login'] ? date('d M Y, h:i A', strtotime($u['last_login'])) : 'Never' ?>
            </td>
            <td style="text-align:right;">
              <div style="display:inline-flex;gap:6px;">
                <a href="<?= ADMIN_URL ?>/users.php?edit_id=<?= (int)$u['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm">Edit</a>
                <?php if ($u['id'] != $_SESSION['admin_user_id']): ?>
                  <a href="<?= ADMIN_URL ?>/users.php?delete_id=<?= (int)$u['id'] ?>" 
                     data-confirm-delete="Are you sure you want to delete staff account '@<?= e($u['username']) ?>'?"
                     class="admin-btn admin-btn-danger admin-btn-sm">Delete</a>
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
