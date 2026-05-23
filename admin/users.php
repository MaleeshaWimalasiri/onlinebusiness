<?php
/* Admin - manage registered users (SELECT, UPDATE role, DELETE). */
$adminPage = 'users';
$pageTitle = 'Manage Users';
require_once __DIR__ . '/../includes/functions.php';
if (!is_admin()) { redirect('../login.php'); }

$me = current_user();

/* ----- Change a user's role (DML: UPDATE) --------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'role') {
    $id   = (int) ($_POST['id'] ?? 0);
    $role = ($_POST['role'] ?? 'customer') === 'admin' ? 'admin' : 'customer';
    if ($id !== (int) $me['id']) {
        $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->execute([$role, $id]);
        set_flash('User role updated.');
    } else {
        set_flash('You cannot change your own role.', 'error');
    }
    redirect('users.php');
}

/* ----- Delete a user (DML: DELETE) ---------------------------------- */
if (($_GET['action'] ?? '') === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    if ($id === (int) $me['id']) {
        set_flash('You cannot delete your own account.', 'error');
    } else {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        set_flash('User deleted.');
    }
    redirect('users.php');
}

/* ----- Load users (DML: SELECT) ------------------------------------- */
$users = $pdo->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();

require __DIR__ . '/includes/admin_header.php';
?>

<?php show_flash(); ?>

<div class="panel">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th>
                    <th>Joined</th><th>Role</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= (int) $u['id'] ?></td>
                    <td><?= e($u['name']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['phone'] ?: '-') ?></td>
                    <td><small><?= e(date('d M Y', strtotime($u['created_at']))) ?></small></td>
                    <td>
                        <span class="badge bg-<?= $u['role'] === 'admin' ? 'dark' : 'secondary' ?>">
                            <?= e($u['role']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ((int) $u['id'] === (int) $me['id']): ?>
                            <span class="small text-muted">This is you</span>
                        <?php else: ?>
                            <form method="post" action="users.php" class="d-flex gap-1 mb-1">
                                <input type="hidden" name="action" value="role">
                                <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                <select name="role" class="form-select form-select-sm" style="width:110px">
                                    <option value="customer" <?= $u['role'] === 'customer' ? 'selected' : '' ?>>customer</option>
                                    <option value="admin"    <?= $u['role'] === 'admin'    ? 'selected' : '' ?>>admin</option>
                                </select>
                                <button class="btn btn-sm btn-gold" type="submit">Set</button>
                            </form>
                            <a href="users.php?action=delete&id=<?= (int) $u['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               data-confirm="Delete this user account?">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
