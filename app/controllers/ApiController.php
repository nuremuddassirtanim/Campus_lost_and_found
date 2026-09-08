<?php
/** Every endpoint here answers JSON and is called from public/js/app.js via fetch(). */
final class ApiController extends Controller
{
    public function items(): void
    {
        $this->json(['ok' => true, 'items' => array_map([$this, 'shape'], Item::feed($_GET))]);
    }

    public function createItem(): void
    {
        $user = $this->requireJsonAuth('student');
        $in   = $_POST ?: $this->input();          // FormData when a photo is attached
        $this->requireCsrf($in);

        $res = Item::create((int) $user['id'], $in, $_FILES['photo'] ?? null);
        if (!$res['ok']) { $this->json($res, 422); }

        $this->json(['ok' => true, 'ref' => $res['ref'],
            'message' => 'Report ' . $res['ref'] . ' submitted. It appears in the feed once a moderator approves it.']);
    }

    public function createClaim(): void
    {
        $user = $this->requireJsonAuth('student');
        $in   = $this->input();
        $this->requireCsrf($in);

        $res = Claim::create((int) ($in['item_id'] ?? 0), (int) $user['id'], $in);
        if (!$res['ok']) { $this->json($res, 422); }

        $this->json(['ok' => true, 'message' => 'Claim filed on ' . $res['ref'] . '. The security desk reviews it next.']);
    }

    public function itemStatus(): void
    {
        $this->requireJsonAuth('admin');
        $in = $this->input();
        $this->requireCsrf($in);

        $ok = Item::setStatus((int) ($in['id'] ?? 0), (string) ($in['status'] ?? ''));
        $this->json($ok ? ['ok' => true, 'message' => 'Listing updated.'] : ['ok' => false, 'error' => 'Unknown status.'], $ok ? 200 : 422);
    }

    public function claimStatus(): void
    {
        $this->requireJsonAuth('admin');
        $in = $this->input();
        $this->requireCsrf($in);

        $ok = Claim::setStatus((int) ($in['id'] ?? 0), (string) ($in['status'] ?? ''));
        $this->json($ok ? ['ok' => true, 'message' => 'Claim updated.'] : ['ok' => false, 'error' => 'Unknown status.'], $ok ? 200 : 422);
    }

    public function stats(): void
    {
        $this->json(['ok' => true, 'stats' => Item::stats()]);
    }

    private function shape(array $i): array
    {
        return [
            'id'       => (int) $i['id'],
            'ref'      => $i['ref'],
            'type'     => $i['type'],
            'title'    => $i['title'],
            'category' => $i['category'],
            'location' => $i['location'],
            'status'   => $i['status'],
            'reporter' => $i['reporter'] ?? '',
            'date'     => date('d M Y', strtotime($i['created_at'])),
            'url'      => Config::get('app.base_url') . '/item/' . $i['id'],
        ];
    }
}
