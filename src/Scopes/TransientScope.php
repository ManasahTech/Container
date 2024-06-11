<?php

namespace ManasahTech\Container\Scopes;

use ManasahTech\Container\Scope;
use ManasahTech\Container\Container;

class TransientScope implements Scope
{
    public function resolve(Container $container, string $abstract, callable $factory)
    {
        return $factory($container);
    }
}