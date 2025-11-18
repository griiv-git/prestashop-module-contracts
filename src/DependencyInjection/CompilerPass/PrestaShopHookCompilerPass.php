<?php

namespace Griiv\Prestashop\Module\Contracts\DependencyInjection\CompilerPass;

use Griiv\Prestashop\Module\Contracts\Attribute\AsPrestaShopHook;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Finder\Finder;

class PrestaShopHookCompilerPass implements CompilerPassInterface
{
    /**
     * @var array Namespaces à scanner pour trouver les classes avec l'attribut AsPrestaShopHook
     */
    private array $namespacesToScan;

    /**
     * @var array Répertoires à scanner pour trouver les classes
     */
    private array $directories;

    /**
     * @param array $namespacesToScan Namespaces à scanner (ex: ['VotreModule\Hook\Display', 'VotreModule\Hook\Action'])
     * @param array $directories Répertoires absolus à scanner (ex: ['/path/to/module/src/Hook'])
     */
    public function __construct(array $namespacesToScan = [], array $directories = [])
    {
        $this->namespacesToScan = $namespacesToScan;
        $this->directories = $directories;
    }

    public function process(ContainerBuilder $container): void
    {
        // Étape 1 : Scanner et enregistrer les nouvelles classes avec l'attribut
        $this->scanAndRegisterClasses($container);

        // Étape 2 : Tagger tous les services existants qui ont l'attribut
        $this->tagExistingServices($container);
    }

    /**
     * Scanne les répertoires spécifiés et enregistre les classes avec l'attribut AsPrestaShopHook
     */
    private function scanAndRegisterClasses(ContainerBuilder $container): void
    {
        if (empty($this->directories)) {
            return;
        }

        // Utiliser Symfony Finder pour trouver tous les fichiers PHP
        $finder = new Finder();
        $finder->files()->in($this->directories)->name('*.php');

        foreach ($finder as $file) {
            $this->processFile($file->getPathname(), $container);
        }
    }

    /**
     * Traite un fichier PHP et enregistre la classe si elle a l'attribut
     */
    private function processFile(string $filePath, ContainerBuilder $container): void
    {
        // Extraire le namespace et le nom de classe du fichier
        $className = $this->extractClassName($filePath);

        if (!$className || !class_exists($className)) {
            return;
        }

        try {
            $reflectionClass = new ReflectionClass($className);

            // Ignorer les classes abstraites et les interfaces
            if ($reflectionClass->isAbstract() || $reflectionClass->isInterface()) {
                return;
            }

            $attributes = $reflectionClass->getAttributes(AsPrestaShopHook::class);

            if (!empty($attributes)) {
                // La classe a l'attribut, l'enregistrer comme service si elle n'existe pas déjà
                if (!$container->has($className)) {
                    $definition = new Definition($className);
                    $definition->setAutowired(true);
                    $definition->setAutoconfigured(true);
                    $definition->setPublic(true);

                    // Ajouter les tags pour chaque attribut
                    foreach ($attributes as $attribute) {
                        $attributeInstance = $attribute->newInstance();
                        $definition->addTag('prestashop.hook', [
                            'hook' => $attributeInstance->name
                        ]);
                    }

                    $container->setDefinition($className, $definition);
                }
            }
        } catch (\ReflectionException $e) {
            // Ignorer les erreurs de réflexion
            return;
        }
    }

    /**
     * Extrait le nom de classe complet (FQCN) d'un fichier PHP
     */
    private function extractClassName(string $filePath): ?string
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }

        // Extraire le namespace
        $namespace = null;
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $namespace = trim($matches[1]);
        }

        // Extraire le nom de la classe
        $className = null;
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            $className = $matches[1];
        }

        if ($namespace && $className) {
            return $namespace . '\\' . $className;
        }

        return $className;
    }

    /**
     * Tague les services existants qui ont l'attribut AsPrestaShopHook
     */
    private function tagExistingServices(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            $class = $definition->getClass();

            if (!$class || !class_exists($class)) {
                continue;
            }

            try {
                $reflectionClass = new ReflectionClass($class);
                $attributes = $reflectionClass->getAttributes(AsPrestaShopHook::class);

                if (!empty($attributes)) {
                    // Vérifier si le tag n'existe pas déjà pour éviter les doublons
                    $existingTags = $definition->getTag('prestashop.hook');

                    foreach ($attributes as $attribute) {
                        $attributeInstance = $attribute->newInstance();

                        // Vérifier si le tag existe déjà
                        $tagExists = false;
                        foreach ($existingTags as $existingTag) {
                            if (isset($existingTag['hook']) && $existingTag['hook'] === $attributeInstance->name) {
                                $tagExists = true;
                                break;
                            }
                        }

                        if (!$tagExists) {
                            $definition->addTag('prestashop.hook', [
                                'hook' => $attributeInstance->name
                            ]);
                        }
                    }
                }
            } catch (\ReflectionException $e) {
                continue;
            }
        }
    }
}
