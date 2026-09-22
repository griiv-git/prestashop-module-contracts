<?php

/**
 * Exemple d'utilisation de l'attribut AsPrestaShopHook pour un hook de filtrage
 */

namespace VotreModule\Hook\Filter;

use Griiv\Prestashop\Module\Contracts\Attribute\AsPrestaShopHook;
use Griiv\Prestashop\Module\Contracts\Hook\Hook;
use Griiv\Prestashop\Module\Contracts\Hook\Contracts\FilterHookInterface;

/**
 * Hook de filtrage pour modifier les résultats de recherche de produits
 *
 * Ce hook permet de filtrer ou modifier les produits retournés
 * par la recherche avant qu'ils ne soient affichés.
 */
#[AsPrestaShopHook(name: 'filterProductSearch')]
class FilterProductSearchExample extends Hook implements FilterHookInterface
{
    /**
     * Méthode appelée pour filtrer les résultats de recherche
     *
     * @param array $params Paramètres du hook contenant les produits
     * @return array Produits filtrés/modifiés
     */
    public function filter($params): array
    {
        $products = $params['products'] ?? [];

        // Exemple 1 : Filtrer les produits en rupture de stock
        $products = array_filter($products, function ($product) {
            return $product['quantity'] > 0;
        });

        // Exemple 2 : Ajouter des informations personnalisées
        $products = array_map(function ($product) {
            // Ajouter un badge "nouveau" pour les produits récents
            $createdDate = new \DateTime($product['date_add']);
            $now = new \DateTime();
            $diff = $now->diff($createdDate);

            if ($diff->days <= 7) {
                $product['is_new'] = true;
                $product['badge'] = 'Nouveau';
            }

            // Calculer une note personnalisée
            $product['custom_score'] = $this->calculateCustomScore($product);

            return $product;
        }, $products);

        // Exemple 3 : Trier les produits selon un critère personnalisé
        usort($products, function ($a, $b) {
            return ($b['custom_score'] ?? 0) <=> ($a['custom_score'] ?? 0);
        });

        return $products;
    }

    /**
     * Calcule un score personnalisé pour un produit
     *
     * @param array $product Données du produit
     * @return float Score calculé
     */
    private function calculateCustomScore(array $product): float
    {
        $score = 0;

        // Bonus pour les produits en promotion
        if (isset($product['reduction']) && $product['reduction'] > 0) {
            $score += 10;
        }

        // Bonus pour les produits bien notés
        if (isset($product['rating']) && $product['rating'] >= 4) {
            $score += 5;
        }

        // Bonus pour les produits populaires
        if (isset($product['sales']) && $product['sales'] > 100) {
            $score += 15;
        }

        return $score;
    }
}
