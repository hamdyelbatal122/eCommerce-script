<?php ob_start(); $title = 'Forgot Password'; ?>
<div class="py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="mb-3">Forgot Password</h2>
                    <p class="text-muted">Enter your email to generate a password reset link.</p>
                    <form method="POST" action="/password/forgot">
                        <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <button class="btn btn-primary w-100">Generate reset link</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include_once __DIR__ . '/../layouts/main.php'; ?>
