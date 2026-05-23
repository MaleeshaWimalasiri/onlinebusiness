<?php
/* Admin layout header - also enforces admin-only access. */
require_once __DIR__ . '/../../includes/functions.php';

if (!is_admin()) {
    set_flash('Please log in as an administrator.', 'error');
    redirect('../login.php');
}

$adminPage = $adminPage ?? '';
$pageTitle = $pageTitle ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= e(SITE_NAME) ?> Admin</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="admin-body">
<div class="container-fluid px-0">
    <!-- Mobile top bar (visible below the md breakpoint) -->
    <div class="admin-topbar d-md-none">
        <span class="brand-mb"><span class="brand-mark">&#10070;</span> Maheesha Admin</span>
        <button class="admin-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav"
                aria-label="Toggle navigation">&#9776;</button>
    </div>
    <div class="row g-0">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 admin-sidebar collapse d-md-block p-0" id="adminNav">
            <div class="brand d-none d-md-block">
                <span class="brand-mark">&#10070;</span> Maheesha
                <span class="brand-sub">Admin Panel</span>
            </div>
            <a href="index.php"    class="<?= $adminPage === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="products.php" class="<?= $adminPage === 'products'  ? 'active' : '' ?>">Products</a>
            <a href="categories.php" class="<?= $adminPage === 'categories' ? 'active' : '' ?>">Categories</a>
            <a href="orders.php"   class="<?= $adminPage === 'orders'    ? 'active' : '' ?>">Orders</a>
            <a href="messages.php" class="<?= $adminPage === 'messages'  ? 'active' : '' ?>">Messages</a>
            <a href="users.php"    class="<?= $adminPage === 'users'     ? 'active' : '' ?>">Users</a>
            <hr class="text-secondary mx-3">
            <a href="../index.php">View Site</a>
            <a href="../logout.php">Logout</a>
        </nav>
        <!-- Main content -->
        <main class="col-md-9 col-lg-10 py-4 px-3 px-md-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <h2 class="mb-0"><?= e($pageTitle) ?></h2>
                <span class="small text-muted">Signed in as <?= e(current_user()['name']) ?></span>
            </div>
