<?php /** @var string $content */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars(($title ?? 'Home') . ' · ' . Config::get('app.name')) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $base ?>/css/style.css">
<script>window.LF = { base: <?= json_encode($base) ?>, csrf: <?= json_encode($csrf) ?>, role: <?= json_encode(Session::role()) ?> };</script>
</head>
<body>

<header class="nav">
  <a class="nav-brand" href="<?= $base ?>/">AIUB Lost &amp; Found</a>
  <nav class="nav-links">
    <a href="<?= $base ?>/">Feed</a>
    <a href="<?= $base ?>/report">Report an item</a>
    <?php if (Session::user()): ?>
      <a href="<?= $base ?>/mine">My dashboard</a>
    <?php endif; ?>
    <?php if (Session::isAdmin()): ?>
      <a href="<?= $base ?>/admin">Moderation</a>
    <?php endif; ?>
  </nav>
  <div class="nav-right">
    <?php if ($u = Session::user()): ?>
      <span class="tag tag-outline"><?= htmlspecialchars($u['role'] === 'admin' ? 'Admin' : $u['name']) ?></span>
      <form method="post" action="<?= $base ?>/logout" class="inline">
        <input type="hidden" name="csrf" value="<?= $csrf ?>">
        <button class="btn btn-secondary" type="submit">Log out</button>
      </form>
    <?php else: ?>
      <a class="btn btn-secondary" href="<?= $base ?>/register">Register</a>
      <a class="btn btn-primary" href="<?= $base ?>/login">Log in</a>
    <?php endif; ?>
  </div>
</header>

<?php if (!empty($flash)): ?>
  <p class="notice" role="status"><?= htmlspecialchars($flash) ?></p>
<?php endif; ?>

<main class="wrap"><?= $content ?></main>

<footer class="foot">
  <span class="foot-brand">AIUB Lost &amp; Found</span>
  <span>@2026, All copyright reserved by NUR E MUDDASSIR TANIM</span>
</footer>

<script src="<?= $base ?>/js/app.js"></script>
</body>
</html>
