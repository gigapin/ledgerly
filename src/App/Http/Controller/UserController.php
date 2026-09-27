<?php 
declare(strict_types=1);

namespace Ledgerly\App\Http\Controller;

use Ledgerly\Infrastructure\Route;

class UserController
{
    public string $role = "admin";
    
    #[Route('/user', name: 'users.index')]
    public function index()
    {
        echo "Rhis is users index page";
    }

    #[Route('/user/{user}/avatar', name: 'users.avatar')]
    public function getAvatar(string $user)
    {
        echo "The avatar is called $user";
    }

    #[Route('/user/show/{id}', name: 'users.show')]
    public function show(string $id)
    { 
        echo "User $id";
    }
}