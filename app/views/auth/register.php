<section class="center-col narrow">
  <h2 class="center">Register</h2>
  <p class="muted center">Students only. After registering, log in with your email and password.</p>

  <form id="register-form" class="stack" method="post" action="<?= $base ?>/api/auth/register" novalidate>
    <input type="hidden" name="csrf" value="<?= $csrf ?>">
    <div class="field"><label for="name">Full name</label>
      <input class="input" id="name" name="name" required placeholder="as printed on your ID card"></div>
    <div class="field"><label for="student_id">Student ID</label>
      <input class="input" id="student_id" name="student_id" required placeholder="23-52508-2"></div>
    <div class="field"><label for="remail">AIUB email</label>
      <input class="input" id="remail" name="email" type="email" required placeholder="you@student.aiub.edu"></div>
    <div class="field"><label for="rpass">Password</label>
      <input class="input" id="rpass" name="password" type="password" required minlength="6"></div>
    <div class="field"><label for="rpass2">Confirm password</label>
      <input class="input" id="rpass2" name="password_confirm" type="password" required></div>
    <button class="btn btn-primary btn-block" type="submit">Create account</button>
    <p class="form-msg" data-msg></p>
  </form>

  <p class="center small">Already registered? <a href="<?= $base ?>/login">Log in</a>.</p>
</section>
