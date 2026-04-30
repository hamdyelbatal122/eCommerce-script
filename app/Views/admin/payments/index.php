<?php
ob_start();
$title = 'Payments Management';
?>
<div class="py-4">
    <h2 class="mb-4">Payments Management</h2>
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>ID</th><th>User</th><th>Order</th><th>Provider</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach (($payments ?? []) as $payment): ?>
                    <tr>
                        <td>#<?php echo (int) $payment['id']; ?></td>
                        <td><?php echo htmlspecialchars($payment['username'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($payment['order_number'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($payment['provider']); ?></td>
                        <td>$<?php echo number_format((float) $payment['amount'], 2); ?> <?php echo htmlspecialchars($payment['currency']); ?></td>
                        <td><span class="badge bg-info"><?php echo htmlspecialchars($payment['status']); ?></span></td>
                        <td><?php echo htmlspecialchars($payment['created_at']); ?></td>
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
