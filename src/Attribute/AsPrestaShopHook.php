<?php

namespace Griiv\Prestashop\Module\Contracts\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsPrestaShopHook
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
