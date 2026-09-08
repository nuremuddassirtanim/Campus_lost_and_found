<section class="center-col">
  <p class="kicker"><?= htmlspecialchars($item['ref']) ?> · <?= htmlspecialchars(ucfirst($item['type'])) ?></p>
  <h2><?= htmlspecialchars($item['title']) ?></h2>

  <dl class="spec">
    <dt>Category</dt><dd><?= htmlspecialchars($item['category']) ?></dd>
    <dt>Location</dt><dd><?= htmlspecialchars($item['location']) ?></dd>
    <dt>Reported</dt><dd><?= date('d M Y, H:i', strtotime($item['created_at'])) ?></dd>
    <dt>Status</dt><dd><span class="tag tag-<?= htmlspecialchars($item['status']) ?>"><?= htmlspecialchars(ucfirst($item['status'])) ?></span></dd>
    <dt>Reported by</dt><dd><?= htmlspecialchars($item['reporter']) ?></dd>
  </dl>

  <p class="body"><?= nl2br(htmlspecialchars($item['description'])) ?></p>

  <?php if ($item['photo']): ?>
    <figure class="duotone blueprint">
      <img src="<?= $base ?>/uploads/<?= htmlspecialchars($item['photo']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
      <i class="corner tl"></i><i class="corner tr"></i><i class="corner bl"></i><i class="corner br"></i>
    </figure>
  <?php endif; ?>

  <?php if ($canClaim): ?>
    <h4>File a claim</h4>
    <?php if ($item['verify_q']): ?>
      <p class="muted small">Verification question: <strong><?= htmlspecialchars($item['verify_q']) ?></strong></p>
    <?php endif; ?>
    <form id="claim-form" class="stack narrow" method="post" action="<?= $base ?>/api/claims">
      <input type="hidden" name="csrf" value="<?= $csrf ?>">
      <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
      <div class="field"><label for="answer">Your answer</label>
        <textarea class="input" id="answer" name="answer" required></textarea></div>
      <div class="field"><label for="contact">Contact (phone or email)</label>
        <input class="input" id="contact" name="contact" required></div>
      <button class="btn btn-primary" type="submit">Submit claim</button>
      <p class="form-msg" data-msg></p>
    </form>
  <?php elseif (!Session::user()): ?>
    <p class="muted">Only registered students can claim. <a href="<?= $base ?>/login">Log in</a> first.</p>
  <?php endif; ?>

  <p><a href="<?= $base ?>/">Back to the feed</a></p>
</section>
