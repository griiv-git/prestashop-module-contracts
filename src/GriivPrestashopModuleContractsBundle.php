<?php

namespace Griiv\Prestashop\Module\Contracts;

use Griiv\Prestashop\Module\Contracts\DependencyInjection\CompilerPass\PrestaShopHookCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GriivPrestashopModuleContractsBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // Enregistrer le CompilerPass pour l'auto-tagging des hooks
        $container->addCompilerPass(new PrestaShopHookCompilerPass());
    }
}
