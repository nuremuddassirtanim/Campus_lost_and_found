<?php
/**
 * One-time setup: writes a real bcrypt hash for the admin account.
 * Visit once after importing database/schema.sql, then DELETE this file.
 *
 *   http://localhost/lostfound-php/public/setup.php?password=admin123
 */
declare(strict_types=1);

require __DIR__ . '/../app/core/Config.php';
require __DIR__ . '/../app/core/Database.php';
Config::load();

$password = $_GET['password'] ?? 'admin123';
$hash     = password_hash($password, PASSWORD_DEFAULT);

Database::run(
    "UPDATE users SET password_hash = ? WHERE email = 'admin@aiub.edu'",
    [$hash]
);

$rows = Database::run('SELECT COUNT(*) AS c FROM users')->fetch()['c'];

header('Content-Type: text/html; charset=utf-8');
echo '<h1>Setup complete</h1>';
echo '<p>Admin password set to <code>' . htmlspecialchars($password) . '</code> for <code>admin@aiub.edu</code>.</p>';
echo '<p>Users in database: ' . (int) $rows . '</p>';
echo '<p><strong>Delete public/setup.php now</strong>, then <a href="./login">log in</a>.</p>';
