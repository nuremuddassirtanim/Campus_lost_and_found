<?php
final class User
{
    public static function findByEmail(string $email): ?array
    {
        $row = Database::run('SELECT * FROM users WHERE email = ? LIMIT 1', [strtolower(trim($email))])->fetch();
        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $row = Database::run('SELECT * FROM users WHERE id = ? LIMIT 1', [$id])->fetch();
        return $row ?: null;
    }

    /** @return array{ok:bool,error?:string,id?:int} */
    public static function register(array $in): array
    {
        $name  = trim($in['name'] ?? '');
        $sid   = trim($in['student_id'] ?? '');
        $email = strtolower(trim($in['email'] ?? ''));
        $pass  = (string) ($in['password'] ?? '');

        if ($name === '' || $sid === '' || $email === '' || $pass === '') {
            return ['ok' => false, 'error' => 'Name, student ID, email and password are all required.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'That email address is not valid.'];
        }
        if (strlen($pass) < 6) {
            return ['ok' => false, 'error' => 'Use a password of at least 6 characters.'];
        }
        if (($in['password_confirm'] ?? $pass) !== $pass) {
            return ['ok' => false, 'error' => 'The two passwords do not match.'];
        }
        if (self::findByEmail($email)) {
            return ['ok' => false, 'error' => 'An account already exists for that email. Log in instead.'];
        }

        Database::run(
            'INSERT INTO users (name, student_id, email, password_hash, role) VALUES (?,?,?,?,\'student\')',
            [$name, $sid, $email, password_hash($pass, PASSWORD_DEFAULT)]
        );
        return ['ok' => true, 'id' => (int) Database::pdo()->lastInsertId()];
    }

    public static function verify(string $email, string $password): ?array
    {
        $user = self::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }
        return $user;
    }

    // ── "remember me" cookie: selector:validator, validator hashed at rest ──

    public static function issueRememberToken(int $userId, int $days): string
    {
        $selector  = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(32));
        Database::run(
            'INSERT INTO remember_tokens (user_id, selector, validator, expires_at) VALUES (?,?,?,?)',
            [$userId, $selector, hash('sha256', $validator), date('Y-m-d H:i:s', time() + $days * 86400)]
        );
        return $selector . ':' . $validator;
    }

    public static function userForRememberCookie(string $cookie): ?array
    {
        if (!str_contains($cookie, ':')) { return null; }
        [$selector, $validator] = explode(':', $cookie, 2);
        $row = Database::run(
            'SELECT * FROM remember_tokens WHERE selector = ? AND expires_at > NOW() LIMIT 1',
            [$selector]
        )->fetch();
        if (!$row || !hash_equals($row['validator'], hash('sha256', $validator))) { return null; }
        return self::find((int) $row['user_id']);
    }

    public static function clearRememberTokens(int $userId): void
    {
        Database::run('DELETE FROM remember_tokens WHERE user_id = ?', [$userId]);
    }
}
