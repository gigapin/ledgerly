<?php
declare(strict_types=1);

namespace Ledgerly\App\Http\Controller;

use Ledgerly\Infrastructure\Route;

class DashboardController 
{
    #[Route('/dashboard', name: 'dashboard.index')]
    public function index()
    {
        echo "You are in the dashboard";
    }

    #[Route('/dashboard/show/{id}', name: 'dashboard.show')]
    public function show(int $id)
    {
        echo "This is the show method $id";
    }

    #[Route('/dashboard/create', name: 'dashboard.create')]
    public function create()
    {
        echo "Here is create method";
    }
}