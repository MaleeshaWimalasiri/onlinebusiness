<?php
/* Checkout - collects delivery details and creates the order. */
require_once __DIR__ . '/includes/functions.php';

$cart = cart_detailed($pdo);

if (empty($cart['lines'])) {
    set_flash('Your cart is empty.', 'error');
    redirect('cart.php');
}

$user   = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '')    { $errors[] = 'Full name is required.'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'A valid email is required.'; }
    if ($phone === '')   { $errors[] = 'Phone number is required.'; }
    if ($address === '') { $errors[] = 'Delivery address is required.'; }

    if (empty($errors)) {
        /* Use a transaction so the order and its items stay consistent. */
        $pdo->beginTransaction();
        try {
            /* DML: INSERT - the order header. */
            $stmt = $pdo->prepare(
                'INSERT INTO orders (user_id, customer_name, email, phone, address, total, status)
                 VALUES (?, ?, ?, ?, ?, ?, "Pending")'
            );
            $stmt->execute([
                $user['id'] ?? null, $name, $email, $phone, $address, $cart['total'],
            ]);
            $orderId = (int) $pdo->lastInsertId();

            /* DML: INSERT order items + UPDATE product stock. */
            $itemStmt  = $pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, name, quantity, price)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stockStmt = $pdo->prepare(
                'UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?'
            );
            foreach ($cart['lines'] as $line) {
                $p = $line['product'];
                $itemStmt->execute([$orderId, $p['id'], $p['name'], $line['qty'], $p['price']]);
                $stockStmt->execute([$line['qty'], $p['id']]);
            }

            $pdo->commit();
            cart_clear();
            set_flash('Order #' . $orderId . ' placed successfully! We will contact you shortly.');
            redirect(is_logged_in() ? 'account.php' : 'index.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Something went wrong while placing your order. Please try again.';
        }
    }
}

$pageTitle = 'Checkout';
require __DIR__ . '/includes/header.php';
?>

<div class="container py-5">

    <div class="section-title">
        <h2>Checkout</h2>
        <div class="line"></div>
    </div>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger alert-permanent"><?= e($error) ?></div>
    <?php endforeach; ?>

    <div class="row g-4">
        <div class="col-md-7">
            <div class="panel">
                <h4 class="mb-3">Delivery Details</h4>
                <form method="post" action="checkout.php" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required
                               value="<?= e($_POST['name'] ?? $user['name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?= e($_POST['email'] ?? $user['email'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" required
                               value="<?= e($_POST['phone'] ?? $user['phone'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Delivery Address</label>
                        <textarea name="address" class="form-control" rows="3" required><?= e($_POST['address'] ?? $user['address'] ?? '') ?></textarea>
                    </div>
                    <p class="small text-muted">Payment is collected as cash on delivery.</p>
                    <button class="btn btn-gold w-100" type="submit">Place Order</button>
                </form>
            </div>
        </div>
        <div class="col-md-5">
            <div class="panel">
                <h4 class="mb-3">Order Summary</h4>
                <?php foreach ($cart['lines'] as $line): ?>
                    <div class="d-flex justify-content-between small mb-2">
                        <span><?= e($line['product']['name']) ?> &times; <?= (int) $line['qty'] ?></span>
                        <span><?= money($line['subtotal']) ?></span>
                    </div>
                <?php endforeach; ?>
                <hr>
                <div class="d-flex justify-content-between">
                    <strong>Total</strong>
                    <strong class="text-gold"><?= money($cart['total']) ?></strong>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
