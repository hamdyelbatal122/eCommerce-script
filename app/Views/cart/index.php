<?php
ob_start();
$title = 'Shopping Cart';
?>

<div class="py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Your Cart</h2>
        <a href="/" class="btn btn-outline-primary btn-sm">Continue shopping</a>
    </div>

    <?php if (empty($cart)): ?>
        <div class="card"><div class="card-body text-center py-5">
            <h5>Your cart is empty</h5>
            <p class="text-muted">Add products to begin checkout.</p>
            <a href="/" class="btn btn-primary">Browse products</a>
        </div></div>
    <?php else: ?>
        <div class="card mb-4">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th></tr></thead>
                    <tbody>
                    <?php foreach ($cart as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td>$<?php echo number_format((float) $row['price'], 2); ?></td>
                            <td><?php echo (int) $row['quantity']; ?></td>
                            <td>$<?php echo number_format(((float) $row['price']) * ((int) $row['quantity']), 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between"><span>Subtotal</span><strong>$<?php echo number_format($subtotal, 2); ?></strong></div>
                <div class="d-flex justify-content-between"><span>Delivery</span><strong>$<?php echo number_format($delivery_fee, 2); ?></strong></div>
                <hr>
                <div class="d-flex justify-content-between fs-5"><span>Total</span><strong>$<?php echo number_format($total, 2); ?></strong></div>
                <a class="btn btn-primary w-100 mt-3" href="/checkout">Proceed to checkout</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include_once __DIR__ . '/../layouts/main.php';
?>
