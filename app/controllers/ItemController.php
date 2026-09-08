<?php
final class ItemController extends Controller
{
    public function show(array $p): void
    {
        $item = Item::find((int) $p['id']);
        if (!$item) { (new HomeController())->notFound(); return; }

        $this->view('items/show', [
            'title'    => $item['ref'] . ' · ' . $item['title'],
            'item'     => $item,
            'canClaim' => Session::isStudent()
                          && (int) $item['user_id'] !== Session::id()
                          && !Claim::exists((int) $item['id'], (int) Session::id()),
        ]);
    }

    public function create(): void
    {
        $this->view('items/create', [
            'title'      => 'Report an item',
            'categories' => Item::CATEGORIES,
        ]);
    }

    public function mine(): void
    {
        $this->requireLogin();
        $this->view('items/mine', [
            'title'  => 'My dashboard',
            'items'  => Item::byUser((int) Session::id()),
            'claims' => Claim::byUser((int) Session::id()),
        ]);
    }
}
