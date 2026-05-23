<?php
/* Home page - shows featured products and categories. */
require_once __DIR__ . '/includes/functions.php';

/* DML: SELECT - newest 4 products for the "Featured" section. */
$featured = $pdo->query(
    'SELECT p.*, c.name AS category
       FROM products p
       LEFT JOIN categories c ON c.id = p.category_id
   ORDER BY p.created_at DESC, p.id DESC
      LIMIT 4'
)->fetchAll();

/* DML: SELECT - all categories for the category showcase. */
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$pageTitle = 'Home';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container">
        <h1>Timeless Elegance, Crafted for You</h1>
        <p>Discover handcrafted fine jewelry that celebrates life's most precious moments. Ethically sourced, expertly finished.</p>
        <a href="products.php" class="btn btn-gold">Explore the Collection</a>
    </div>
</section>

<div class="container py-5">

    <?php show_flash(); ?>

    <!-- Categories -->
    <div class="section-title">
        <h2>Shop by Category</h2>
        <div class="line"></div>
    </div>
    <div class="row g-4 mb-5">
        <?php foreach ($categories as $cat): ?>
            <div class="col-6 col-md-3">
                <a href="products.php?category=<?= (int) $cat['id'] ?>" class="text-decoration-none">
                    <div class="feature-card text-center">
                        <img src="assets/images/<?= e($cat['image'] ?? 'placeholder.svg') ?>"
                             class="product-thumb" alt="<?= e($cat['name']) ?>">
                        <div class="card-body">
                            <h5 class="mb-0 text-gold"><?= e($cat['name']) ?></h5>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Featured products -->
    <div class="section-title">
        <h2>New Arrivals</h2>
        <div class="line"></div>
    </div>
    <div class="row g-4">
        <?php foreach ($featured as $product): ?>
            <div class="col-6 col-md-3">
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
                            <a href="product.php?id=<?= (int) $product['id'] ?>"
                               class="btn btn-sm btn-outline-gold">View</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-5">
        <a href="products.php" class="btn btn-outline-gold">View All Products</a>
    </div>

    <!-- Promise strip -->
    <div class="row text-center mt-5 pt-4 g-4">
        <div class="col-md-4">
            <h5 class="text-gold">&#10003; Ethically Sourced</h5>
            <p class="small">Every gemstone is responsibly and conflict-free sourced.</p>
        </div>
        <div class="col-md-4">
            <h5 class="text-gold">&#10003; Lifetime Warranty</h5>
            <p class="small">Free cleaning and maintenance on all purchases.</p>
        </div>
        <div class="col-md-4">
            <h5 class="text-gold">&#10003; Secure Delivery</h5>
            <p class="small">Insured island-wide delivery, beautifully packaged.</p>
        </div>
    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
