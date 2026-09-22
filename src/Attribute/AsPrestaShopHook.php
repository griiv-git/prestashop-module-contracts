<?php

namespace Griiv\Prestashop\Module\Contracts\Attribute;

use Attribute;

/**
 * Attribut PHP 8.0+ pour déclarer un hook PrestaShop sur une classe de service.
 *
 * Sur PHP < 8.0, la syntaxe #[AsPrestaShopHook(...)] n'est pas disponible
 * mais la classe reste utilisable comme simple DTO.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class AsPrestaShopHook
{
    /** @var string */
    public $name;

    /** @var string */
    public $module;

    /** @var string */
    public $description;

    /**
     * @param string $name
     * @param string $module
     * @param string $description
     */
    public function __construct($name, $module, $description = '')
    {
        $this->name = $name;
        $this->module = $module;
        $this->description = $description;
    }
}
