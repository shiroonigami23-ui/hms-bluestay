<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/includes/bootstrap.php';

$title = 'Reset Password | BlueStay HMS';
$message = '';
$ok = false;
$token = trim($_GET['token'] ?? $_POST['token'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if (strlen($password) < 8) {
        $message = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $message = 'Passwords do not match.';
    } elseif (!$token) {
        $message = 'Invalid reset token.';
    } else {
        $ok = Auth::resetPasswordWithToken($pdo, $token, $password);
        $message = $ok ? 'Password updated successfully. You can log in now.' : 'Reset link is invalid or expired.';
    }
}

require dirname(__DIR__) . '/app/views/partials/header.php';
?>
<main class="container narrow">
  <section class="card">
    <h1>Reset Password</h1>
    <?php if ($message): ?><p class="alert"><?= e($message) ?></p><?php endif; ?>
    <?php if (!$ok): ?>
      <form method="post" class="form-grid">
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <label>New Password<input type="password" name="password" required></label>
        <label>Confirm Password<input type="password" name="confirm_password" required></label>
        <button class="btn" type="submit">Update Password</button>
      </form>
    <?php else: ?>
      <a class="btn" href="login.php">Go to Login</a>
    <?php endif; ?>
  </section>
</main>
<?php require dirname(__DIR__) . '/app/views/partials/footer.php'; ?>
