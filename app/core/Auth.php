<?php
declare(strict_types=1);

final class Auth
{
    private const MAX_LOGIN_ATTEMPTS = 8;
    private const LOGIN_WINDOW_MINUTES = 15;

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function login(PDO $pdo, string $email, string $password): bool
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        if (self::isRateLimited($pdo, $email, $ip)) {
            return false;
        }

        $stmt = $pdo->prepare('SELECT id, full_name, email, role, password_hash FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            self::recordLoginAttempt($pdo, $email, $ip, false);
            return false;
        }

        unset($user['password_hash']);
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        self::recordLoginAttempt($pdo, $email, $ip, true);
        return true;
    }

    public static function register(PDO $pdo, array $data): bool
    {
        $stmt = $pdo->prepare(
            'INSERT INTO users(full_name,email,phone,role,password_hash) VALUES (:full_name,:email,:phone,:role,:password_hash)'
        );
        return $stmt->execute([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'role' => $data['role'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: login.php');
            exit;
        }
    }

    public static function requireRole(array $roles): void
    {
        self::requireLogin();
        $role = self::user()['role'] ?? '';
        if (!in_array($role, $roles, true)) {
            http_response_code(403);
            echo 'Access denied.';
            exit;
        }
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    private static function isRateLimited(PDO $pdo, string $email, string $ip): bool
    {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM auth_attempts
             WHERE attempted_at >= DATE_SUB(NOW(), INTERVAL :minutes MINUTE)
               AND success = 0
               AND (email = :email OR ip_address = :ip)'
        );
        $stmt->execute([
            'minutes' => self::LOGIN_WINDOW_MINUTES,
            'email' => $email,
            'ip' => $ip,
        ]);
        $fails = (int) $stmt->fetchColumn();
        return $fails >= self::MAX_LOGIN_ATTEMPTS;
    }

    private static function recordLoginAttempt(PDO $pdo, string $email, string $ip, bool $success): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO auth_attempts(email, ip_address, success, attempted_at) VALUES(:email, :ip, :success, NOW())'
        );
        $stmt->execute([
            'email' => $email,
            'ip' => $ip,
            'success' => $success ? 1 : 0,
        ]);
    }

    public static function createPasswordResetToken(PDO $pdo, string $email): ?string
    {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if (!$user) {
            return null;
        }

        $token = bin2hex(random_bytes(24));
        $tokenHash = hash('sha256', $token);

        $pdo->prepare('DELETE FROM password_resets WHERE user_id = :user_id')
            ->execute(['user_id' => (int) $user['id']]);

        $insert = $pdo->prepare(
            'INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 30 MINUTE))'
        );
        $insert->execute([
            'user_id' => (int) $user['id'],
            'token_hash' => $tokenHash,
        ]);

        return $token;
    }

    public static function resetPasswordWithToken(PDO $pdo, string $token, string $newPassword): bool
    {
        $tokenHash = hash('sha256', $token);
        $stmt = $pdo->prepare(
            'SELECT id, user_id FROM password_resets WHERE token_hash = :token_hash AND used_at IS NULL AND expires_at > NOW() LIMIT 1'
        );
        $stmt->execute(['token_hash' => $tokenHash]);
        $reset = $stmt->fetch();
        if (!$reset) {
            return false;
        }

        $pdo->beginTransaction();
        try {
            $pdo->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id')
                ->execute([
                    'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                    'id' => (int) $reset['user_id'],
                ]);

            $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id')
                ->execute(['id' => (int) $reset['id']]);

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            return false;
        }
    }
}
