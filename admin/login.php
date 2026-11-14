<?php
declare(strict_types=1);
require __DIR__ . '/../helpers.php';
$cfg = require __DIR__ . '/../config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic brute-force slowdown: max 5 attempts per session per 5 minutes.
    $_SESSION['attempts'] = array_filter($_SESSION['attempts'] ?? [], fn($t) => $t > time() - 300);
    if (!csrf_check()) {
        $error = 'Session expired. Reload and try again.';
    } elseif (count($_SESSION['attempts']) >= 5) {
        $error = 'Too many attempts. Try again in a few minutes.';
    } else {
        $ok = hash_equals($cfg['admin_user'], (string)($_POST['user'] ?? ''))
            && password_verify((string)($_POST['pass'] ?? ''), $cfg['admin_hash']);
        if ($ok) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            header('Location: index.php');
            exit;
        }
        $_SESSION['attempts'][] = time();
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin login</title><link rel="stylesheet" href="../assets/style.css"></head>
<body><main class="card">
<h1>Admin login</h1>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?>
<label>Username <input name="user" required autofocus></label>
<label>Password <input type="password" name="pass" required></label>
<button type="submit">Log in</button></form>
<p class="muted small">Demo credentials: admin / admin123</p>
</main></body></html>
