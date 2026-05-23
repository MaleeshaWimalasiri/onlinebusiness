<?php
/* Login for customers and admins. */
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect(is_admin() ? 'admin/index.php' : 'account.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    /* DML: SELECT - look up the user by email. */
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id'      => $user['id'],
            'name'    => $user['name'],
            'email'   => $user['email'],
            'phone'   => $user['phone'],
            'address' => $user['address'],
            'role'    => $user['role'],
        ];
        set_flash('Welcome back, ' . $user['name'] . '!');
        redirect($user['role'] === 'admin' ? 'admin/index.php' : 'account.php');
    } else {
        $error = 'Invalid email or password.';
    }
}

$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="panel">
                <h3 class="text-center mb-4">Login</h3>

                <?php show_flash(); ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-permanent"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="post" action="login.php" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?= e($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-gold w-100" type="submit">Login</button>
                </form>
                <p class="text-center small mt-3 mb-0">
                    New here? <a href="register.php" class="text-gold">Create an account</a>
                </p>
                <hr>
                <p class="small text-muted mb-0 text-center">
                    Demo admin: admin@maheeshajewels.com / admin123<br>
                    Demo customer: sara@example.com / password123
                </p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
