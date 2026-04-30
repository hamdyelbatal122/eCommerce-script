<?php 
    ob_start();
    $title = 'User Dashboard';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-3 mb-4">
            <!-- User Card -->
            <div class="card text-center">
                <div style="width: 100px; height: 100px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin: 20px auto; font-size: 2rem;">
                    <i class="fas fa-user"></i>
                </div>
                <div class="card-body">
                    <h5><?php echo $user['full_name'] ?? $user['username']; ?></h5>
                    <p class="text-muted">@<?php echo $user['username']; ?></p>
                    <p class="small"><?php echo $user['email']; ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#items">My Items</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#comments">Comments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#settings">Settings</a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- My Items -->
                <div id="items" class="tab-pane fade show active">
                    <h5 class="mb-3">My Items</h5>
                    <?php if (!empty($items)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td><?php echo $item['name']; ?></td>
                                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                                            <td>
                                                <?php if ($item['approve'] == 0): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Approved</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($item['add_date'])); ?></td>
                                            <td>
                                                <a href="/items/<?php echo $item['item_id']; ?>" class="btn btn-sm btn-info">View</a>
                                                <button class="btn btn-sm btn-danger">Delete</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p>You haven't posted any items yet.</p>
                            <a href="/items/create" class="btn btn-primary">Post Your First Item</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Comments -->
                <div id="comments" class="tab-pane fade">
                    <h5 class="mb-3">My Comments</h5>
                    <?php if (!empty($comments)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Comment</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($comments as $comment): ?>
                                        <tr>
                                            <td><?php echo $comment['item_id']; ?></td>
                                            <td><?php echo substr($comment['comment'], 0, 50); ?>...</td>
                                            <td><?php echo date('M d, Y', strtotime($comment['comment_date'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-danger">Delete</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No comments yet.</p>
                    <?php endif; ?>
                </div>

                <!-- Settings -->
                <div id="settings" class="tab-pane fade">
                    <h5 class="mb-3">Account Settings</h5>
                    <form method="POST" action="/dashboard/update" id="updateForm">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" value="<?php echo $user['full_name']; ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>

                    <hr class="my-4">

                    <h5 class="mb-3">Change Password</h5>
                    <form method="POST" action="/dashboard/change-password" id="passwordForm">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="password_confirm" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
    $content = ob_get_clean();
    include_once __DIR__ . '/../../layouts/main.php';
?>
