<?php
final class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('items/feed', [
            'title'      => 'Campus feed',
            'items'      => Item::feed(['search' => $_GET['search'] ?? '', 'type' => $_GET['type'] ?? '']),
            'categories' => Item::CATEGORIES,
            'stats'      => Item::stats(),
        ]);
    }

    public function notFound(): void
    {
        $this->view('404', ['title' => 'Not found']);
    }
}
