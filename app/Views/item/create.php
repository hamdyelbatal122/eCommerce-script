<?php 
    ob_start();
    $title = 'Create Item';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4">Post a New Item</h2>

            <form method="POST" action="/items/create" id="createItemForm" class="needs-validation">
                <div class="mb-3">
                    <label class="form-label">Item Name *</label>
                    <input type="text" class="form-control" name="name" required minlength="5">
                    <small class="text-muted">At least 5 characters</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Category *</label>
                    <select class="form-select" name="category_id" required>
                        <option value="">Select a category</option>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo $category['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description *</label>
                    <textarea class="form-control" name="description" rows="5" required minlength="10" placeholder="Describe your item in detail..."></textarea>
                    <small class="text-muted">At least 10 characters</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Price *</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" name="price" step="0.01" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tags (comma-separated)</label>
                        <input type="text" class="form-control" name="tags" placeholder="e.g., electronics, new, shipping">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Country of Origin</label>
                        <input type="text" class="form-control" name="country_made" placeholder="e.g., USA, China">
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Your item will be reviewed by our admin team before appearing on the marketplace.
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="/" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg">Post Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
    $content = ob_get_clean();
    include_once __DIR__ . '/../../layouts/main.php';
?>
