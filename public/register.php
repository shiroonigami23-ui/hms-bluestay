<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/includes/bootstrap.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_from_request();
    $data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'role' => 'customer',
        'password' => (string) ($_POST['password'] ?? ''),
    ];

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($data['password']) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        try {
            Auth::register($pdo, $data);
            Auth::login($pdo, $data['email'], $data['password']);
            header('Location: dashboard.php');
            exit;
        } catch (Throwable $e) {
            $error = 'Unable to register. Try another email.';
        }
    }
}

$title = 'Register | BlueStay HMS';
require dirname(__DIR__) . '/app/views/partials/header.php';
?>
<main class="container narrow">
  <section class="card">
    <h1>Create Customer Account</h1>
    <?php if ($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>
    <form method="post" class="form-grid">
      <?= csrf_input() ?>
      <label>Full Name<input type="text" name="full_name" required></label>
      <label>Email<input type="email" name="email" required></label>
      <label>Phone<input type="text" name="phone" required></label>
      <label>Password<input type="password" name="password" required></label>
      <button class="btn" type="submit">Register</button>
    </form>
    <p class="helper tiny">Staff accounts are created by hotel admin and shared via assigned official email.</p>
  </section>
</main>
<?php require dirname(__DIR__) . '/app/views/partials/footer.php'; ?>
