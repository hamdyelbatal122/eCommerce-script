<?php ob_start(); $title = 'Reset Password'; ?>
<div class="py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="mb-3">Reset Password</h2>
                    <form method="POST" action="/password/reset/<?php echo urlencode($token); ?>">
                        <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="password" minlength="8" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="password_confirm" minlength="8" required>
                        </div>
                        <button class="btn btn-primary w-100">Update password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include_once __DIR__ . '/../layouts/main.php'; ?>
