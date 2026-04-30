<?php ob_start(); $title = 'Coupon Management'; ?>
<div class="py-4">
    <h2 class="mb-4">Coupon Management</h2>
    <div class="card mb-4">
        <div class="card-body">
            <form id="couponForm" method="POST" action="/admin/coupons">
                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                <div class="row g-2">
                    <div class="col-md-4"><input class="form-control" name="code" placeholder="Code (e.g. SPRING20)" required></div>
                    <div class="col-md-3"><input class="form-control" type="number" step="0.01" min="1" max="100" name="discount_percent" placeholder="Discount %" required></div>
                    <div class="col-md-3"><input class="form-control" type="datetime-local" name="expires_at"></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Create</button></div>
                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Code</th><th>Discount</th><th>Status</th><th>Expires</th><th>Created</th></tr></thead>
                <tbody>
                <?php foreach (($coupons ?? []) as $coupon): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($coupon['code']); ?></td>
                        <td><?php echo number_format((float) $coupon['discount_percent'], 2); ?>%</td>
                        <td><span class="badge <?php echo ((int) $coupon['is_active'] === 1) ? 'bg-success' : 'bg-secondary'; ?>"><?php echo ((int) $coupon['is_active'] === 1) ? 'Active' : 'Inactive'; ?></span></td>
                        <td><?php echo htmlspecialchars($coupon['expires_at'] ?? 'No expiry'); ?></td>
                        <td><?php echo htmlspecialchars($coupon['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include_once __DIR__ . '/../../layouts/main.php'; ?>
