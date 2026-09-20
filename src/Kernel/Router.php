<?php
declare(strict_types=1);

namespace Ledgerly\Kernel;

use InvalidArgumentException;
use Ledgerly\Infrastructure\Route;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;

class Router
{
    public string $valueUriParam = "";

    public function __construct()
    {
        $this->getAttributesFromReflection();
    }

    protected function getAttributesFromReflection(): void
    {
        // Itera tutti i metodi esposti dalla classe
        foreach ($this->setReflectionClass()->getMethods() as $method) {
            // Itera tutti i valori restituiti dagli attributi associati ai metodi
            foreach ($method->getAttributes(Route::class) as $params) {
                // crea instanza e restituisce tutti i valori degli attributi
                $route = $params->newInstance();
                
                if ($this->uri() === $route->path) {
                    $this->getMethodWithoutParams($method->name);
                } else {
                   $this->getMethodWithParams($route->path, $method->getName());
                } 
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

    protected function getMethodWithoutParams(string $method): void
    {
        // crea una nuova istanza di reflection
        $instance = $this->setReflectionClass()->newInstance();
        // ottiene il metodo che si lega al path
        $obj = $this->setReflectionClass()->getMethod($method);
        $obj->invoke($instance);
    }

    protected function getMethodWithParams(string $route, string $method): void
    {
        try {
             $firstSegment =  explode("/", $this->uri());
            // Regex per verificare se nel path della rotta c'è un valore
            // espresso all'interno di graffe che sta ad indicare un parametro
            $pattern = "/\{[a-z]*\}/";
            
            if (preg_match($pattern, $route, $matches)) {
                // ottiene il valore tra graffe e rimuove le graffe
                $stripRoutePath = explode('/', $route);
                
                if (!in_array($matches[0], $stripRoutePath)) {
                    throw new InvalidArgumentException('Value not found');
                }

                $indexParamArray = array_search($matches[0], $stripRoutePath);
                if (!isset($firstSegment[$indexParamArray])) {
                    throw new InvalidArgumentException('Value not found');
                }
                
                $this->valueUriParam =  $firstSegment[$indexParamArray];
                // Replace il valore nella posizione estratta dell'array con $valueUriParam
                $firstSegment[$indexParamArray] = $this->valueUriParam;
                // confronta l'uri con il path restituito dagli attributi
                $pathRoute = implode('/', $firstSegment);
                
                if ($this->uri() === $pathRoute) {
                    $this->invokeMethodWithArguments($matches[0], $method);
                }
            } 
        } catch(InvalidArgumentException $exc) {
            print($exc->getMessage());
        }
    } 

    protected function invokeMethodWithArguments(string $matches, string $method): void
    {
        try {
            $param = trim($matches[0], '{}');
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

            $refClassString = $this->getClassName();
            $reflectionMethod->invokeArgs(new $refClassString, array($this->valueUriParam));
        } catch (ReflectionException $ref) {
            print($ref->getMessage());
        } catch (InvalidArgumentException $exc) {
            print($exc->getMessage());
        }
    }
}