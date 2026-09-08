<?php
final class Claim
{
    public static function create(int $itemId, int $userId, array $in): array
    {
        $answer  = trim((string) ($in['answer'] ?? ''));
        $contact = trim((string) ($in['contact'] ?? ''));
        if ($answer === '' || $contact === '') {
            return ['ok' => false, 'error' => 'Answer the verification question and leave a contact.'];
        }
        $item = Item::find($itemId);
        if (!$item)                        { return ['ok' => false, 'error' => 'That item no longer exists.']; }
        if ((int) $item['user_id'] === $userId) { return ['ok' => false, 'error' => 'You reported this item yourself.']; }
        if (self::exists($itemId, $userId))     { return ['ok' => false, 'error' => 'You have already filed a claim on this item.']; }

        Database::run('INSERT INTO claims (item_id, user_id, answer, contact) VALUES (?,?,?,?)',
            [$itemId, $userId, $answer, $contact]);
        return ['ok' => true, 'id' => (int) Database::pdo()->lastInsertId(), 'ref' => $item['ref']];
    }

    public static function exists(int $itemId, int $userId): bool
    {
        return (bool) Database::run('SELECT id FROM claims WHERE item_id = ? AND user_id = ?', [$itemId, $userId])->fetch();
    }

    public static function byUser(int $userId): array
    {
        return Database::run(
            'SELECT c.*, i.ref, i.title FROM claims c JOIN items i ON i.id = c.item_id
              WHERE c.user_id = ? ORDER BY c.created_at DESC',
            [$userId]
        )->fetchAll();
    }

    public static function pending(): array
    {
        return Database::run(
            "SELECT c.*, i.ref, i.title, i.verify_q, u.name AS claimant, u.student_id
               FROM claims c JOIN items i ON i.id = c.item_id JOIN users u ON u.id = c.user_id
              WHERE c.status = 'pending' ORDER BY c.created_at ASC"
        )->fetchAll();
    }

    /** Approving a claim also marks the item matched. */
    public static function setStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['pending', 'approved', 'declined'], true)) { return false; }
        Database::run('UPDATE claims SET status = ? WHERE id = ?', [$status, $id]);
        if ($status === 'approved') {
            $row = Database::run('SELECT item_id FROM claims WHERE id = ?', [$id])->fetch();
            if ($row) { Item::setStatus((int) $row['item_id'], 'matched'); }
        }
        return true;
    }
}
