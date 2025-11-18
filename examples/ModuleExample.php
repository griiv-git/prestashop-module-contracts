<?php

/**
 * Exemple complet d'utilisation de ModuleAbstract avec l'attribut AsPrestaShopHook
 *
 * Ce fichier montre comment configurer un module PrestaShop pour utiliser
 * l'auto-tagging des hooks via le PrestaShopHookCompilerPass.
 */

namespace VotreModule;

use Griiv\Prestashop\Module\Contracts\Module\ModuleAbstract;
use Griiv\Prestashop\Module\Contracts\DependencyInjection\CompilerPass\PrestaShopHookCompilerPass;

class VotreModule extends ModuleAbstract
{
    /**
     * Namespace utilisé pour la résolution automatique des hooks
     */
    protected $nameSpace = 'VotreModule\\';

    public function __construct()
    {
        $this->name = 'votremodule';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Votre Nom';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->trans('Votre Module', [], 'Modules.Votremodule.Admin');
        $this->description = $this->trans('Description de votre module', [], 'Modules.Votremodule.Admin');

        // Enregistrer le CompilerPass pour l'auto-tagging des hooks avec l'attribut #[AsPrestaShopHook]
        $this->registerCompilerPass();
    }

    /**
     * Enregistre le CompilerPass pour l'auto-tagging des hooks
     *
     * Cette méthode ajoute le PrestaShopHookCompilerPass au container Symfony.
     * Le CompilerPass scanne tous les services et ajoute automatiquement le tag
     * 'prestashop.hook' aux classes qui possèdent l'attribut #[AsPrestaShopHook].
     */
    private function registerCompilerPass(): void
    {
        try {
            $kernel = static::getKernel();
            $container = $kernel->getContainer();

            // Ajouter le CompilerPass seulement si le container est en phase de compilation
            if ($container instanceof \Symfony\Component\DependencyInjection\ContainerBuilder) {
                $container->addCompilerPass(new PrestaShopHookCompilerPass());
            }
        } catch (\Exception $e) {
            // En cas d'erreur, logger l'exception pour faciliter le debug
            // Note : Dans un environnement de production, vous devriez utiliser un logger approprié
            error_log('Failed to register PrestaShopHookCompilerPass: ' . $e->getMessage());
        }
    }

    /**
     * Retourne la liste des hooks à enregistrer
     *
     * @return array Liste des noms de hooks
     */
    public function getHooks(): array
    {
        return [
            'displayHeader',
            'displayFooter',
            'actionProductUpdate',
            'actionProductSave',
            'filterProductSearch',
        ];
    }

    /**
     * Méthode appelée lors de l'installation du module
     *
     * @return bool True si l'installation réussit
     */
    public function install(): bool
    {
        if (!parent::install()) {
            return false;
        }

        // Enregistrer les hooks définis dans getHooks()
        foreach ($this->getHooks() as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Méthode appelée lors de la désinstallation du module
     *
     * @return bool True si la désinstallation réussit
     */
    public function uninstall(): bool
    {
        // Désenregistrer tous les hooks
        foreach ($this->getHooks() as $hook) {
            $this->unregisterHook($hook);
        }

        return parent::uninstall();
    }
}
