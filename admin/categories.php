<?php
/* Admin - manage categories (full DML: INSERT, SELECT, UPDATE, DELETE). */
$adminPage = 'categories';
$pageTitle = 'Manage Categories';
require_once __DIR__ . '/../includes/functions.php';
if (!is_admin()) { redirect('../login.php'); }

$action = $_GET['action'] ?? 'list';
$images = ['placeholder.svg', 'rings.svg', 'necklaces.svg', 'earrings.svg', 'bracelets.svg'];

/* ----- Handle save (create or update) ------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id          = (int) ($_POST['id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') {
        set_flash('Please enter a category name.', 'error');
        redirect('categories.php');
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
        redirect('categories.php');
    } elseif ($upload !== null) {
        $image = $upload;
    }

    if ($id > 0) {
        /* DML: UPDATE - edit an existing category. */
        $stmt = $pdo->prepare('UPDATE categories SET name = ?, description = ?, image = ? WHERE id = ?');
        $stmt->execute([$name, $description, $image, $id]);
        set_flash('Category updated successfully.');
    } else {
        /* DML: INSERT - add a new category. */
        $stmt = $pdo->prepare('INSERT INTO categories (name, description, image) VALUES (?, ?, ?)');
        $stmt->execute([$name, $description, $image]);
        set_flash('Category added successfully.');
    }
    redirect('categories.php');
}

/* ----- Handle delete ------------------------------------------------ */
if ($action === 'delete' && isset($_GET['id'])) {
    /* DML: DELETE - remove a category. Products keep, their category
       is set to NULL by the foreign key rule. */
    $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
    $stmt->execute([(int) $_GET['id']]);
    set_flash('Category deleted. Affected products are now uncategorised.');
    redirect('categories.php');
}

/* ----- Load a category for the edit form ---------------------------- */
$editing = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([(int) $_GET['id']]);
    $editing = $stmt->fetch();
}

/* DML: SELECT - all categories with how many products use each one. */
$categories = $pdo->query(
    'SELECT c.*, COUNT(p.id) AS product_count
       FROM categories c
       LEFT JOIN products p ON p.category_id = c.id
   GROUP BY c.id
   ORDER BY c.name'
)->fetchAll();

require __DIR__ . '/includes/admin_header.php';
?>

<?php show_flash(); ?>

<div class="row g-4">
    <!-- Add / edit form -->
    <div class="col-xl-4">
        <div class="panel">
            <h5 class="mb-3"><?= $editing ? 'Edit Category' : 'Add New Category' ?></h5>
            <form method="post" action="categories.php" class="needs-validation"
                  enctype="multipart/form-data" novalidate>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
                <input type="hidden" name="current_image"
                       value="<?= e($editing['image'] ?? 'placeholder.svg') ?>">
                <div class="mb-3">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="name" class="form-control" required
                           value="<?= e($editing['name'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= e($editing['description'] ?? '') ?></textarea>
                </div>

                <!-- Category image: current preview + upload + built-in fallback -->
                <div class="mb-3">
                    <label class="form-label">Category Image</label>
                    <div class="text-center mb-2">
                        <img src="../assets/images/<?= e($editing['image'] ?? 'placeholder.svg') ?>"
                             alt="Current image"
                             style="width:110px;height:110px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">
                        <div class="small text-muted mt-1">Current</div>
                    </div>
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
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-gold" type="submit">
                        <?= $editing ? 'Update Category' : 'Add Category' ?>
                    </button>
                    <?php if ($editing): ?>
                        <a href="categories.php" class="btn btn-outline-gold">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Category list -->
    <div class="col-xl-8">
        <div class="panel">
            <h5 class="mb-3">All Categories</h5>
            <?php if (empty($categories)): ?>
                <p class="text-muted mb-0">No categories yet. Add one using the form.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr><th>ID</th><th>Image</th><th>Name</th><th>Description</th>
                                <th>Products</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($categories as $c): ?>
                            <tr>
                                <td><?= (int) $c['id'] ?></td>
                                <td><img src="../assets/images/<?= e($c['image'] ?? 'placeholder.svg') ?>" alt=""
                                         style="width:44px;height:44px;object-fit:cover;border-radius:6px;"></td>
                                <td><?= e($c['name']) ?></td>
                                <td class="small text-muted"><?= e($c['description'] ?: '-') ?></td>
                                <td><span class="badge bg-secondary"><?= (int) $c['product_count'] ?></span></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="categories.php?action=edit&id=<?= (int) $c['id'] ?>"
                                           class="btn btn-sm btn-outline-gold">Edit</a>
                                        <a href="categories.php?action=delete&id=<?= (int) $c['id'] ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           data-confirm="Delete this category? Products in it will become uncategorised.">Delete</a>
                                    </div>
                                </td>
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
