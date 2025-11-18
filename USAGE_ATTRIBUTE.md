# Utilisation de l'attribut AsPrestaShopHook

## Description

L'attribut `#[AsPrestaShopHook]` permet de marquer automatiquement vos classes de hooks pour qu'elles soient enregistrées dans le container de dépendances avec le tag `prestashop.hook`. Cela facilite la gestion et la découverte automatique des hooks dans votre module PrestaShop.

## Prérequis

- PHP >= 8.0 (pour le support des attributs natifs)
- PrestaShop 1.7+ avec Symfony

## Installation et configuration

### 1. Installer la bibliothèque

```bash
composer require griiv/prestashop-module-contracts
```

### 2. Enregistrer le CompilerPass dans votre module

Dans la classe principale de votre module, ajoutez le code suivant pour enregistrer le `PrestaShopHookCompilerPass` :

```php
<?php

namespace VotreNamespace;

use Griiv\Prestashop\Module\Contracts\Module\ModuleAbstract;
use Griiv\Prestashop\Module\Contracts\DependencyInjection\CompilerPass\PrestaShopHookCompilerPass;

class VotreModule extends ModuleAbstract
{
    protected $nameSpace = 'VotreNamespace\\';

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

        // Enregistrer le CompilerPass pour l'auto-tagging des hooks
        $this->registerCompilerPass();
    }

    /**
     * Enregistre le CompilerPass pour l'auto-tagging des hooks
     */
    private function registerCompilerPass(): void
    {
        $kernel = static::getKernel();
        $container = $kernel->getContainer();

        // Ajouter le CompilerPass si le container est encore en phase de compilation
        if ($container instanceof \Symfony\Component\DependencyInjection\ContainerBuilder) {
            $container->addCompilerPass(new PrestaShopHookCompilerPass());
        }
    }

    public function getHooks(): array
    {
        return [
            'displayHeader',
            'actionProductUpdate',
            // ... autres hooks
        ];
    }
}
```

### 3. Vider le cache

Après la configuration, videz le cache de PrestaShop :

```bash
php bin/console cache:clear
```

## Utilisation

### Exemple basique

Utilisez l'attribut `#[AsPrestaShopHook]` sur vos classes de hooks pour qu'elles soient automatiquement tagées avec `prestashop.hook` :

```php
<?php

namespace VotreModule\Hook\Display;

use Griiv\Prestashop\Module\Contracts\Attribute\AsPrestaShopHook;
use Griiv\Prestashop\Module\Contracts\Hook\Hook;
use Griiv\Prestashop\Module\Contracts\Hook\Contracts\DisplayHookInterface;

#[AsPrestaShopHook(name: 'displayHeader')]
class DisplayHeader extends Hook implements DisplayHookInterface
{
    public function display($params): string
    {
        $this->setTplName('header');

        $this->context->smarty->assign([
            'custom_var' => 'valeur',
        ]);

        return $this->getModule()->fetch($this->getTpl());
    }
}
```

### Exemple avec un hook d'action

```php
<?php

namespace VotreModule\Hook\Action;

use Griiv\Prestashop\Module\Contracts\Attribute\AsPrestaShopHook;
use Griiv\Prestashop\Module\Contracts\Hook\Hook;
use Griiv\Prestashop\Module\Contracts\Hook\Contracts\ActionHookInterface;

#[AsPrestaShopHook(name: 'actionProductUpdate')]
class ActionProductUpdate extends Hook implements ActionHookInterface
{
    public function action($params): bool
    {
        $product = $params['product'];

        // Votre logique métier ici
        // Par exemple : log, mise à jour, synchronisation, etc.

        return true;
    }
}
```

### Exemple avec un hook de filtrage

