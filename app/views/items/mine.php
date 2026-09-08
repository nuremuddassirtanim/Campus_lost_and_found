<section>
  <h2><?= htmlspecialchars($user['name']) ?></h2>
  <p class="muted small">Signed in as <?= htmlspecialchars($user['email']) ?> · session cookie <code><?= Config::get('app.session_name') ?></code></p>

  <h4>My reports</h4>
  <table class="table">
    <thead><tr><th>Ref</th><th>Item</th><th>Type</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($items as $i): ?>
        <tr>
          <td><a href="<?= $base ?>/item/<?= (int) $i['id'] ?>"><?= htmlspecialchars($i['ref']) ?></a></td>
          <td><?= htmlspecialchars($i['title']) ?></td>
          <td><?= htmlspecialchars(ucfirst($i['type'])) ?></td>
          <td><span class="tag tag-<?= htmlspecialchars($i['status']) ?>"><?= htmlspecialchars(ucfirst($i['status'])) ?></span></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?><tr><td colspan="4" class="muted">No reports yet.</td></tr><?php endif; ?>
    </tbody>
  </table>

  <h4>My claims</h4>
  <table class="table">
    <thead><tr><th>Ref</th><th>Item</th><th>Filed</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($claims as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['ref']) ?></td>
          <td><?= htmlspecialchars($c['title']) ?></td>
          <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
          <td><span class="tag tag-<?= htmlspecialchars($c['status']) ?>"><?= htmlspecialchars(ucfirst($c['status'])) ?></span></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$claims): ?><tr><td colspan="4" class="muted">No claims yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>
