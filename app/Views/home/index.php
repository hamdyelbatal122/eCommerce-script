<?php 
    ob_start();
?>

<!-- Hero Section -->
<div class="hero mb-5">
    <div class="container-fluid">
        <h1>Welcome to EMarket</h1>
        <p class="lead">Buy and sell products in a trusted, secure marketplace</p>
        
        <div class="input-group mx-auto" style="max-width: 700px;">
            <input type="text" class="form-control form-control-lg" placeholder="Search items..." id="searchInput">
            <button class="btn btn-light btn-lg" type="button" id="searchBtn">
                <i class="fas fa-search"></i> Search
            </button>
        </div>
        <div class="mt-4 d-flex justify-content-center gap-2 flex-wrap">
            <a href="/search" class="btn btn-outline-light">Explore Deals</a>
            <a href="/items/create" class="btn btn-warning">Start Selling</a>
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- Statistics -->
    <div class="row mb-5">
        <div class="col-md-4 mb-4">
            <div class="card text-center p-4">
                <h3 class="text-primary mb-2"><?php echo number_format($stats['total_items'] ?? 0); ?></h3>
                <p class="text-secondary">Products Listed</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card text-center p-4">
                <h3 class="text-primary mb-2"><?php echo number_format($stats['total_users'] ?? 0); ?></h3>
                <p class="text-secondary">Active Sellers</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card text-center p-4">
                <h3 class="text-primary mb-2">100%</h3>
                <p class="text-secondary">Secure Transactions</p>
            </div>
        </div>
    </div>

    <!-- Latest Items -->
    <div class="mb-5">
        <h2 class="mb-4">Latest Listings</h2>
        <div class="row">
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card item-card">
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 200px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-image text-white" style="font-size: 3rem;"></i>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo \ECommerce\Core\View::escape($item['name']); ?></h5>
                                <p class="card-text text-muted text-truncate"><?php echo \ECommerce\Core\View::escape(substr($item['description'], 0, 60)); ?>...</p>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="item-price">$<?php echo number_format($item['price'], 2); ?></span>
                                    <a href="/items/<?php echo $item['item_id']; ?>" class="btn btn-sm btn-primary">View</a>
                                </div>
                                <?php if (\ECommerce\Core\Authenticator::isLoggedIn()): ?>
                                    <form method="POST" action="/cart/add" class="d-grid">
                                        <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                                        <input type="hidden" name="item_id" value="<?php echo (int) $item['item_id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button class="btn btn-sm btn-outline-primary">Add to Cart</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-center text-muted">No items listed yet. Be the first to post!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Categories -->
    <?php if (!empty($categories)): ?>
        <div class="mb-5">
            <h2 class="mb-4">Browse Categories</h2>
            <div class="row">
                <?php foreach ($categories as $category): ?>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <a href="/items/category/<?php echo $category['id']; ?>" class="card text-decoration-none text-dark">
                            <div class="card-body text-center">
                                <i class="fas fa-folder text-primary" style="font-size: 2rem; margin-bottom: 10px;"></i>
                                <h6 class="card-title"><?php echo $category['name']; ?></h6>
                                <small class="text-muted"><?php echo $category['items_count']; ?> items</small>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php 
    $content = ob_get_clean();
    include_once __DIR__ . '/../layouts/main.php';
?>
