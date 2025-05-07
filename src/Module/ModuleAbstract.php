<?php

namespace Griiv\Prestashop\Module\Contracts\Module;

use Griiv\Prestashop\Module\Contracts\Module\Contracts\ModuleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ModuleAbstract extends \Module implements ModuleInterface
{
    private static $kernel;

    protected $nameSpace = 'Griiv\\Prestashop\\Module\\';

    public function getHooks(): array
    {
        return [];
    }

    public function __call(string $name, array $args)
    {
        if (preg_match('@hook@', $name)) {
            $method = 'display';
            $hookName = substr($name, 4);

            if (substr($hookName, 0, 6) == 'Action') {
                $method = 'action';
            } elseif (substr($hookName, 0, 6) == 'Filter') {
                $method = 'filter';
            } elseif (substr($hookName, 0, 10) == 'Additional') {
                $method = 'additional';
            }

            $className = '\\' . $this->nameSpace . ucfirst($method) . '\\' . $hookName;
            if (!class_exists($className)) {
                throw new \Exception("Class $className not found");
            }

            return $this->get($this->nameSpace . ucfirst($method) . '\\' . $hookName)->{$method}($args[0]);
        }
    }

    public static function getKernel(): KernelInterface
    {
        // if the singleton doesn't exist
        if (!self::$kernel) {
            require_once _PS_ROOT_DIR_.'/app/AppKernel.php';
            $env = _PS_MODE_DEV_ ? 'dev' : 'prod';
            $debug = _PS_MODE_DEV_ ? true : false;
            self::$kernel = new \AppKernel($env, $debug);
            self::$kernel->boot();
        }

        return self::$kernel;
    }

    /**
     * Get a specific Symfony service.
     *
     * @param string $service
     *
     * @return object
     */
    public static function getService(string $service): object
    {
        return self::getKernel()->getContainer()->get($service);
    }

    public static function getParameter(string $key): string
    {
        return self::getKernel()->getContainer()->getParameter($key);
    }

    public static function getEntityManager(): \Doctrine\ORM\EntityManagerInterface
    {
        return self::getService('doctrine.orm.entity_manager');
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }

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