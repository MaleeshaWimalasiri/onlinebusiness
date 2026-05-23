<?php
/* Customer registration. */
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('account.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if ($name === '')  { $errors[] = 'Please enter your name.'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Please enter a valid email.'; }
    if (strlen($password) < 6) { $errors[] = 'Password must be at least 6 characters.'; }
    if ($password !== $confirm) { $errors[] = 'Passwords do not match.'; }

    /* DML: SELECT - make sure the email is not already registered. */
    if (empty($errors)) {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = 'An account with that email already exists.';
        }
    }

    if (empty($errors)) {
        /* DML: INSERT - create the new customer account. */
        $stmt = $pdo->prepare(
            'INSERT INTO users (name, email, password, phone, address, role)
             VALUES (?, ?, ?, ?, ?, "customer")'
        );
        $stmt->execute([
            $name, $email, password_hash($password, PASSWORD_DEFAULT), $phone, $address,
        ]);
        set_flash('Account created successfully. Please log in.');
        redirect('login.php');
    }
}

$pageTitle = 'Register';
require __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="panel">
                <h3 class="text-center mb-4">Create an Account</h3>

                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-danger alert-permanent"><?= e($error) ?></div>
                <?php endforeach; ?>

                <form method="post" action="register.php" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required
                               value="<?= e($_POST['name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?= e($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control"
                                   value="<?= e($_POST['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control"
                                   value="<?= e($_POST['address'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm" class="form-control" required minlength="6">
                        </div>
                    </div>
                    <button class="btn btn-gold w-100" type="submit">Register</button>
                </form>
                <p class="text-center small mt-3 mb-0">
                    Already have an account? <a href="login.php" class="text-gold">Log in</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
