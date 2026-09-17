<?php
declare(strict_types=1);

namespace Ledgerly\Kernel;

/**
 * Il router deve utilizzare gli attributi di php
 * richiamabili con #[Route(<path>)]
 * A seguire ci sono una serie di prametri che si possono specificare
 * quali l'http method, il route name e l'environment
 */

class Router
{
    public function __construct()
    {
        echo "Hello World PHP Power";
    }
}