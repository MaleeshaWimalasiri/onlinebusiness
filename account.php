<?php
/* Customer account page - profile and order history. */
require_once __DIR__ . '/includes/functions.php';
require_login();

$user = current_user();

/* DML: SELECT - this customer's orders. */
$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

/* DML: SELECT - the items for each order, grouped by order id. */
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

$pageTitle = 'My Account';
require __DIR__ . '/includes/header.php';
?>

<div class="container py-5">

    <div class="section-title">
        <h2>My Account</h2>
        <div class="line"></div>
    </div>

    <?php show_flash(); ?>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="panel">
                <h4 class="mb-3">Profile</h4>
                <p class="mb-1"><strong>Name:</strong> <?= e($user['name']) ?></p>
                <p class="mb-1"><strong>Email:</strong> <?= e($user['email']) ?></p>
                <p class="mb-1"><strong>Phone:</strong> <?= e($user['phone'] ?: '-') ?></p>
                <p class="mb-0"><strong>Address:</strong> <?= e($user['address'] ?: '-') ?></p>
            </div>
        </div>
        <div class="col-md-8">
            <div class="panel">
                <h4 class="mb-3">Order History</h4>
                <?php if (empty($orders)): ?>
                    <p class="text-muted">You have not placed any orders yet.</p>
                    <a href="products.php" class="btn btn-gold">Start Shopping</a>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between flex-wrap">
                                <strong>Order #<?= (int) $order['id'] ?></strong>
                                <span class="badge bg-<?= $statusColors[$order['status']] ?? 'secondary' ?> badge-status">
                                    <?= e($order['status']) ?>
                                </span>
                            </div>
                            <small class="text-muted">
                                Placed on <?= e(date('d M Y', strtotime($order['created_at']))) ?>
                            </small>
                            <ul class="small mt-2 mb-1">
                                <?php foreach ($items[$order['id']] ?? [] as $item): ?>
                                    <li><?= e($item['name']) ?> &times; <?= (int) $item['quantity'] ?>
                                        — <?= money($item['price']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="text-end">
                                <strong>Total: <span class="text-gold"><?= money($order['total']) ?></span></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
