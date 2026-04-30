<?php 
    ob_start();
?>

<?php if (\ECommerce\Core\Authenticator::isAdmin()): ?>
    <!-- Admin Dashboard -->
    <div class="container-fluid py-5">
        <h1 class="mb-4">Admin Dashboard</h1>

        <!-- Statistics Cards -->
        <div class="row mb-5">
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Total Users</h6>
                        <h3 class="text-primary"><?php echo number_format($stats['total_users']); ?></h3>
                        <small class="text-warning"><?php echo $stats['pending_users']; ?> pending</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Total Items</h6>
                        <h3 class="text-success"><?php echo number_format($stats['total_items']); ?></h3>
                        <small class="text-warning"><?php echo $stats['pending_items']; ?> pending</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Comments</h6>
                        <h3 class="text-info"><?php echo number_format($stats['total_comments']); ?></h3>
                        <small class="text-warning"><?php echo $stats['pending_comments']; ?> pending</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <a href="/admin/users?status=pending" class="card text-decoration-none">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Action Required</h6>
                        <h3 class="text-danger">
                            <?php echo ($stats['pending_users'] + $stats['pending_items'] + $stats['pending_comments']); ?>
                        </h3>
                        <small>Approvals needed</small>
                    </div>
                </a>
            </div>
        </div>

        <!-- Admin Navigation -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="btn-group w-100" role="group">
                    <a href="/admin/users" class="btn btn-outline-primary">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a href="/admin/items" class="btn btn-outline-primary">
                        <i class="fas fa-box"></i> Manage Items
                    </a>
                    <a href="/admin/comments" class="btn btn-outline-primary">
                        <i class="fas fa-comments"></i> Manage Comments
                    </a>
                    <a href="/admin/categories" class="btn btn-outline-primary">
                        <i class="fas fa-folder"></i> Categories
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <h4>Latest User Registrations</h4>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tbody>
                            <?php if (!empty($latest_users)): ?>
                                <?php foreach ($latest_users as $user): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $user['username']; ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo $user['email']; ?></small>
                                        </td>
                                        <td>
                                            <?php if ($user['reg_status'] == 0): ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="text-muted">No recent users</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <h4>Latest Item Listings</h4>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tbody>
                            <?php if (!empty($latest_items)): ?>
                                <?php foreach ($latest_items as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo substr($item['name'], 0, 30); ?>...</strong>
                                            <br>
                                            <small class="text-muted">$<?php echo number_format($item['price'], 2); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($item['approve'] == 0): ?>
                                                <span class="badge bg-danger">Not Approved</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="text-muted">No recent items</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="container py-5 text-center">
        <h2>Access Denied</h2>
        <p class="text-muted">You do not have permission to access this page.</p>
        <a href="/" class="btn btn-primary">Go Home</a>
    </div>
<?php endif; ?>

<?php 
    $content = ob_get_clean();
    include_once __DIR__ . '/../../layouts/main.php';
?>
