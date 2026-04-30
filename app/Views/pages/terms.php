<?php ob_start(); $title = 'Terms of Use'; ?>
<div class="py-5">
    <h1>Terms of Use</h1>
    <p>Users must provide accurate information, follow local laws, and avoid fraudulent or abusive activity.</p>
    <p>Sellers are responsible for product accuracy, inventory availability, and delivery commitments.</p>
    <p>We reserve the right to suspend accounts that violate marketplace policies or legal requirements.</p>
</div>
<?php $content = ob_get_clean(); include_once __DIR__ . '/../layouts/main.php'; ?>
