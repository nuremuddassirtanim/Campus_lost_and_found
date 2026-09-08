<section>
  <h2>Moderation</h2>
  <div class="stats">
    <div class="stat blueprint"><b><?= $stats['pending_items'] ?? 0 ?></b><span>Reports awaiting approval</span><i class="corner tl"></i><i class="corner tr"></i><i class="corner bl"></i><i class="corner br"></i></div>
    <div class="stat blueprint"><b><?= $stats['pending_claims'] ?? 0 ?></b><span>Claims in review</span><i class="corner tl"></i><i class="corner tr"></i><i class="corner bl"></i><i class="corner br"></i></div>
    <div class="stat blueprint"><b><?= $stats['returned_items'] ?? 0 ?></b><span>Items returned</span><i class="corner tl"></i><i class="corner tr"></i><i class="corner bl"></i><i class="corner br"></i></div>
  </div>

  <h4>Reports awaiting approval</h4>
  <table class="table" id="pending-items">
    <thead><tr><th>Ref</th><th>Item</th><th>Reporter</th><th>Decision</th></tr></thead>
    <tbody>
      <?php foreach ($pending as $i): ?>
        <tr data-row>
          <td><?= htmlspecialchars($i['ref']) ?></td>
          <td><?= htmlspecialchars($i['title']) ?> <span class="muted small"><?= htmlspecialchars($i['location']) ?></span></td>
          <td><?= htmlspecialchars($i['reporter']) ?></td>
          <td class="actions">
            <button class="btn btn-primary" data-item-status="open" data-id="<?= (int) $i['id'] ?>">Approve</button>
            <button class="btn btn-secondary" data-item-status="rejected" data-id="<?= (int) $i['id'] ?>">Reject</button>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$pending): ?><tr><td colspan="4" class="muted">Queue is clear.</td></tr><?php endif; ?>
    </tbody>
  </table>

  <h4>Claims in review</h4>
  <table class="table" id="pending-claims">
    <thead><tr><th>Ref</th><th>Claimant</th><th>Answer</th><th>Decision</th></tr></thead>
    <tbody>
      <?php foreach ($claims as $c): ?>
        <tr data-row>
          <td><?= htmlspecialchars($c['ref']) ?></td>
          <td><?= htmlspecialchars($c['claimant']) ?> <span class="muted small"><?= htmlspecialchars((string) $c['student_id']) ?></span></td>
          <td><?= htmlspecialchars($c['answer']) ?></td>
          <td class="actions">
            <button class="btn btn-primary" data-claim-status="approved" data-id="<?= (int) $c['id'] ?>">Approve</button>
            <button class="btn btn-secondary" data-claim-status="declined" data-id="<?= (int) $c['id'] ?>">Decline</button>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$claims): ?><tr><td colspan="4" class="muted">No claims waiting.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>
