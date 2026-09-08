<section class="center-col narrow">
  <h2 class="center">Log in</h2>
  <p class="muted center">Students log in with the account they registered. Staff use the admin account.</p>

  <form id="login-form" class="stack" method="post" action="<?= $base ?>/api/auth/login" novalidate>
    <input type="hidden" name="csrf" value="<?= $csrf ?>">
    <div class="field"><label for="email">AIUB email</label>
      <input class="input" id="email" name="email" type="email" required placeholder="you@student.aiub.edu"></div>
    <div class="field"><label for="password">Password</label>
      <input class="input" id="password" name="password" type="password" required></div>
    <label class="check"><input type="checkbox" name="remember" value="1"> Keep me logged in on this device (14-day cookie)</label>
    <button class="btn btn-primary btn-block" type="submit">Log in</button>
    <p class="form-msg" data-msg></p>
  </form>

  <p class="center small">No student account yet? <a href="<?= $base ?>/register">Register</a> first.</p>

  <div class="demo">
    <p class="small muted">Demo account</p>
    <p class="small"><code>admin@aiub.edu</code> / <code>admin123</code> — staff moderation view.</p>
  </div>
</section>