```php
<?php

namespace VotreModule\Hook\Filter;

use Griiv\Prestashop\Module\Contracts\Attribute\AsPrestaShopHook;
use Griiv\Prestashop\Module\Contracts\Hook\Hook;
use Griiv\Prestashop\Module\Contracts\Hook\Contracts\FilterHookInterface;

#[AsPrestaShopHook(name: 'filterProductSearch')]
class FilterProductSearch extends Hook implements FilterHookInterface
{
    public function filter($params): array
    {
        $products = $params['products'];

        // Filtrer ou modifier les produits

        return $products;
    }
}
```

## Fonctionnement technique

### Auto-tagging

Lorsque le container Symfony est compilé, le `PrestaShopHookCompilerPass` :

1. Parcourt tous les services définis dans le container
2. Vérifie si chaque service possède l'attribut `#[AsPrestaShopHook]`
3. Pour chaque service avec l'attribut, ajoute automatiquement le tag `prestashop.hook` avec le nom du hook spécifié

### Structure du tag

Le tag `prestashop.hook` contient les informations suivantes :

```yaml
tags:
  - { name: 'prestashop.hook', hook: 'displayHeader' }
```

Cela permet au système de dépendances de PrestaShop de :
- Identifier facilement tous les hooks disponibles
- Injecter automatiquement les dépendances nécessaires
- Gérer le cycle de vie des hooks

## Avantages

1. **Déclaratif** : Le nom du hook est déclaré directement sur la classe, rendant le code plus lisible
2. **Auto-discovery** : Plus besoin de déclarer manuellement chaque hook dans un fichier de configuration
3. **Type-safe** : L'attribut garantit que seules les classes marquées explicitement sont tagées
4. **Maintenabilité** : Facilite la gestion des hooks dans les grandes bases de code
5. **IDE-friendly** : Les IDEs modernes reconnaissent les attributs PHP et peuvent fournir l'autocomplétion

## Migration depuis l'approche classique

### Avant (configuration manuelle)

```yaml
# config/services.yml
services:
  votre_module.hook.display_header:
    class: VotreModule\Hook\Display\DisplayHeader
    tags:
      - { name: 'prestashop.hook', hook: 'displayHeader' }
```

### Après (avec attribut)

```php
<?php

namespace VotreModule\Hook\Display;

use Griiv\Prestashop\Module\Contracts\Attribute\AsPrestaShopHook;

#[AsPrestaShopHook(name: 'displayHeader')]
class DisplayHeader extends Hook implements DisplayHookInterface
{
    // ... code du hook
}
```

La configuration dans `services.yml` n'est plus nécessaire car le tag est ajouté automatiquement.

## Dépannage

### Le hook n'est pas reconnu

1. Vérifiez que le `PrestaShopHookCompilerPass` est bien enregistré dans votre module
2. Videz le cache : `php bin/console cache:clear`
3. Vérifiez que votre classe possède bien l'attribut `#[AsPrestaShopHook]`
4. Assurez-vous que votre classe est bien un service enregistré dans le container

### Erreur de réflexion

Si vous obtenez une erreur de réflexion, assurez-vous que :
- La classe existe et est chargeable par l'autoloader PSR-4
- Le namespace est correct
- PHP 8.0+ est bien utilisé

### Le CompilerPass n'est pas enregistré

Si le CompilerPass ne semble pas fonctionner :
- Vérifiez que vous appelez bien `registerCompilerPass()` dans le constructeur de votre module
- Assurez-vous que le container est encore en phase de compilation (type `ContainerBuilder`)
- Vérifiez les logs de Symfony pour voir si des erreurs sont remontées

## Compatibilité

- **PHP** : >= 8.0 (requis pour les attributs natifs)
- **PrestaShop** : 1.7+ (avec support Symfony natif)
- **Symfony** : Toutes versions supportées par PrestaShop

## Voir aussi

- [README.md](README.md) - Documentation principale
- [Documentation PrestaShop sur les hooks](https://devdocs.prestashop.com/1.7/modules/concepts/hooks/)
- [Documentation Symfony sur les CompilerPasses](https://symfony.com/doc/current/service_container/compiler_passes.html)
