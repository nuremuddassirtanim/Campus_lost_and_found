<section class="center-col narrow">
  <h2 class="center">Report a lost or found item</h2>

  <?php if (!Session::isStudent()): ?>
    <p class="muted center">Only students can post. <a href="<?= $base ?>/login">Log in</a> first.</p>
  <?php else: ?>
    <form id="report-form" class="stack" method="post" action="<?= $base ?>/api/items" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= $csrf ?>">
      <div class="seg">
        <label class="seg-opt"><input type="radio" name="type" value="lost" checked> I lost this</label>
        <label class="seg-opt"><input type="radio" name="type" value="found"> I found this</label>
      </div>
      <div class="field"><label for="title">Item title</label>
        <input class="input" id="title" name="title" required placeholder="Any item name"></div>
      <div class="field"><label for="category">Category</label>
        <select class="input" id="category" name="category" required>
          <?php foreach ($categories as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="field"><label for="location">Location</label>
        <input class="input" id="location" name="location" required placeholder="Annex 2, Room 4021"></div>
      <div class="field"><label for="description">Description</label>
        <textarea class="input" id="description" name="description" required placeholder="Anything identifying"></textarea></div>
      <div class="field"><label for="verify_q">Verification question</label>
        <input class="input" id="verify_q" name="verify_q" placeholder="What is written on the back?"></div>
      <div class="field"><label for="photo">Photo (optional)</label>
        <input class="input" id="photo" name="photo" type="file" accept="image/*"></div>
      <button class="btn btn-primary btn-block" type="submit">Submit report</button>
      <p class="form-msg" data-msg></p>
    </form>
  <?php endif; ?>
</section>
