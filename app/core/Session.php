<?php
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        session_name(Config::get('app.session_name', 'LFSESSID'));
        session_set_cookie_params([
            'lifetime' => 0,                 // session cookie
            'path'     => Config::get('app.base_url') . '/',
            'httponly' => true,              // not readable from JS
            'samesite' => 'Lax',
            'secure'   => !empty($_SERVER['HTTPS']),
        ]);
        session_start();
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(16));
        }
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);         // fresh id on privilege change
        $_SESSION['user'] = [
            'id'         => (int) $user['id'],
            'name'       => $user['name'],
            'email'      => $user['email'],
            'student_id' => $user['student_id'],
            'role'       => $user['role'],
        ];
    }

    public static function user(): ?array   { return $_SESSION['user'] ?? null; }
    public static function id(): ?int       { return $_SESSION['user']['id'] ?? null; }
    public static function role(): string   { return $_SESSION['user']['role'] ?? 'guest'; }
    public static function isAdmin(): bool  { return self::role() === 'admin'; }
    public static function isStudent(): bool{ return self::role() === 'student'; }
    public static function csrf(): string   { return $_SESSION['csrf'] ?? ''; }

    public static function checkCsrf(?string $token): bool
    {
        return is_string($token) && hash_equals(self::csrf(), $token);
    }

    public static function flash(?string $msg = null): ?string
    {
        if ($msg !== null) { $_SESSION['flash'] = $msg; return null; }
        $out = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $out;
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}
