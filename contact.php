<?php
/* Contact page - inquiry form saved to the database. */
require_once __DIR__ . '/includes/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '')    { $errors[] = 'Please enter your name.'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Please enter a valid email.'; }
    if ($subject === '') { $errors[] = 'Please enter a subject.'; }
    if ($message === '') { $errors[] = 'Please enter your message.'; }

    if (empty($errors)) {
        /* DML: INSERT - store the contact message. */
        $stmt = $pdo->prepare(
            'INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$name, $email, $subject, $message]);
        set_flash('Thank you for contacting us! We will reply to you soon.');
        redirect('contact.php');
    }
}

$pageTitle = 'Contact Us';
require __DIR__ . '/includes/header.php';
?>

<div class="container py-5">

    <div class="section-title">
        <h2>Get in Touch</h2>
        <div class="line"></div>
    </div>

    <?php show_flash(); ?>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger alert-permanent"><?= e($error) ?></div>
    <?php endforeach; ?>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="panel h-100">
                <h4 class="mb-3">Visit Our Showroom</h4>
                <p class="small mb-2"><strong>Address:</strong><br>No. 45, Galle Road, Colombo 03, Sri Lanka</p>
                <p class="small mb-2"><strong>Phone:</strong><br>+94 11 234 5678</p>
                <p class="small mb-2"><strong>Email:</strong><br>hello@maheeshajewels.com</p>
                <p class="small mb-0"><strong>Opening Hours:</strong><br>
                    Mon - Sat: 9.00 AM - 7.00 PM<br>
                    Sunday: 10.00 AM - 4.00 PM</p>
            </div>
        </div>
        <div class="col-md-7">
            <div class="panel">
                <h4 class="mb-3">Send Us a Message</h4>
                <form method="post" action="contact.php" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Your Name</label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?= e($_POST['name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Your Email</label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?= e($_POST['email'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" required
                               value="<?= e($_POST['subject'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="5" required><?= e($_POST['message'] ?? '') ?></textarea>
                    </div>
                    <button class="btn btn-gold" type="submit">Send Message</button>
                </form>
            </div>
        </div>
    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
