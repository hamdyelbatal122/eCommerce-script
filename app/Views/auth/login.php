<?php 
    ob_start();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h2 class="card-title text-center mb-4">Login</h2>
                    
                    <form method="POST" action="/login" id="loginForm">
                        <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
                    </form>

                    <div class="d-grid mb-3">
                        <a href="/auth/google/redirect" class="btn btn-outline-danger">
                            Continue with Google
                        </a>
                    </div>

                    <hr>

                    <p class="text-center text-muted mb-0">
                        Don't have an account? 
                        <a href="/register" class="text-primary fw-bold">Sign up here</a>
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
