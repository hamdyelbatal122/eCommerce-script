<?php 
    ob_start();
    $title = 'Search Results';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Product Listing</h2>
        <span class="text-muted"><?php echo (int) ($total ?? count($items ?? [])); ?> items found</span>
    </div>

    <form method="GET" action="/advanced-search" class="card card-body mb-4">
        <div class="row g-2">
            <div class="col-md-4"><input class="form-control" name="q" value="<?php echo htmlspecialchars($query ?? ''); ?>" placeholder="Search products"></div>
            <div class="col-md-2"><input class="form-control" type="number" step="0.01" name="min_price" value="<?php echo htmlspecialchars((string) ($min_price ?? '')); ?>" placeholder="Min price"></div>
            <div class="col-md-2"><input class="form-control" type="number" step="0.01" name="max_price" value="<?php echo htmlspecialchars((string) ($max_price ?? '')); ?>" placeholder="Max price"></div>
            <div class="col-md-2">
                <select class="form-select" name="sort">
                    <option value="newest" <?php echo (($sort ?? '') === 'newest') ? 'selected' : ''; ?>>Newest</option>
                    <option value="price_asc" <?php echo (($sort ?? '') === 'price_asc') ? 'selected' : ''; ?>>Price low-high</option>
                    <option value="price_desc" <?php echo (($sort ?? '') === 'price_desc') ? 'selected' : ''; ?>>Price high-low</option>
                    <option value="name_asc" <?php echo (($sort ?? '') === 'name_asc') ? 'selected' : ''; ?>>Name A-Z</option>
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Apply</button></div>
        </div>
    </form>

    <?php if (!empty($items)): ?>
        <div class="row">
            <?php foreach ($items as $item): ?>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card item-card">
                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 200px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-image text-white" style="font-size: 3rem;"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo \ECommerce\Core\View::escape($item['name']); ?></h5>
                            <p class="card-text text-muted text-truncate">
                                <?php echo \ECommerce\Core\View::escape(substr($item['description'], 0, 60)); ?>...
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="item-price">$<?php echo number_format($item['price'], 2); ?></span>
                                <a href="/items/<?php echo $item['item_id']; ?>" class="btn btn-sm btn-primary">View</a>
                            </div>
                            <?php if (\ECommerce\Core\Authenticator::isLoggedIn()): ?>
                                <form method="POST" action="/cart/add" class="mt-2">
                                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                                    <input type="hidden" name="item_id" value="<?php echo (int) $item['item_id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button class="btn btn-sm btn-outline-primary w-100">Add to Cart</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <h5>No items found</h5>
            <p>We couldn't find any items matching "<strong><?php echo htmlspecialchars($query); ?></strong>"</p>
            <a href="/" class="btn btn-primary">Back to Home</a>
        </div>
    <?php endif; ?>

    <?php if (($total_pages ?? 1) > 1): ?>
        <nav class="mt-4">
            <ul class="pagination">
                <?php for ($p = 1; $p <= (int) $total_pages; $p++): ?>
                    <li class="page-item <?php echo ((int) ($page ?? 1) === $p) ? 'active' : ''; ?>">
                        <a class="page-link" href="/advanced-search?<?php echo http_build_query(array_merge($_GET, ['page' => $p])); ?>"><?php echo $p; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php 
    $content = ob_get_clean();
    include_once __DIR__ . '/../../layouts/main.php';
?>
