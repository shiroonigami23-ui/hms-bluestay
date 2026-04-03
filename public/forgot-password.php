<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/includes/bootstrap.php';

$title = 'Forgot Password | BlueStay HMS';
$message = '';
$resetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_from_request();
    $email = trim($_POST['email'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $token = Auth::createPasswordResetToken($pdo, $email);
        $message = 'If this email exists, a reset link was generated.';
        if ($token) {
            $resetLink = 'reset-password.php?token=' . urlencode($token);
        }
    } else {
        $message = 'Enter a valid email address.';
    }
}

require dirname(__DIR__) . '/app/views/partials/header.php';
?>
<main class="container narrow">
  <section class="card">
    <h1>Forgot Password</h1>
    <p class="muted">Enter your registered email to get a reset link.</p>
    <?php if ($message): ?><p class="alert"><?= e($message) ?></p><?php endif; ?>
    <form method="post" class="form-grid">
      <?= csrf_input() ?>
      <label>Email<input type="email" name="email" required></label>
      <button class="btn" type="submit">Generate Reset Link</button>
    </form>
    <?php if ($resetLink): ?>
      <p class="helper">Demo link: <a href="<?= e($resetLink) ?>">Reset Password</a></p>
    <?php endif; ?>
  </section>
</main>
<?php require dirname(__DIR__) . '/app/views/partials/footer.php'; ?>
