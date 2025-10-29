<?php
require_once __DIR__ . '/../config/config.php';

if (isLoggedIn()) {
    redirect(url('dashboard.php'));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';   
    
    if ($username && $password) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM users WHERE username = ? AND is_active = ?";
        $stmt = $db->query($sql, [$username, true]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            redirect(url('dashboard.php'));
        } else {
            $error = 'Username atau password salah!';
        }
    } else {
        $error = 'Mohon isi username dan password!';
    }
}

$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
</head>
<body>
    <div class="login-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card login-card shadow-lg">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <i class="bi bi-shop text-primary" style="font-size: 4rem;"></i>
                                <h3 class="mt-3"><?php echo APP_NAME; ?></h3>
                                <p class="text-muted">Sistem Manajemen Inventori & Kasir</p>
                            </div>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" name="username" class="form-control" required autofocus>
                                    </div>
                                </div>
                                
                                <div class=fan class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100 py-2">
                                    <i class="bi bi-box-arrow-in-right"></i> Login
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
