<?php
session_start();
require_once __DIR__ . '/config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Username dan password harus diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT id_user, username, password, role, id_guru FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            $storedPassword = $user['password'];
            $passwordVerified = false;

            if (password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
                $passwordVerified = password_verify($password, $storedPassword);
                if ($passwordVerified) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $pdo->prepare('UPDATE users SET password = ? WHERE id_user = ?');
                    $updateStmt->execute([$newHash, $user['id_user']]);
                }
            } else {
                $passwordVerified = password_verify($password, $storedPassword);
            }

            if ($passwordVerified) {
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                header('Location: admin/dashboard.php');
                exit;
            }
        }

        $error = 'Username atau password salah.';
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Absen XII RPL 1</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <section class="login-page">
        <div class="login-card">
            <div class="login-brand">
                <div class="brand-icon">📘</div>
                <h1>Absen Kelas</h1>
                <p>Sistem Dashboard Absensi XII RPL 1</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post" action="login.php" autocomplete="off">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" placeholder="Masukkan username" required>

                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Masukkan password" required>

                <button type="submit" class="primary-btn">Masuk</button>
            </form>

            <p class="help-text">Pastikan username dan password benar. Hubungi admin jika lupa password.</p>
        </div>
    </section>
</body>
</html>
