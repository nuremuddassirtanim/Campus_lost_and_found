<?php
abstract class Controller
{
    /** Render a view inside the layout. */
    protected function view(string $template, array $data = [], string $layout = 'layout'): void
    {
        $data['user']  = Session::user();
        $data['csrf']  = Session::csrf();
        $data['base']  = Config::get('app.base_url');
        $data['flash'] = Session::flash();

        extract($data, EXTR_SKIP);
        ob_start();
        require __DIR__ . '/../views/' . $template . '.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/' . $layout . '.php';
    }

    protected function json($payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Body of an AJAX request: JSON payload or classic form post. */
    protected function input(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw !== '' && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'json')) {
            return json_decode($raw, true) ?: [];
        }
        return $_POST;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . Config::get('app.base_url') . $path);
        exit;
    }

    protected function requireLogin(): void
    {
        if (!Session::user()) { $this->redirect('/login'); }
    }

    protected function requireAdmin(): void
    {
        if (!Session::isAdmin()) { $this->redirect('/login'); }
    }

    protected function requireJsonAuth(?string $role = null): array
    {
        $user = Session::user();
        if (!$user) { $this->json(['ok' => false, 'error' => 'Log in first.'], 401); }
        if ($role && $user['role'] !== $role) { $this->json(['ok' => false, 'error' => 'Not allowed.'], 403); }
        return $user;
    }

    protected function requireCsrf(array $in): void
    {
        if (!Session::checkCsrf($in['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null))) {
            $this->json(['ok' => false, 'error' => 'Session expired. Reload the page.'], 419);
        }
    }
}
