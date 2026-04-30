<?php
ob_start();
$title = 'My Wishlist';
?>
<div class="py-5">
    <h2 class="mb-4">My Wishlist</h2>
    <?php if (empty($items)): ?>
        <div class="alert alert-info">Your wishlist is empty.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($items as $item): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                            <p class="text-primary fw-bold">$<?php echo number_format((float) $item['price'], 2); ?></p>
                            <div class="d-flex gap-2">
                                <a href="/items/<?php echo (int) $item['item_id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
include_once __DIR__ . '/../layouts/main.php';
?>
