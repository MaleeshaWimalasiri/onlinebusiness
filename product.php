<?php
/* Product detail page - shows one product, its reviews and a review form. */
require_once __DIR__ . '/includes/functions.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

/* DML: SELECT - load the single product. */
$stmt = $pdo->prepare(
    'SELECT p.*, c.name AS category
       FROM products p
       LEFT JOIN categories c ON c.id = p.category_id
      WHERE p.id = ?'
);
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('Sorry, that product could not be found.', 'error');
    redirect('products.php');
}

/* Handle a new review submission (DML: INSERT). */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'review') {
    $name    = trim($_POST['name'] ?? '');
    $rating  = (int) ($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($name === '' || $comment === '' || $rating < 1 || $rating > 5) {
        set_flash('Please provide your name, a rating and a comment.', 'error');
    } else {
        $user = current_user();
        $ins  = $pdo->prepare(
            'INSERT INTO reviews (product_id, user_id, name, rating, comment)
             VALUES (?, ?, ?, ?, ?)'
        );
        $ins->execute([$id, $user['id'] ?? null, $name, $rating, $comment]);
        set_flash('Thank you! Your review has been posted.');
    }
    redirect('product.php?id=' . $id);
}

/* DML: SELECT - reviews for this product. */
$stmt = $pdo->prepare('SELECT * FROM reviews WHERE product_id = ? ORDER BY created_at DESC');
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();

/* DML: SELECT - average rating (aggregate). */
$stmt = $pdo->prepare('SELECT AVG(rating) AS avg_rating, COUNT(*) AS total FROM reviews WHERE product_id = ?');
$stmt->execute([$id]);
$ratingInfo = $stmt->fetch();
$avgRating  = round((float) ($ratingInfo['avg_rating'] ?? 0));

$pageTitle = $product['name'];
require __DIR__ . '/includes/header.php';
?>

<div class="container py-5">

    <?php show_flash(); ?>

    <nav class="small mb-3">
        <a href="products.php" class="text-gold">Shop</a> /
        <span class="text-muted"><?= e($product['name']) ?></span>
    </nav>

    <div class="row g-4">
        <div class="col-md-6">
            <img src="assets/images/<?= e($product['image']) ?>" class="detail-img"
                 alt="<?= e($product['name']) ?>">
        </div>
        <div class="col-md-6">
            <span class="cat-pill"><?= e($product['category'] ?? 'Jewelry') ?></span>
            <h1 class="mb-2"><?= e($product['name']) ?></h1>
            <div class="mb-2">
                <?= stars($avgRating) ?>
                <span class="small text-muted ms-1">(<?= (int) $ratingInfo['total'] ?> review(s))</span>
            </div>
            <p class="price-tag mb-3"><?= money($product['price']) ?></p>
            <p><?= nl2br(e($product['description'])) ?></p>

            <?php if ((int) $product['stock'] > 0): ?>
                <p class="small text-success">In stock (<?= (int) $product['stock'] ?> available)</p>
                <form method="post" action="cart.php" class="d-flex gap-2 align-items-center">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <input type="number" name="quantity" value="1" min="1" max="<?= (int) $product['stock'] ?>"
                           class="form-control" style="width:90px">
                    <button class="btn btn-gold" type="submit">Add to Cart</button>
                </form>
            <?php else: ?>
                <p class="badge bg-secondary">Currently sold out</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reviews -->
    <hr class="my-5">
    <div class="row">
        <div class="col-md-7">
            <h3 class="mb-3">Customer Reviews</h3>
            <?php if (empty($reviews)): ?>
                <p class="text-muted">No reviews yet. Be the first to review this product!</p>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="panel mb-3">
                        <div class="d-flex justify-content-between">
                            <strong><?= e($review['name']) ?></strong>
                            <small class="text-muted"><?= e(date('d M Y', strtotime($review['created_at']))) ?></small>
                        </div>
                        <div><?= stars($review['rating']) ?></div>
                        <p class="mb-0 mt-1"><?= nl2br(e($review['comment'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="col-md-5">
            <div class="panel">
                <h4 class="mb-3">Write a Review</h4>
                <form method="post" action="product.php?id=<?= $id ?>" class="needs-validation" novalidate>
                    <input type="hidden" name="action" value="review">
                    <input type="hidden" name="rating" value="<?= is_logged_in() ? 5 : 5 ?>">
                    <div class="mb-3">
                        <label class="form-label">Your Name</label>
                        <input type="text" name="name" class="form-control" required
                               value="<?= e(current_user()['name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block">Your Rating</label>
                        <div class="rating-picker" style="font-size:1.6rem; color:var(--gold); cursor:pointer;">
                            <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Your Review</label>
                        <textarea name="comment" class="form-control" rows="3" required></textarea>
                    </div>
                    <button class="btn btn-gold w-100" type="submit">Submit Review</button>
                </form>
            </div>
        </div>
    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
