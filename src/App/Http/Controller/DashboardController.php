<?php
declare(strict_types=1);

namespace Ledgerly\App\Http\Controller;

use Ledgerly\Infrastructure\Route;
use Ledgerly\App\Http\Controller\Controller;
use Ledgerly\Kernel\Container;

class DashboardController 
{
    public function __construct(public UserController $user)
    {
        
    }

    #[Route('/dashboard', name: 'dashboard.index')]
    public function index()
    {
        echo "You are in the dashboard";
        //(new Container()->get(UserController::class)->getAvatar('Bob'));
        //return $this->user->getAvatar('Jack');
    }

    #[Route('/dashboard/show/{id}', name: 'dashboard.show')]
    public function show(int $id)
    {
        echo "This is the show method $id and";
    }

    #[Route('/dashboard/create', name: 'dashboard.create')]
    public function create()
    {
        echo "Here is create method";
    }

    #[Route('/dashboard/{id}/delete', name: 'dashboard.delete')]
    public function destroy(int $id)
    {
        echo "Item $id was destroyed";
    }

    #[Route('/dashboard/store', name: 'dashboard.store')]
    public function store()
    {
        echo "Here is a store method";
    }
}