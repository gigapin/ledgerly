<?php
declare(strict_types=1);

namespace Ledgerly\Kernel;

use InvalidArgumentException;
use Ledgerly\Infrastructure\Route;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use Ledgerly\Kernel\Container;
use Ledgerly\Exception\ContainerException;
use ReflectionParameter;
use ReflectionUnionType;


class Router
{
    public string $valueUriParam = "";
    private array $routes = [];

    public function __construct(public Container $container)
    {
        $this->getAttributesFromReflection();
        $this->dispatch();
    }

    protected function getAttributesFromReflection()
    {
        // Itera tutti i metodi esposti dalla classe
        foreach ($this->setReflectionClass()->getMethods() as $method) {
            // Itera tutti i valori restituiti dagli attributi associati ai metodi
            foreach ($method->getAttributes(Route::class) as $params) {
                // crea instanza e restituisce tutti i valori degli attributi
                $this->routes[] = ['route' => $params->newInstance(), 'methodName' => $method->name];
            }
        }
 
        return $this->routes;
    }

    protected function dispatch()
    {
        for ($i = 0; $i < count($this->routes); $i++) {
            if ($this->uri() === $this->routes[$i]['route']->path) {
                return $this->getMethodWithoutParams($this->routes[$i]['methodName']);
            } 
        }

        for ($i = 0; $i < count($this->routes); $i++) {
            $pathRoute = $this->getMethodWithParams($this->routes[$i]['route']->path, $this->routes[$i]['methodName']);
            
            if (isset($pathRoute['path']) && $this->uri() === $pathRoute['path']) {
                $this->invokeMethodWithArguments($pathRoute['param'], $this->routes[$i]['methodName']);
            }
            
        }
    }

    protected function uri(): string 
    {
        return $_SERVER['REQUEST_URI'];
    }

    protected function getClassName(): string
    {
        $firstSegment =  explode("/", $this->uri());
        // aggiunge la prima lettera maiuscola e il suffisso Controller 
        // per creare il nome della classe da associare all'uri
        $className = ucfirst($firstSegment[1]) . "Controller";

        // crea il path per fare l'import corretto
        return "Ledgerly\App\Http\Controller\\" . $className;
    }

    protected function setReflectionClass(): ReflectionClass
    {
        $refClass = $this->getClassName();
        // Istanzia la classe ReflectionClass e gli passa come argomento 
        // il nome della classe ottenuto
        return new ReflectionClass($refClass);
    }

    protected function getMethodWithoutParams(string $method)
    {
        // crea una nuova istanza di reflection
        $instance = $this->container->get($this->getClassName());
      
        // ottiene il metodo che si lega al path
        $obj = $this->setReflectionClass()->getMethod($method);
        $obj->invoke($instance);
    }

    protected function getMethodWithParams(string $route, string $method)
    {
        try {
            $firstSegment =  explode("/", $this->uri());
    
            // Regex per verificare se nel path della rotta c'è un valore
            // espresso all'interno di graffe che sta ad indicare un parametro
            $pattern = "/\{[a-z]*\}/";
            
            if (preg_match($pattern, $route, $matches)) {
                // ottiene il valore tra graffe e rimuove le graffe
                $stripRoutePath = explode('/', $route);

                $indexParamArray = array_search($matches[0], $stripRoutePath);
                $diff = array_diff($firstSegment, $stripRoutePath);
             
                if (count($diff) == 1) {
                    $index = array_key_first($diff);
                    if ($index === $indexParamArray) {
                        $this->valueUriParam =  $firstSegment[$indexParamArray];
                
                        // Replace il valore nella posizione estratta dell'array con $valueUriParam
                        $firstSegment[$indexParamArray] = $this->valueUriParam;
                        
                        // confronta l'uri con il path restituito dagli attributi
                        $pathRoute = implode('/', $firstSegment);
                        $param = trim($matches[0], '{}');
                    
                        return ['path' => $pathRoute, 'param' => $param, 'method' => $method];
                    }
                }
            } 
        } catch(InvalidArgumentException $exc) {
            print($exc->getMessage());
        }
    } 

    protected function invokeMethodWithArguments(string $param, string $method)
    {
        try {
            // viene invocata l'istanza dell'oggetto e restituisce il valore del metodo invocato
            $reflectionMethod = new ReflectionMethod($this->getClassName(), $method);
            
            foreach ($reflectionMethod->getParameters() as $param) {
                $type = $param->getType();
                if (! $type instanceof ReflectionNamedType) {
                    throw new ReflectionException("Parameter is not a known type");
                }

                if ($type->getName() === "int") {
                    if (! preg_match("/^[\d]*$/", $this->valueUriParam)) {
                        throw new InvalidArgumentException("Format parameter is invalid");
                    }  
                }
            }

            $instance = $this->container->get($this->getClassName());
           
            return $reflectionMethod->invokeArgs($instance, array($this->valueUriParam));
        } catch (ReflectionException $ref) {
            print($ref->getMessage());
        } catch (InvalidArgumentException $exc) {
            print($exc->getMessage());
        }
    }
}