<section>
  <h1 class="hero">Lost something on campus? Start here.</h1>

  <div class="stats" id="stats">
    <div class="stat blueprint"><b><?= $stats['open_items'] ?? 0 ?></b><span>Open listings</span><i class="corner tl"></i><i class="corner tr"></i><i class="corner bl"></i><i class="corner br"></i></div>
    <div class="stat blueprint"><b><?= $stats['pending_claims'] ?? 0 ?></b><span>Claims in review</span><i class="corner tl"></i><i class="corner tr"></i><i class="corner bl"></i><i class="corner br"></i></div>
    <div class="stat blueprint"><b><?= $stats['returned_items'] ?? 0 ?></b><span>Items returned</span><i class="corner tl"></i><i class="corner tr"></i><i class="corner bl"></i><i class="corner br"></i></div>
  </div>

  <form class="filters" id="feed-filters" onsubmit="return false">
    <input class="input" type="search" name="search" data-filter placeholder="Search title, place or ref">
    <select class="input" name="type" data-filter>
      <option value="">Lost and found</option>
      <option value="lost">Lost only</option>
      <option value="found">Found only</option>
    </select>
    <select class="input" name="category" data-filter>
      <option value="All">All categories</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
      <?php endforeach; ?>
    </select>  </form>

  <div class="grid" id="feed">
    <?php foreach ($items as $i): ?>
      <article class="card blueprint">
        <p class="kicker"><?= htmlspecialchars(ucfirst($i['type'])) ?> · <?= htmlspecialchars($i['category']) ?></p>
        <h3><?= htmlspecialchars($i['title']) ?></h3>
        <p class="muted small"><?= htmlspecialchars($i['location']) ?> · <?= date('d M Y', strtotime($i['created_at'])) ?></p>
        <div class="card-foot">
          <span class="tag tag-<?= htmlspecialchars($i['status']) ?>"><?= htmlspecialchars(ucfirst($i['status'])) ?></span>
          <a class="btn btn-ghost" href="<?= $base ?>/item/<?= (int) $i['id'] ?>">View details</a>
        </div>
        <i class="corner tl"></i><i class="corner tr"></i><i class="corner bl"></i><i class="corner br"></i>
      </article>
    <?php endforeach; ?>
    <?php if (!$items): ?><p class="muted">Nothing listed yet.</p><?php endif; ?>
  </div>
</section>
