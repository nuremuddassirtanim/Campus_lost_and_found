<?php
final class Item
{
    public const CATEGORIES = ['Electronics', 'ID & cards', 'Books & notes', 'Bags', 'Keys', 'Clothing', 'Other'];

    /** Public feed with optional search / filters — used by the page and by GET /api/items. */
    public static function feed(array $q = []): array
    {
        $sql    = 'SELECT i.*, u.name AS reporter FROM items i JOIN users u ON u.id = i.user_id WHERE i.status IN (\'open\',\'matched\',\'returned\')';
        $params = [];

        if (!empty($q['type']) && in_array($q['type'], ['lost', 'found'], true)) {
            $sql .= ' AND i.type = ?';       $params[] = $q['type'];
        }
        if (!empty($q['category']) && $q['category'] !== 'All') {
            $sql .= ' AND i.category = ?';   $params[] = $q['category'];
        }
        if (!empty($q['search'])) {
            $sql .= ' AND (i.title LIKE ? OR i.description LIKE ? OR i.location LIKE ? OR i.ref LIKE ?)';
            $like = '%' . $q['search'] . '%';
            array_push($params, $like, $like, $like, $like);
        }
        $sql .= ($q['sort'] ?? 'new') === 'old' ? ' ORDER BY i.created_at ASC' : ' ORDER BY i.created_at DESC';
        $sql .= ' LIMIT 60';

        return Database::run($sql, $params)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $row = Database::run(
            'SELECT i.*, u.name AS reporter, u.email AS reporter_email
               FROM items i JOIN users u ON u.id = i.user_id WHERE i.id = ? LIMIT 1',
            [$id]
        )->fetch();
        return $row ?: null;
    }

    public static function byUser(int $userId): array
    {
        return Database::run('SELECT * FROM items WHERE user_id = ? ORDER BY created_at DESC', [$userId])->fetchAll();
    }

    public static function pending(): array
    {
        return Database::run(
            'SELECT i.*, u.name AS reporter FROM items i JOIN users u ON u.id = i.user_id
              WHERE i.status = \'pending\' ORDER BY i.created_at ASC'
        )->fetchAll();
    }

    public static function create(int $userId, array $in, ?array $file = null): array
    {
        $type = ($in['type'] ?? 'lost') === 'found' ? 'found' : 'lost';
        foreach (['title', 'category', 'location', 'description'] as $field) {
            if (trim((string) ($in[$field] ?? '')) === '') {
                return ['ok' => false, 'error' => 'Title, category, location and description are required.'];
            }
        }
        if (!in_array($in['category'], self::CATEGORIES, true)) {
            return ['ok' => false, 'error' => 'Pick a category from the list.'];
        }

        $photo = $file ? self::storePhoto($file) : null;
        $ref   = self::nextRef();

        Database::run(
            'INSERT INTO items (ref, user_id, type, title, category, location, description, verify_q, photo, status)
             VALUES (?,?,?,?,?,?,?,?,?,\'pending\')',
            [$ref, $userId, $type, trim($in['title']), $in['category'], trim($in['location']),
             trim($in['description']), trim((string) ($in['verify_q'] ?? '')) ?: null, $photo]
        );
        return ['ok' => true, 'id' => (int) Database::pdo()->lastInsertId(), 'ref' => $ref];
    }

    public static function setStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['pending', 'open', 'matched', 'returned', 'rejected'], true)) { return false; }
        Database::run('UPDATE items SET status = ? WHERE id = ?', [$status, $id]);
        return true;
    }

    public static function stats(): array
    {
        $row = Database::run(
            "SELECT
               SUM(status IN ('open','matched'))              AS open_items,
               SUM(status = 'returned')                        AS returned_items,
               SUM(status = 'pending')                         AS pending_items,
               (SELECT COUNT(*) FROM claims WHERE status='pending') AS pending_claims
             FROM items"
        )->fetch() ?: [];
        return array_map(static fn($v) => (int) $v, $row);
    }

    private static function nextRef(): string
    {
        $n = (int) Database::run('SELECT COUNT(*) AS c FROM items')->fetch()['c'];
        return sprintf('LF-%04d', $n + 1);
    }

    private static function storePhoto(array $file): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) { return null; }
        $dir = Config::get('app.upload_dir');
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) { return null; }
        $name = 'item-' . bin2hex(random_bytes(6)) . '.' . $ext;
        return move_uploaded_file($file['tmp_name'], $dir . '/' . $name) ? $name : null;
    }
}
