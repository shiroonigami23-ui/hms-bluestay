<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/includes/bootstrap.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    if (Auth::login($pdo, $email, $password)) {
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Invalid credentials.';
}

$title = 'Login | BlueStay HMS';
require dirname(__DIR__) . '/app/views/partials/header.php';
?>
<main class="container narrow">
  <section class="card">
    <h1>Login</h1>
    <?php if ($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>
    <form method="post" class="form-grid">
      <label>Email<input type="email" name="email" required></label>
      <label>Password<input type="password" name="password" required></label>
      <button class="btn" type="submit">Sign In</button>
    </form>
    <div class="form-links">
      <a href="forgot-password.php">Forgot password?</a>
      <a href="register.php">Create new account</a>
    </div>
  </section>
</main>
<?php require dirname(__DIR__) . '/app/views/partials/footer.php'; ?>
