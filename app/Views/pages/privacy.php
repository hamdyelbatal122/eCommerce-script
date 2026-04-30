<?php ob_start(); $title = 'Privacy Policy'; ?>
<div class="py-5">
    <h1>Privacy Policy</h1>
    <p>We collect only the data required to process orders, provide support, and improve platform quality.</p>
    <p>Your personal information is never sold. Payment details are handled securely through trusted providers.</p>
    <p>By using this platform, you agree to our data processing practices and security controls.</p>
</div>
<?php $content = ob_get_clean(); include_once __DIR__ . '/../layouts/main.php'; ?>
