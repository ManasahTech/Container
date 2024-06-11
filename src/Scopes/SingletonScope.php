<?php

namespace ManasahTech\Container\Scopes;

use ManasahTech\Container\Scope;
use ManasahTech\Container\Container;

class SingletonScope implements Scope
{
    protected static $instances = [];

    public function resolve(Container $container, string $abstract, callable $factory)
    {
        if (!isset(self::$instances[$abstract])) {
            self::$instances[$abstract] = $factory($container);
        }
        return self::$instances[$abstract];
    }
}