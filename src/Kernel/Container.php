<?php 

declare(strict_types=1);

namespace Ledgerly\Kernel;

use Override;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Ledgerly\Exception\ContainerException;
use ReflectionParameter;
use ReflectionUnionType;
use ReflectionNamedType;

class Container implements ContainerInterface
{
    public array $instance = [];
    
    #[Override]
    public function get(string $id)
    {
        if ($this->has($id)) {
            var_dump('ID HAS', $id);
            $obj = $this->instance[$id];

            return $obj($this);
        }
       
        return $this->resolve($id);
    }

    #[Override]
    public function has(string $id): bool
    {
        return isset($this->instance[$id]);
    }

    public function set(string $id, callable $concrete): void
    {
        $this->instance[$id] = $concrete;
    }

    public function resolve(string $id)
    {
        //var_dump('RESOLVE ',$id);
        // Inspect the class that we trying to get from container
        $reflection = new ReflectionClass($id);

        if (! $reflection->isInstantiable()) {
            throw new ContainerException("Class " . $id . " is not instantiable");
        }

        // Inspect the constructor of the class
        $constructor = $reflection->getConstructor();

        if (! $constructor) {
            return new $id;
        }

        //Inspect the parameters of the class(dependencies)
        $parameters = $constructor->getParameters();

        if (! $parameters) {
            return new $id;
        }

        // If the constructor parameter is a class trying to resolve it using the container
        $dependencies = array_map(function(ReflectionParameter $param) {
            $name = $param->getName();
            $type = $param->getType();
            //var_dump($type->getName());
            if (! $type) {
                throw new ContainerException('Type missing');
            }

            if ($type instanceof ReflectionUnionType) {
                throw new ContainerException("Failed resolve class for union type");
            }

            if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
                return $this->get($type->getName());
            }

            throw new ContainerException("Failed resolve class for invalid param");
        }, $parameters);
        
        return $reflection->newInstanceArgs($dependencies);
    }
}