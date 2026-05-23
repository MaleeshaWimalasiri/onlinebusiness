<?php
/* About page - static company information. */
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'About Us';
require __DIR__ . '/includes/header.php';
?>

<div class="container py-5">

    <div class="section-title">
        <h2>About <?= e(SITE_NAME) ?></h2>
        <div class="line"></div>
    </div>

    <div class="row align-items-center g-4">
        <div class="col-md-6">
            <img src="assets/images/placeholder.svg" class="detail-img" alt="About Maheesha Jewels">
        </div>
        <div class="col-md-6">
            <h3 class="text-gold">Our Story</h3>
            <p>Founded in 2010, Maheesha Jewels began as a small family workshop in Colombo with a
               simple belief: that fine jewelry should be both beautiful and meaningful. Today we
               are one of Sri Lanka's most trusted names in handcrafted jewelry.</p>
            <p>Every piece in our collection is designed and finished by skilled artisans using
               ethically sourced gemstones and responsibly mined precious metals.</p>
        </div>
    </div>

    <div class="row text-center mt-5 g-4">
        <div class="col-md-4">
            <div class="panel h-100">
                <h4 class="text-gold">Our Mission</h4>
                <p class="small mb-0">To craft timeless jewelry that celebrates love, milestones
                   and self-expression, while upholding the highest ethical standards.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel h-100">
                <h4 class="text-gold">Our Vision</h4>
                <p class="small mb-0">To be the most loved jewelry brand in South Asia, known for
                   craftsmanship, integrity and exceptional customer care.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel h-100">
                <h4 class="text-gold">Our Values</h4>
                <p class="small mb-0">Craftsmanship, honesty, sustainability and a genuine
                   commitment to every customer who walks through our doors.</p>
            </div>
        </div>
    </div>

    <div class="text-center mt-5">
        <h3>Ready to find your perfect piece?</h3>
        <a href="products.php" class="btn btn-gold mt-2">Browse the Collection</a>
    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
