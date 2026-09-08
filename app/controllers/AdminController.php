<?php
final class AdminController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $this->view('admin/index', [
            'title'   => 'Moderation',
            'pending' => Item::pending(),
            'claims'  => Claim::pending(),
            'stats'   => Item::stats(),
        ]);
    }
}
