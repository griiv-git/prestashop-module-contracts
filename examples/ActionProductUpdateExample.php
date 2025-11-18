<?php

/**
 * Exemple d'utilisation de l'attribut AsPrestaShopHook pour un hook d'action
 */

namespace VotreModule\Hook\Action;

use Griiv\Prestashop\Module\Contracts\Attribute\AsPrestaShopHook;
use Griiv\Prestashop\Module\Contracts\Hook\Hook;
use Griiv\Prestashop\Module\Contracts\Hook\Contracts\ActionHookInterface;
use Psr\Log\LoggerInterface;

/**
 * Hook d'action déclenché lors de la mise à jour d'un produit
 *
 * Cet exemple montre comment injecter des dépendances via le constructeur
 * grâce à l'autowiring du container Symfony.
 */
#[AsPrestaShopHook(name: 'actionProductUpdate')]
class ActionProductUpdateExample extends Hook implements ActionHookInterface
{
    private LoggerInterface $logger;

    /**
     * Le constructeur peut recevoir des dépendances injectées automatiquement
     * par le container Symfony grâce à l'autowiring
     */
    public function __construct(
        \Context $context,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
    }

    /**
     * Méthode appelée lorsque le hook actionProductUpdate est déclenché
     *
     * @param array $params Paramètres du hook contenant le produit
     * @return bool True si l'action s'est bien déroulée
     */
    public function action($params): bool
    {
        try {
            /** @var \Product $product */
            $product = $params['product'];

            // Logger l'événement
            $this->logger->info('Product updated', [
                'product_id' => $product->id,
                'product_name' => $product->name,
            ]);

            // Votre logique métier ici
            // Par exemple :
            // - Synchroniser avec un système externe
            // - Invalider un cache
            // - Envoyer une notification
            // - Mettre à jour des données liées

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error in actionProductUpdate hook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }
}
