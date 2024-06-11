<?php

namespace ManasahTech\Container\Attributes;

use Attribute;
use ManasahTech\Container\Scopes\TransientScope;

#[Attribute(Attribute::TARGET_CLASS)]
class Injectable
{
    public string $scope;

    public function __construct(string $scope = TransientScope::class)
    {
        $this->scope = $scope;
    }
}