<?php 
    ob_start();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h2 class="card-title text-center mb-4">Create Account</h2>
                    
                    <form method="POST" action="/register" id="registerForm">
                        <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                            <small class="text-muted">3-20 characters, alphanumeric</small>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="text-muted">At least 6 characters</small>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">Create Account</button>
                    </form>

                    <div class="d-grid mb-3">
                        <a href="/auth/google/redirect" class="btn btn-outline-danger">
                            Sign up with Google
                        </a>
                    </div>

                    <hr>

                    <p class="text-center text-muted mb-0">
                        Already have an account? 
                        <a href="/login" class="text-primary fw-bold">Login here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
    $content = ob_get_clean();
    include_once __DIR__ . '/../../layouts/main.php';
?>
