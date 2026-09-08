<?php
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        $c   = Config::get('db');
        $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset={$c['charset']}";
        try {
            self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            $hint = Config::get('app.debug') ? $e->getMessage() : 'Database unavailable.';
            exit('<h1>Database connection failed</h1><p>' . htmlspecialchars($hint) . '</p>'
               . '<p>Check <code>app/config/config.php</code> and that the schema was imported in phpMyAdmin.</p>');
        }
        return self::$pdo;
    }

    /** Prepared statement helper: Database::run('SELECT … WHERE id = ?', [$id]) */
    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
