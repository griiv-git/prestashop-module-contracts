<?php

namespace Griiv\Prestashop\Module\Contracts\DependencyInjection\CompilerPass;

use Griiv\Prestashop\Module\Contracts\Attribute\AsPrestaShopHook;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PrestaShopHookCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Parcourir tous les services définis dans le container
        foreach ($container->getDefinitions() as $id => $definition) {
            $class = $definition->getClass();

            // Ignorer les services sans classe définie
            if (!$class || !class_exists($class)) {
                continue;
            }

            // Utiliser la réflexion pour vérifier si la classe a l'attribut AsPrestaShopHook
            try {
                $reflectionClass = new ReflectionClass($class);
                $attributes = $reflectionClass->getAttributes(AsPrestaShopHook::class);

                // Si l'attribut est présent, ajouter le tag prestashop.hook
                if (!empty($attributes)) {
                    foreach ($attributes as $attribute) {
                        $attributeInstance = $attribute->newInstance();

                        // Ajouter le tag avec le nom du hook
                        $definition->addTag('prestashop.hook', [
                            'hook' => $attributeInstance->name
                        ]);
                    }
                }
            } catch (\ReflectionException $e) {
                // Ignorer les erreurs de réflexion
                continue;
            }
        }
    }
}
