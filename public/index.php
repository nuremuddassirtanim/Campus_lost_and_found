<?php
declare(strict_types=1);

require __DIR__ . '/../app/core/Config.php';
Config::load();
ini_set('display_errors', Config::get('app.debug') ? '1' : '0');
error_reporting(E_ALL);

spl_autoload_register(static function (string $class): void {
    foreach (['core', 'models', 'controllers'] as $dir) {
        $file = __DIR__ . '/../app/' . $dir . '/' . $class . '.php';
        if (is_file($file)) { require $file; return; }
    }
});

Session::start();

// Log the user back in from the "remember me" cookie if the session is gone.
if (!Session::user() && !empty($_COOKIE['lf_remember'])) {
    if ($u = User::userForRememberCookie($_COOKIE['lf_remember'])) {
        Session::login($u);
    }
}

$router = new Router();

$router->get('/','HomeController@index');
$router->get('/item/{id}',           'ItemController@show');
$router->get('/report',              'ItemController@create');
$router->get('/mine',                'ItemController@mine');
$router->get('/login',               'AuthController@loginForm');
$router->get('/register',            'AuthController@registerForm');
$router->get('/admin',               'AdminController@index');

$router->post('/logout',             'AuthController@logout');
$router->post('/api/auth/login',     'AuthController@login');
$router->post('/api/auth/register',  'AuthController@register');
$router->get('/api/items',           'ApiController@items');
$router->post('/api/items',          'ApiController@createItem');
$router->post('/api/claims',         'ApiController@createClaim');
$router->get('/api/stats',           'ApiController@stats');
$router->post('/api/admin/item-status',  'ApiController@itemStatus');
$router->post('/api/admin/claim-status', 'ApiController@claimStatus');

$uri = $_GET['url'] ?? substr($_SERVER['REQUEST_URI'], strlen(Config::get('app.base_url')));
$router->dispatch($_SERVER['REQUEST_METHOD'], $uri ?: '/');