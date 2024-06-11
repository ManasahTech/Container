<?php

namespace ManasahTech\Container;

use Closure;
use Exception;
use ReflectionClass;

// use ManasahTech\Contracts\Container\BindingResolutionException;
// use ManasahTech\Contracts\Container\CircularDependencyException;
use ManasahTech\Contracts\Container\Container as ContainerContract;
use ManasahTech\Container\Attributes\Injectable;
use ManasahTech\Container\Scopes\TransientScope;


class Container implements ContainerContract
{
    protected $bindings = [];
    protected $sharedInstances = [];


    //----------
    public function has($key) : bool
    {
        return false;
    }
    public function get($key)
    {
        return null;
    }
    //----------
    

    public function bind($abstract, $concrete = null, $scope = TransientScope::class)
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        if (!$concrete instanceof Closure) {
            $concrete = $this->getClosure($abstract, $concrete);
        }

        $this->bindings[$abstract] = compact('concrete', 'scope');
    }

    protected function getClosure($abstract, $concrete)
    {
        return function($container) use ($abstract, $concrete) {
            return $container->build($concrete);
        };
    }

    public function build($concrete)
    {
        if ($concrete instanceof Closure) {
            return $concrete($this);
        }

        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new Exception("Class {$concrete} is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $parameters = $constructor->getParameters();
        $dependencies = $this->resolveDependencies($parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    protected function resolveDependencies($parameters)
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();

            if (is_null($dependency)) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new Exception("Cannot resolve the dependency.");
                }
            } else {
                $dependencies[] = $this->resolve($dependency->name);
            }
        }

        return $dependencies;
    }

    public function resolve($abstract)
    {
        if (!isset($this->bindings[$abstract])) {
            $this->autoBind($abstract);
        }

        $binding = $this->bindings[$abstract];
        $concrete = $binding['concrete'];
        $scopeClass = $binding['scope'];

        if (!class_exists($scopeClass)) {
            throw new Exception("Scope class {$scopeClass} does not exist.");
        }

        $scope = new $scopeClass();
        if (!$scope instanceof Scope) {
            throw new Exception("Scope class {$scopeClass} must implement Scope interface.");
        }

        return $scope->resolve($this, $abstract, function() use ($concrete) {
            return $this->build($concrete);
        });
    }

    protected function autoBind($abstract)
    {
        $reflector = new ReflectionClass($abstract);
        $attributes = $reflector->getAttributes(Injectable::class);

        if (!empty($attributes)) {
            $attribute = $attributes[0]->newInstance();
            $this->bind($abstract, $abstract, $attribute->scope);
        } else {
            $this->bind($abstract, $abstract);
        }
    }

    public function make($abstract)
    {
        return $this->resolve($abstract);
    }
}