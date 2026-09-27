<?php 
declare(strict_types=1);

namespace Ledgerly\App\Http\Controller;

use Ledgerly\Kernel\Container;

class Controller
{
    public static Container $container;

    public function __construct()
    {
        static::$container = new Container();
    }
}