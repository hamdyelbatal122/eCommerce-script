<?php ob_start(); $title = 'Wishlist Analytics'; ?>
<div class="py-4">
    <h2 class="mb-4">Wishlist Analytics</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-6"><div class="card"><div class="card-body"><h6>Users with Wishlist</h6><h3><?php echo (int) ($users_with_wishlist ?? 0); ?></h3></div></div></div>
        <div class="col-md-6"><div class="card"><div class="card-body"><h6>Total Wishlist Entries</h6><h3><?php echo (int) ($total_entries ?? 0); ?></h3></div></div></div>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Item</th><th>Wishlist Count</th></tr></thead>
                <tbody>
                <?php foreach (($top_wishlisted ?? []) as $row): ?>
                    <tr><td><?php echo htmlspecialchars($row['name']); ?></td><td><?php echo (int) $row['wishes']; ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include_once __DIR__ . '/../../layouts/main.php'; ?>
