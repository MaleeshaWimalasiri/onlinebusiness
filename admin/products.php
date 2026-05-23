<?php
/* Admin - manage products (full DML: INSERT, SELECT, UPDATE, DELETE). */
$adminPage = 'products';
$pageTitle = 'Manage Products';
require_once __DIR__ . '/../includes/functions.php';
if (!is_admin()) { redirect('../login.php'); }

$action     = $_GET['action'] ?? 'list';
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$images     = ['placeholder.svg', 'rings.svg', 'necklaces.svg', 'earrings.svg', 'bracelets.svg'];

/* ----- Handle save (create or update) ------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id          = (int) ($_POST['id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $categoryId  = (int) ($_POST['category_id'] ?? 0) ?: null;
    $description = trim($_POST['description'] ?? '');
    $price       = (float) ($_POST['price'] ?? 0);
    $stock       = (int) ($_POST['stock'] ?? 0);

    if ($name === '' || $price <= 0) {
        set_flash('Please enter a product name and a valid price.', 'error');
        redirect('products.php');
    }

    /* Decide which image to use: keep current, pick a built-in one,
       or use a freshly uploaded photo (an upload always wins). */
    $image = $_POST['current_image'] ?? 'placeholder.svg';
    if (!empty($_POST['image'])) {
        $image = $_POST['image'];
    }
    $upload = save_uploaded_image('image_file');
    if (is_string($upload) && strncmp($upload, 'ERR:', 4) === 0) {
        set_flash(substr($upload, 4), 'error');
        redirect('products.php');
    } elseif ($upload !== null) {
        $image = $upload;
    }

    if ($id > 0) {
        /* DML: UPDATE - edit an existing product. */
        $stmt = $pdo->prepare(
            'UPDATE products
                SET name = ?, category_id = ?, description = ?, price = ?, stock = ?, image = ?
              WHERE id = ?'
        );
        $stmt->execute([$name, $categoryId, $description, $price, $stock, $image, $id]);
        set_flash('Product updated successfully.');
    } else {
        /* DML: INSERT - add a new product. */
        $stmt = $pdo->prepare(
            'INSERT INTO products (name, category_id, description, price, stock, image)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $categoryId, $description, $price, $stock, $image]);
        set_flash('Product added successfully.');
    }
    redirect('products.php');
}

/* ----- Handle delete ------------------------------------------------ */
if ($action === 'delete' && isset($_GET['id'])) {
    /* DML: DELETE - remove a product. */
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([(int) $_GET['id']]);
    set_flash('Product deleted.');
    redirect('products.php');
}

/* ----- Load a product for the edit form ----------------------------- */
$editing = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([(int) $_GET['id']]);
    $editing = $stmt->fetch();
}

require __DIR__ . '/includes/admin_header.php';
?>

<?php show_flash(); ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- Product add / edit form -->
    <div class="panel" style="max-width:700px">
        <h5 class="mb-3"><?= $editing ? 'Edit Product' : 'Add New Product' ?></h5>
        <form method="post" action="products.php" class="needs-validation"
              enctype="multipart/form-data" novalidate>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
            <input type="hidden" name="current_image"
                   value="<?= e($editing['image'] ?? 'placeholder.svg') ?>">
            <div class="mb-3">
                <label class="form-label">Product Name</label>
                <input type="text" name="name" class="form-control" required
                       value="<?= e($editing['name'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">-- None --</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"
                            <?= ($editing['category_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>>
                            <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Product image: current preview + upload + built-in fallback -->
            <div class="mb-3">
                <label class="form-label">Product Image</label>
                <div class="d-flex align-items-start gap-3 flex-wrap">
                    <div class="text-center">
                        <img src="../assets/images/<?= e($editing['image'] ?? 'placeholder.svg') ?>"
                             alt="Current image"
                             style="width:110px;height:110px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">
                        <div class="small text-muted mt-1">Current</div>
                    </div>
                    <div class="flex-grow-1" style="min-width:240px">
                        <label class="form-label small mb-1">Upload a new photo</label>
                        <input type="file" name="image_file" class="form-control"
                               accept="image/jpeg,image/png,image/gif,image/webp">
                        <div class="form-text">JPG, PNG, GIF or WEBP &middot; max 3 MB.</div>

                        <label class="form-label small mb-1 mt-2">or pick a built-in image</label>
                        <select name="image" class="form-select">
                            <option value="">-- Keep current / uploaded photo --</option>
                            <?php foreach ($images as $img): ?>
                                <option value="<?= e($img) ?>"><?= e($img) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Price (Rs.)</label>
                    <input type="number" name="price" step="0.01" min="0" class="form-control" required
                           value="<?= e($editing['price'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Stock Quantity</label>
                    <input type="number" name="stock" min="0" class="form-control" required
                           value="<?= e($editing['stock'] ?? 0) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4"><?= e($editing['description'] ?? '') ?></textarea>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-gold" type="submit">Save Product</button>
                <a href="products.php" class="btn btn-outline-gold">Cancel</a>
            </div>
        </form>
    </div>

<?php else: ?>
    <!-- Product list -->
    <div class="mb-3">
        <a href="products.php?action=add" class="btn btn-gold">+ Add New Product</a>
    </div>
    <div class="panel">
        <?php
        /* DML: SELECT - list all products with their category name. */
        $products = $pdo->query(
            'SELECT p.*, c.name AS category
               FROM products p
               LEFT JOIN categories c ON c.id = p.category_id
           ORDER BY p.id DESC'
        )->fetchAll();
        ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>ID</th><th>Image</th><th>Name</th><th>Category</th>
                        <th>Price</th><th>Stock</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= (int) $p['id'] ?></td>
                        <td><img src="../assets/images/<?= e($p['image']) ?>" alt=""
                                 style="width:44px;height:44px;object-fit:cover;border-radius:6px;"></td>
                        <td><?= e($p['name']) ?></td>
                        <td><?= e($p['category'] ?? '-') ?></td>
                        <td><?= money($p['price']) ?></td>
                        <td><?= (int) $p['stock'] ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="products.php?action=edit&id=<?= (int) $p['id'] ?>"
                                   class="btn btn-sm btn-outline-gold">Edit</a>
                                <a href="products.php?action=delete&id=<?= (int) $p['id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   data-confirm="Delete this product? This cannot be undone.">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
