<?php
/**
 * One-time web installer. Creates tables, seeds default content and the
 * first admin account, then locks itself (storage/installed.lock).
 * Delete this file after installation for extra safety.
 */
declare(strict_types=1);

require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/security.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/database/installer.php';

date_default_timezone_set((string)(config('timezone') ?: 'UTC'));
start_secure_session();
send_security_headers(true);

$locked = is_file(root_path('storage/installed.lock'));
$errors = [];
$done   = false;
$dbOk   = null;

if (!$locked) {
    try {
        DB::pdo();
        $dbOk = true;
    } catch (Throwable $e) {
        $dbOk = false;
        $errors[] = 'Database connection failed. Check DB_* values in your .env file. (' . $e->getMessage() . ')';
    }
}

if (!$locked && $dbOk && is_post()) {
    if (!csrf_valid()) {
        $errors[] = 'Session expired — please reload and try again.';
    } else {
        $name  = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $pass  = (string)($_POST['password'] ?? '');
        if ($name === '') $errors[] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
        if (strlen($pass) < 10 || !preg_match('/[A-Za-z]/', $pass) || !preg_match('/\d/', $pass)) {
            $errors[] = 'Password must be at least 10 characters and include letters and numbers.';
        }
        if (!is_writable(root_path('storage'))) $errors[] = 'The storage/ folder must be writable.';
        if (!$errors) {
            try {
                ht_install($name, $email, $pass);
                $done = true;
            } catch (Throwable $e) {
                $errors[] = 'Installation failed: ' . $e->getMessage();
            }
        }
    }
}
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Install · Primary Infotech</title>
    <link rel="stylesheet" href="<?= e(path('assets/css/admin.css')) ?>">
</head>
<body class="auth">
<main class="auth__card">
    <h1 class="auth__title">Install</h1>
    <?php if ($locked): ?>
        <p class="auth__sub">The site is already installed. For safety, delete <code>install.php</code> from the server.</p>
        <a class="a-btn a-btn--primary" href="<?= e(path('admin/login.php')) ?>">Go to admin</a>
    <?php elseif ($done): ?>
        <div class="a-alert a-alert--ok">Installation complete. Please delete <code>install.php</code> now.</div>
        <a class="a-btn a-btn--primary" href="<?= e(path('admin/login.php')) ?>">Sign in to admin</a>
    <?php else: ?>
        <p class="auth__sub">Driver: <strong><?= e(DB::driver()) ?></strong> · Database: <?= $dbOk ? '<span class="a-ok">connected</span>' : '<span class="a-bad">not connected</span>' ?></p>
        <?php foreach ($errors as $er): ?><div class="a-alert a-alert--err"><?= e($er) ?></div><?php endforeach; ?>
        <p class="a-alert a-alert--warn">This will create all tables (existing tables with the same names are dropped) and seed the default content.</p>
        <form method="post" class="a-form">
            <?= csrf_field() ?>
            <label class="a-field"><span>Admin name</span><input name="name" required value="<?= e($_POST['name'] ?? '') ?>"></label>
            <label class="a-field"><span>Admin email</span><input name="email" type="email" required value="<?= e($_POST['email'] ?? '') ?>"></label>
            <label class="a-field"><span>Password (min 10 chars, letters + numbers)</span><input name="password" type="password" required minlength="10" autocomplete="new-password"></label>
            <button class="a-btn a-btn--primary a-btn--block" <?= $dbOk ? '' : 'disabled' ?>>Install now</button>
        </form>
    <?php endif; ?>
</main>
</body>
</html>
