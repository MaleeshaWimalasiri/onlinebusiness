<?php
/* Admin - manage orders (SELECT, UPDATE status, DELETE). */
$adminPage = 'orders';
$pageTitle = 'Manage Orders';
require_once __DIR__ . '/../includes/functions.php';
if (!is_admin()) { redirect('../login.php'); }

$statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];

/* ----- Update an order's status (DML: UPDATE) ----------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'status') {
    $id     = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? 'Pending';
    if (in_array($status, $statuses, true)) {
        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        set_flash('Order #' . $id . ' status updated to ' . $status . '.');
    }
    redirect('orders.php');
}

/* ----- Delete an order (DML: DELETE) -------------------------------- */
if (($_GET['action'] ?? '') === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('DELETE FROM orders WHERE id = ?');
    $stmt->execute([(int) $_GET['id']]);
    set_flash('Order deleted.');
    redirect('orders.php');
}

/* ----- Load orders (DML: SELECT) ------------------------------------ */
$orders = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC')->fetchAll();

$items = [];
if ($orders) {
    $ids   = array_column($orders, 'id');
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $stmt  = $pdo->prepare("SELECT * FROM order_items WHERE order_id IN ($marks)");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $row) {
        $items[$row['order_id']][] = $row;
    }
}

$statusColors = [
    'Pending' => 'secondary', 'Processing' => 'info', 'Shipped' => 'primary',
    'Delivered' => 'success', 'Cancelled' => 'danger',
];

require __DIR__ . '/includes/admin_header.php';
?>

<?php show_flash(); ?>

<div class="panel">
    <?php if (empty($orders)): ?>
        <p class="text-muted mb-0">No orders have been placed yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>#</th><th>Customer</th><th>Items</th><th>Total</th>
                        <th>Date</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td><?= (int) $o['id'] ?></td>
                        <td>
                            <strong><?= e($o['customer_name']) ?></strong><br>
                            <small class="text-muted"><?= e($o['email']) ?><br><?= e($o['phone']) ?></small><br>
                            <small class="text-muted"><?= e($o['address']) ?></small>
                        </td>
                        <td>
                            <ul class="small mb-0 ps-3">
                                <?php foreach ($items[$o['id']] ?? [] as $it): ?>
                                    <li><?= e($it['name']) ?> &times; <?= (int) $it['quantity'] ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                        <td><?= money($o['total']) ?></td>
                        <td><small><?= e(date('d M Y', strtotime($o['created_at']))) ?></small></td>
                        <td>
                            <span class="badge bg-<?= $statusColors[$o['status']] ?? 'secondary' ?>">
                                <?= e($o['status']) ?>
                            </span>
                        </td>
                        <td>
                            <form method="post" action="orders.php" class="d-flex gap-1 mb-1">
                                <input type="hidden" name="action" value="status">
                                <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                                <select name="status" class="form-select form-select-sm" style="width:120px">
                                    <?php foreach ($statuses as $s): ?>
                                        <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>>
                                            <?= $s ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-gold" type="submit">Set</button>
                            </form>
                            <a href="orders.php?action=delete&id=<?= (int) $o['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               data-confirm="Delete this order permanently?">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
