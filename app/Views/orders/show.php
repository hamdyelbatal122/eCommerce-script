<?php
ob_start();
$title = 'Order Details';
$statusSteps = ['pending', 'confirmed', 'shipped', 'delivered'];
$currentStatus = $order['status'] ?? 'pending';
$currentStep = array_search($currentStatus, $statusSteps, true);
if ($currentStep === false) {
    $currentStep = 0;
}
?>
<div class="py-5">
    <h2 class="mb-2">Order <?php echo htmlspecialchars($order['order_number']); ?></h2>
    <p class="text-muted">Tracking status: <strong><?php echo htmlspecialchars($currentStatus); ?></strong></p>
    <?php if (!empty($order['tracking_number'])): ?>
        <a class="btn btn-outline-primary btn-sm mb-3" href="/orders/<?php echo (int) $order['id']; ?>/track">
            Track shipment with DHL
        </a>
    <?php endif; ?>

    <div class="d-flex gap-2 mb-4 flex-wrap">
        <?php foreach ($statusSteps as $idx => $step): ?>
            <span class="badge <?php echo $idx <= $currentStep ? 'bg-success' : 'bg-light text-dark'; ?>">
                <?php echo htmlspecialchars(ucfirst($step)); ?>
            </span>
        <?php endforeach; ?>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-3">Items</h5>
            <?php foreach (($items ?? []) as $item): ?>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span><?php echo htmlspecialchars($item['name']); ?> x<?php echo (int) $item['quantity']; ?></span>
                    <strong>$<?php echo number_format((float) $item['total_price'], 2); ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between"><span>Total Paid</span><strong>$<?php echo number_format((float) $order['total_price'], 2); ?></strong></div>
            <div class="d-flex justify-content-between"><span>Payment</span><span><?php echo htmlspecialchars($order['payment_status']); ?></span></div>
            <div class="d-flex justify-content-between"><span>Shipping Address</span><span><?php echo htmlspecialchars($order['shipping_address']); ?></span></div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include_once __DIR__ . '/../layouts/main.php';
?>
