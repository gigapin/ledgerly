<?php 
declare(strict_types=1);

namespace Ledgerly\Infrastructure;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    public function __construct(
        public string $path,
        public ?string $name,
        public ?array $method = ['GET'],
        public ?string $env = 'dev'
    ){}
}