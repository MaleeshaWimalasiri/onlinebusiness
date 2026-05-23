<?php
/* ---------------------------------------------------------------------
 *  Maheesha Jewels - Shared helper functions
 * ------------------------------------------------------------------- */

require_once __DIR__ . '/config.php';

/* Escape output to prevent XSS. */
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/* Format a number as a price. */
function money($amount)
{
    return CURRENCY . number_format((float) $amount, 2);
}

/* Redirect helper. */
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

/* Store a one-time flash message. */
function set_flash($message, $type = 'success')
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

/* Print and clear the flash message (if any). */
function show_flash()
{
    if (empty($_SESSION['flash'])) {
        return;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $class = $flash['type'] === 'error' ? 'alert-danger' : 'alert-success';
    echo '<div class="alert ' . $class . ' text-center mb-0">' . e($flash['message']) . '</div>';
}

/* ----- Authentication helpers --------------------------------------- */

function current_user()
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in()
{
    return current_user() !== null;
}

function is_admin()
{
    $user = current_user();
    return $user && $user['role'] === 'admin';
}

/* Block access to a customer page unless logged in. */
function require_login()
{
    if (!is_logged_in()) {
        set_flash('Please log in to continue.', 'error');
        redirect('login.php');
    }
}

/* Block access to an admin page unless logged in as admin. */
function require_admin()
{
    if (!is_admin()) {
        redirect('login.php');
    }
}

/* ----- Shopping cart helpers (session based) ------------------------ */

function cart_items()
{
    return $_SESSION['cart'] ?? [];
}

function cart_count()
{
    return array_sum(cart_items());
}

/* Add a product to the cart or increase its quantity. */
function cart_add($productId, $quantity = 1)
{
    $productId = (int) $productId;
    $quantity  = max(1, (int) $quantity);
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $quantity;
}

/* Set an exact quantity for a cart line (0 removes it). */
function cart_set($productId, $quantity)
{
    $productId = (int) $productId;
    $quantity  = (int) $quantity;
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

function cart_remove($productId)
{
    unset($_SESSION['cart'][(int) $productId]);
}

function cart_clear()
{
    $_SESSION['cart'] = [];
}

/* Load full product rows for everything currently in the cart. */
function cart_detailed(PDO $pdo)
{
    $cart = cart_items();
    if (empty($cart)) {
        return ['lines' => [], 'total' => 0.0];
    }
    $ids   = array_keys($cart);
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $stmt  = $pdo->prepare("SELECT * FROM products WHERE id IN ($marks)");
    $stmt->execute($ids);

    $lines = [];
    $total = 0.0;
    foreach ($stmt->fetchAll() as $product) {
        $qty      = (int) $cart[$product['id']];
        $subtotal = $qty * (float) $product['price'];
        $total   += $subtotal;
        $lines[]  = ['product' => $product, 'qty' => $qty, 'subtotal' => $subtotal];
    }
    return ['lines' => $lines, 'total' => $total];
}

/* ----- Image upload helper ------------------------------------------ */

/* Handle a product image uploaded through a file input.
 * Returns:
 *   - the stored path (relative to assets/images/) on success
 *   - a string starting with "ERR:" when validation fails
 *   - null when no file was submitted
 */
function save_uploaded_image($field)
{
    if (empty($_FILES[$field]['name']) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'ERR:Upload failed. The file may be larger than the server limit.';
    }
    if ($file['size'] > 3 * 1024 * 1024) {
        return 'ERR:Image must be 3 MB or smaller.';
    }

    /* Verify the file really is an image and get its true type. */
    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        return 'ERR:The uploaded file is not a valid image.';
    }
    $allowed = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_GIF  => 'gif',
        IMAGETYPE_WEBP => 'webp',
    ];
    if (!isset($allowed[$info[2]])) {
        return 'ERR:Only JPG, PNG, GIF and WEBP images are allowed.';
    }

    $dir = __DIR__ . '/../assets/images/uploads';
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        return 'ERR:Could not create the upload folder.';
    }

    $name = 'product_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$info[2]];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        return 'ERR:Could not save the uploaded image.';
    }
    return 'uploads/' . $name;
}

/* Render a row of star icons for a rating value. */
function stars($rating)
{
    $rating = (int) $rating;
    $out    = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= '<span class="star">' . ($i <= $rating ? '&#9733;' : '&#9734;') . '</span>';
    }
    return $out;
}
