<?php
/* Shopping cart - add / update / remove items (session based). */
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        cart_add($_POST['product_id'] ?? 0, $_POST['quantity'] ?? 1);
        set_flash('Item added to your cart.');
        redirect('cart.php');
    }
    if ($action === 'update') {
        cart_set($_POST['product_id'] ?? 0, $_POST['quantity'] ?? 0);
        redirect('cart.php');
    }
    if ($action === 'remove') {
        cart_remove($_POST['product_id'] ?? 0);
        set_flash('Item removed from your cart.');
        redirect('cart.php');
    }
    if ($action === 'clear') {
        cart_clear();
        set_flash('Your cart has been cleared.');
        redirect('cart.php');
    }
}

$cart = cart_detailed($pdo);

$pageTitle = 'Shopping Cart';
require __DIR__ . '/includes/header.php';
?>

<div class="container py-5">

    <div class="section-title">
        <h2>Your Shopping Cart</h2>
        <div class="line"></div>
    </div>

    <?php show_flash(); ?>

    <?php if (empty($cart['lines'])): ?>
        <div class="text-center py-5">
            <p class="text-muted">Your cart is empty.</p>
            <a href="products.php" class="btn btn-gold">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="panel">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Product</th><th>Price</th><th>Quantity</th>
                            <th>Subtotal</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart['lines'] as $line): ?>
                            <?php $p = $line['product']; ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="assets/images/<?= e($p['image']) ?>" alt=""
                                             style="width:56px;height:56px;object-fit:cover;border-radius:6px;">
                                        <a href="product.php?id=<?= (int) $p['id'] ?>" class="text-ink">
                                            <?= e($p['name']) ?>
                                        </a>
                                    </div>
                                </td>
                                <td><?= money($p['price']) ?></td>
                                <td>
                                    <form method="post" action="cart.php" class="m-0">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                                        <input type="number" name="quantity" value="<?= (int) $line['qty'] ?>"
                                               min="1" class="form-control qty-input" style="width:80px">
                                    </form>
                                </td>
                                <td><?= money($line['subtotal']) ?></td>
                                <td>
                                    <form method="post" action="cart.php" class="m-0">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">&times;</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-3">
                <form method="post" action="cart.php" class="m-0">
                    <input type="hidden" name="action" value="clear">
                    <button class="btn btn-sm btn-outline-danger" type="submit">Clear Cart</button>
                </form>
                <div>
                    <h4 class="mb-2 text-end">Total: <span class="text-gold"><?= money($cart['total']) ?></span></h4>
                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        <a href="products.php" class="btn btn-outline-gold">Continue Shopping</a>
                        <a href="checkout.php" class="btn btn-gold">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
