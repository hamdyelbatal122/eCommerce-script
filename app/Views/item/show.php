<?php 
    ob_start();
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8">
            <?php if (!empty($item)): ?>
                <div class="card mb-4">
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 300px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-image text-white" style="font-size: 4rem;"></i>
                    </div>
                    <div class="card-body">
                        <h1 class="card-title"><?php echo \ECommerce\Core\View::escape($item['name']); ?></h1>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p class="text-muted mb-1">Price</p>
                                <h3 class="text-primary">$<?php echo number_format($item['price'], 2); ?></h3>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-1">Category</p>
                                <p><a href="/items/category/<?php echo $item['cat_id']; ?>"><?php echo $item['category_name']; ?></a></p>
                            </div>
                        </div>
                        <?php if (\ECommerce\Core\Authenticator::isLoggedIn()): ?>
                            <div class="d-flex gap-2 mb-4">
                                <form method="POST" action="/cart/add">
                                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                                    <input type="hidden" name="item_id" value="<?php echo (int) $item['item_id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-cart-plus me-1"></i>Add to cart
                                    </button>
                                </form>
                                <form method="POST" action="/wishlist/add">
                                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                                    <input type="hidden" name="item_id" value="<?php echo (int) $item['item_id']; ?>">
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-heart me-1"></i>Wishlist
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <hr>

                        <h5>Description</h5>
                        <p><?php echo nl2br(\ECommerce\Core\View::escape($item['description'])); ?></p>

                        <?php if (!empty($item['tags'])): ?>
                            <h5>Tags</h5>
                            <p>
                                <?php foreach (explode(',', $item['tags']) as $tag): ?>
                                    <a href="/search?q=<?php echo urlencode(trim($tag)); ?>" class="badge bg-secondary text-decoration-none">
                                        <?php echo trim($tag); ?>
                                    </a>
                                <?php endforeach; ?>
                            </p>
                        <?php endif; ?>

                        <hr>

                        <h5>Seller Information</h5>
                        <a href="/user/<?php echo $item['member_id']; ?>" class="text-decoration-none">
                            <div class="d-flex align-items-center">
                                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin-right: 15px;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0"><?php echo $item['full_name'] ?? $item['username']; ?></h6>
                                    <small class="text-muted">@<?php echo $item['username']; ?></small>
                                </div>
                            </div>
                        </a>
                        <hr>
                        <h5>Delivery & Returns</h5>
                        <ul class="mb-0 text-muted">
                            <li>Fast shipping with trusted carriers.</li>
                            <li>Order tracking is available after shipment.</li>
                            <li>7-day return policy for eligible items.</li>
                        </ul>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Comments (<?php echo $comments_count; ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($can_comment): ?>
                            <form method="POST" action="/comments/add" class="mb-4">
                                <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                <div class="mb-3">
                                    <textarea class="form-control" name="comment" rows="3" placeholder="Share your thoughts..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Post Comment</button>
                            </form>
                        <?php else: ?>
                            <p class="text-muted"><a href="/login">Login</a> to comment on this item.</p>
                        <?php endif; ?>

                        <hr>

                        <?php if (!empty($comments)): ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="mb-4">
                                    <div class="d-flex align-items-start">
                                        <div style="width: 40px; height: 40px; background: #667eea; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin-right: 10px; flex-shrink: 0;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0"><?php echo $comment['username']; ?></h6>
                                            <small class="text-muted"><?php echo date('M d, Y', strtotime($comment['comment_date'])); ?></small>
                                            <p class="mt-2 mb-0"><?php echo \ECommerce\Core\View::escape($comment['comment']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center">No comments yet. Be the first to comment!</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">Item not found</div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <!-- Sidebar - Similar Items -->
            <h5 class="mb-3">Similar Items</h5>
            <p class="text-muted">Recommendations coming soon</p>
        </div>
    </div>
</div>

<?php 
    $content = ob_get_clean();
    include_once __DIR__ . '/../../layouts/main.php';
?>
