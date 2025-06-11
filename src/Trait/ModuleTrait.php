<?php

namespace Griiv\Prestashop\Module\Contracts\Trait;

trait ModuleTrait
{
    public static function getTranslationDomain()
    {
        $moduleName = ucfirst(self::class);
        return sprintf('Modules.%s.%s', $moduleName, $moduleName);
    }

    public static function getModuleToken(string $controller): string
    {
        return \Tools::hash(sprintf('%s/%s/%s', self::class, $controller, (new \DateTime())->format('Y-m-d')));
    }
}