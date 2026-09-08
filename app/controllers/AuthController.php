<?php
final class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Session::user()) { $this->redirect('/'); }
        $this->view('auth/login', ['title' => 'Log in']);
    }

    public function registerForm(): void
    {
        if (Session::user()) { $this->redirect('/'); }
        $this->view('auth/register', ['title' => 'Register']);
    }

    /** POST /api/auth/login — called by AJAX, also works as a plain form post. */
    public function login(): void
    {
        $in = $this->input();
        $this->requireCsrf($in);

        $user = User::verify((string) ($in['email'] ?? ''), (string) ($in['password'] ?? ''));
        if (!$user) {
            $this->json(['ok' => false, 'error' => 'No account matches that email and password. Register first if you are new.'], 401);
        }

        Session::login($user);

        if (!empty($in['remember'])) {
            $days = (int) Config::get('app.remember_days', 14);
            setcookie('lf_remember', User::issueRememberToken((int) $user['id'], $days), [
                'expires'  => time() + $days * 86400,
                'path'     => Config::get('app.base_url') . '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure'   => !empty($_SERVER['HTTPS']),
            ]);
        }

        $this->json([
            'ok'   => true,
            'user' => ['name' => $user['name'], 'role' => $user['role']],
            'next' => $user['role'] === 'admin' ? '/admin' : '/mine',
        ]);
    }

    /** POST /api/auth/register — students only; sends them to the login screen after. */
    public function register(): void
    {
        $in = $this->input();
        $this->requireCsrf($in);

        $res = User::register($in);
        if (!$res['ok']) { $this->json($res, 422); }

        $this->json(['ok' => true, 'message' => 'Account created. Log in to continue.', 'next' => '/login']);
    }

    public function logout(): void
    {
        if ($id = Session::id()) { User::clearRememberTokens($id); }
        setcookie('lf_remember', '', [
            'expires' => time() - 3600,
            'path'    => Config::get('app.base_url') . '/',
        ]);
        Session::destroy();
        $this->redirect('/');
    }
}
