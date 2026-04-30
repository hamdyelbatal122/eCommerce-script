<?php ob_start(); $title = 'About Us'; ?>
<div class="py-5">
    <h1>About Us</h1>
    <p>We build a trusted global marketplace for buyers and sellers with secure payments, fast delivery, and transparent order tracking.</p>
    <p>Our mission is to provide a world-class commerce experience with modern technology, fair pricing, and excellent customer support.</p>
</div>
<?php $content = ob_get_clean(); include_once __DIR__ . '/../layouts/main.php'; ?>
