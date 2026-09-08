<?php
final class Router
{
    private array $routes = [];

    public function get(string $pattern, string $handler): void  { $this->add('GET', $pattern, $handler); }
    public function post(string $pattern, string $handler): void { $this->add('POST', $pattern, $handler); }

    private function add(string $method, string $pattern, string $handler): void
    {
        $this->routes[] = ['method' => $method, 'pattern' => $pattern, 'handler' => $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = '/' . trim(parse_url($uri, PHP_URL_PATH) ?? '', '/');
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) { continue; }
            $regex = '#^' . preg_replace('#\\{(\\w+)\\}#', '(?P<$1>[^/]+)', $route['pattern']) . '/?$#';
            if (preg_match($regex, $uri, $m)) {
                $params = [];
                foreach ($m as $k => $v) { if (!is_int($k)) { $params[$k] = $v; } }
                [$class, $action] = explode('@', $route['handler']);
                (new $class())->$action($params);
                return;
            }
        }
        http_response_code(404);
        (new HomeController())->notFound();
    }
}
