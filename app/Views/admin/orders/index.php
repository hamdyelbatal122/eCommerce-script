<?php
ob_start();
$title = 'Orders Management';
?>
<div class="py-4">
    <h2 class="mb-4">Orders Management</h2>
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Order</th><th>User</th><th>Status</th><th>Total</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach (($orders ?? []) as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                        <td><?php echo htmlspecialchars($order['username'] ?? 'N/A'); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($order['status']); ?></span></td>
                        <td>$<?php echo number_format((float) $order['total_price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include_once __DIR__ . '/../../layouts/main.php';
?>
