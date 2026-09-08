<?php
final class Config
{
    private static array $data = [];

    public static function load(): void
    {
        $file = __DIR__ . '/../config/config.php';
        if (!is_file($file)) {
            $file = __DIR__ . '/../config/config.sample.php';
        }
        self::$data = require $file;
        self::$data['app']['base_url'] = self::detectBaseUrl();
    }

    public static function get(string $path, $default = null)
    {
        $node = self::$data;
        foreach (explode('.', $path) as $key) {
            if (!is_array($node) || !array_key_exists($key, $node)) {
                return $default;
            }
            $node = $node[$key];
        }
        return $node;
    }

    /** Works both at a domain root and in a subfolder like /lostfound-php/public. */
    private static function detectBaseUrl(): string
    {
        $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        return rtrim($script, '/');
    }
}
