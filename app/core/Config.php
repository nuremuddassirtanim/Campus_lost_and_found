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
        if (empty(self::$data['app']['base_url'])) {
            self::$data['app']['base_url'] = self::detectBaseUrl();
        }
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

    private static function detectBaseUrl(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '/index.php';
        $base   = rtrim(str_replace('\\', '/', dirname($script)), '/');
        if ($base === '' && !empty($_SERVER['REQUEST_URI'])) {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
            if (preg_match('#^(/.*?/public)(/|$)#', $path, $m)) {
                $base = $m[1];
            }
        }
        return $base === '/' ? '' : $base;
    }
}
