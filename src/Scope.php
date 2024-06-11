<?php

namespace ManasahTech\Container;

interface Scope
{
    public function resolve(Container $container, string $abstract, callable $factory);
}