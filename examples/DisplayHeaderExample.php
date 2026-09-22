<?php

/**
 * Exemple d'utilisation de l'attribut AsPrestaShopHook
 *
 * Ce fichier montre comment utiliser l'attribut #[AsPrestaShopHook]
 * pour marquer automatiquement une classe de hook.
 */

namespace VotreModule\Hook\Display;

use Griiv\Prestashop\Module\Contracts\Attribute\AsPrestaShopHook;
use Griiv\Prestashop\Module\Contracts\Hook\Hook;
use Griiv\Prestashop\Module\Contracts\Hook\Contracts\DisplayHookInterface;

/**
 * Hook d'affichage pour le header
 *
 * L'attribut AsPrestaShopHook indique que cette classe doit être
 * automatiquement tagée avec 'prestashop.hook' dans le container DI.
 */
#[AsPrestaShopHook(name: 'displayHeader')]
class DisplayHeaderExample extends Hook implements DisplayHookInterface
{
    /**
     * Méthode appelée lorsque le hook displayHeader est déclenché
     *
     * @param array $params Paramètres passés par PrestaShop
     * @return string HTML à afficher
     */
    public function display($params): string
    {
        // Définir le nom du template à utiliser
        $this->setTplName('header');

        // Assigner des variables au template Smarty
        $this->context->smarty->assign([
            'custom_message' => 'Ceci est un exemple de hook displayHeader',
            'current_controller' => $this->context->controller->php_self,
            'is_logged' => $this->context->customer->isLogged(),
        ]);

        // Retourner le HTML généré par le template
        return $this->getModule()->fetch($this->getTpl());
    }
}
