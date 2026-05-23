<?php
/* Shop page - product listing with category filter and search. */
require_once __DIR__ . '/includes/functions.php';

$categoryId = isset($_GET['category']) ? (int) $_GET['category'] : 0;
$search     = trim($_GET['search'] ?? '');

/* Build the product query dynamically with bound parameters (DML: SELECT). */
$sql = 'SELECT p.*, c.name AS category
          FROM products p
          LEFT JOIN categories c ON c.id = p.category_id
         WHERE 1 = 1';
$params = [];

if ($categoryId > 0) {
    $sql .= ' AND p.category_id = ?';
    $params[] = $categoryId;
}
if ($search !== '') {
    $sql .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
$sql .= ' ORDER BY p.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

/* Categories for the filter bar. */
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$pageTitle = 'Shop';
require __DIR__ . '/includes/header.php';
?>

<div class="container py-5">

    <div class="section-title">
        <h2>Our Collection</h2>
        <div class="line"></div>
    </div>

    <?php show_flash(); ?>

    <!-- Search -->
    <form class="row justify-content-center mb-4" method="get" action="products.php">
        <div class="col-md-6 d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="Search jewelry..."
                   value="<?= e($search) ?>">
            <button class="btn btn-gold" type="submit">Search</button>
        </div>
    </form>

    <!-- Category filter -->
    <div class="text-center mb-4">
        <a href="products.php" class="btn btn-sm <?= $categoryId === 0 ? 'btn-gold' : 'btn-outline-gold' ?> m-1">All</a>
        <?php foreach ($categories as $cat): ?>
            <a href="products.php?category=<?= (int) $cat['id'] ?>"
               class="btn btn-sm <?= $categoryId === (int) $cat['id'] ? 'btn-gold' : 'btn-outline-gold' ?> m-1">
                <?= e($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <p class="text-center text-muted small"><?= count($products) ?> product(s) found</p>

    <?php if (empty($products)): ?>
        <div class="alert alert-warning text-center alert-permanent">
            No products match your search. <a href="products.php">View all products</a>.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card">
                        <a href="product.php?id=<?= (int) $product['id'] ?>">
                            <img src="assets/images/<?= e($product['image']) ?>" class="product-thumb"
                                 alt="<?= e($product['name']) ?>">
                        </a>
                        <div class="card-body">
                            <span class="cat-pill"><?= e($product['category'] ?? 'Jewelry') ?></span>
                            <a href="product.php?id=<?= (int) $product['id'] ?>" class="d-block product-name mb-1">
                                <?= e($product['name']) ?>
                            </a>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <span class="price"><?= money($product['price']) ?></span>
                                <?php if ((int) $product['stock'] > 0): ?>
                                    <form method="post" action="cart.php" class="m-0">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                        <button class="btn btn-sm btn-outline-gold" type="submit">Add</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Sold out</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
