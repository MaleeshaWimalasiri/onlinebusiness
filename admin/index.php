<?php
/* Admin dashboard - summary statistics. */
$adminPage = 'dashboard';
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/functions.php';
if (!is_admin()) { redirect('../login.php'); }

/* DML: SELECT - aggregate counts for the dashboard cards. */
$totalProducts = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalOrders   = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalUsers    = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$totalMessages = (int) $pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn();
$revenue       = (float) $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status <> 'Cancelled'")->fetchColumn();
$pendingOrders = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();

/* DML: SELECT - five most recent orders. */
$recentOrders = $pdo->query(
    'SELECT * FROM orders ORDER BY created_at DESC LIMIT 5'
)->fetchAll();

/* DML: SELECT - low stock products (stock below 8). */
$lowStock = $pdo->query(
    'SELECT * FROM products WHERE stock < 8 ORDER BY stock ASC'
)->fetchAll();

require __DIR__ . '/includes/admin_header.php';

$statusColors = [
    'Pending' => 'secondary', 'Processing' => 'info', 'Shipped' => 'primary',
    'Delivered' => 'success', 'Cancelled' => 'danger',
];
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2"><div class="stat-card"><div class="num"><?= $totalProducts ?></div><div class="lbl">Products</div></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="stat-card"><div class="num"><?= $totalOrders ?></div><div class="lbl">Orders</div></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="stat-card"><div class="num"><?= $totalUsers ?></div><div class="lbl">Customers</div></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="stat-card"><div class="num"><?= $totalMessages ?></div><div class="lbl">Messages</div></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="stat-card"><div class="num"><?= $pendingOrders ?></div><div class="lbl">Pending</div></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="stat-card"><div class="num" style="font-size:1.2rem"><?= money($revenue) ?></div><div class="lbl">Revenue</div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="panel">
            <h5 class="mb-3">Recent Orders</h5>
            <?php if (empty($recentOrders)): ?>
                <p class="text-muted small mb-0">No orders yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>#</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentOrders as $o): ?>
                            <tr>
                                <td><?= (int) $o['id'] ?></td>
                                <td><?= e($o['customer_name']) ?></td>
                                <td><?= money($o['total']) ?></td>
                                <td><span class="badge bg-<?= $statusColors[$o['status']] ?? 'secondary' ?>"><?= e($o['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="orders.php" class="small text-gold">View all orders &rarr;</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel">
            <h5 class="mb-3">Low Stock Alert</h5>
            <?php if (empty($lowStock)): ?>
                <p class="text-muted small mb-0">All products are well stocked.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Product</th><th>Stock</th></tr></thead>
                        <tbody>
                        <?php foreach ($lowStock as $p): ?>
                            <tr>
                                <td><?= e($p['name']) ?></td>
                                <td><span class="badge bg-<?= $p['stock'] == 0 ? 'danger' : 'warning text-dark' ?>"><?= (int) $p['stock'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
