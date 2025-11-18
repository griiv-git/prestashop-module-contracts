# Utilisation de l'attribut AsPrestaShopHook

## Description

L'attribut `#[AsPrestaShopHook]` permet l'**auto-découverte** et l'**auto-enregistrement** de vos classes de hooks dans le container de dépendances avec le tag `prestashop.hook`. Plus besoin de déclarer manuellement chaque hook dans un fichier de configuration YAML !

Le `PrestaShopHookCompilerPass` scanne automatiquement vos répertoires de hooks, détecte les classes avec l'attribut `#[AsPrestaShopHook]`, les enregistre comme services et les tague automatiquement.

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

        // Enregistrer le CompilerPass pour l'auto-découverte des hooks
        $this->registerCompilerPass();
    }

    /**
     * Enregistre le CompilerPass pour l'auto-découverte et l'auto-enregistrement des hooks
     */
    private function registerCompilerPass(): void
    {
        $kernel = static::getKernel();
        $container = $kernel->getContainer();

        // Ajouter le CompilerPass si le container est encore en phase de compilation
        if ($container instanceof \Symfony\Component\DependencyInjection\ContainerBuilder) {
            // Définir les répertoires à scanner
            $directories = [
                $this->getLocalPath() . 'src/Hook/Display',
                $this->getLocalPath() . 'src/Hook/Action',
                $this->getLocalPath() . 'src/Hook/Filter',
                $this->getLocalPath() . 'src/Hook/Additional',
            ];

            // Filtrer les répertoires qui existent
            $directories = array_filter($directories, 'is_dir');

            // Enregistrer le CompilerPass avec les répertoires à scanner
            $compilerPass = new PrestaShopHookCompilerPass([], $directories);
            $container->addCompilerPass($compilerPass);
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

Créez simplement vos classes de hooks avec l'attribut `#[AsPrestaShopHook]` - elles seront automatiquement découvertes et enregistrées :

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

**C'est tout !** Aucune configuration YAML nécessaire. La classe sera :
1. Automatiquement découverte par le scan du répertoire
2. Enregistrée comme service dans le container
3. Tagguée avec `prestashop.hook` avec le hook `displayHeader`
4. Prête à être utilisée avec autowiring et injection de dépendances

### Exemple avec un hook d'action et injection de dépendances

```php
<?php

namespace VotreModule\Hook\Action;

use Griiv\Prestashop\Module\Contracts\Attribute\AsPrestaShopHook;
use Griiv\Prestashop\Module\Contracts\Hook\Hook;
use Griiv\Prestashop\Module\Contracts\Hook\Contracts\ActionHookInterface;
use Psr\Log\LoggerInterface;

#[AsPrestaShopHook(name: 'actionProductUpdate')]
class ActionProductUpdate extends Hook implements ActionHookInterface
{
    private LoggerInterface $logger;

    /**
     * Les dépendances sont injectées automatiquement grâce à l'autowiring
     */
    public function __construct(\Context $context, LoggerInterface $logger)
    {
        parent::__construct($context);
        $this->logger = $logger;
    }

    public function action($params): bool
    {
        $product = $params['product'];

        $this->logger->info('Product updated', [
            'product_id' => $product->id,
        ]);

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
        $products = array_filter($products, function($product) {
            return $product['quantity'] > 0;
        });

        return $products;
    }
}
```

## Fonctionnement technique

### Auto-découverte et auto-enregistrement

Lorsque le container Symfony est compilé, le `PrestaShopHookCompilerPass` effectue deux actions :

**Étape 1 : Scanner et enregistrer**
1. Parcourt tous les fichiers PHP dans les répertoires spécifiés
2. Extrait le nom de classe complet (FQCN) de chaque fichier
3. Vérifie si la classe possède l'attribut `#[AsPrestaShopHook]`
4. Si oui, enregistre automatiquement la classe comme service dans le container avec :
   - `autowire: true` - Injection automatique des dépendances
   - `autoconfigure: true` - Configuration automatique
   - `public: true` - Service accessible publiquement
5. Ajoute le tag `prestashop.hook` avec le nom du hook

**Étape 2 : Tagger les services existants**
1. Parcourt tous les services déjà définis dans le container
2. Vérifie si leur classe possède l'attribut `#[AsPrestaShopHook]`
3. Ajoute le tag `prestashop.hook` s'il n'existe pas déjà

### Structure du tag

Le tag `prestashop.hook` contient les informations suivantes :

```yaml
tags:
  - { name: 'prestashop.hook', hook: 'displayHeader' }
```

### Répertoires scannés

Par défaut, vous devriez scanner les répertoires suivants (selon votre structure) :
- `src/Hook/Display` - Hooks d'affichage
- `src/Hook/Action` - Hooks d'action
- `src/Hook/Filter` - Hooks de filtrage
- `src/Hook/Additional` - Hooks additionnels

## Avantages

1. **Zéro configuration** : Plus besoin de déclarer manuellement chaque hook dans un fichier YAML
2. **Auto-découverte** : Les hooks sont automatiquement découverts en scannant vos répertoires
3. **Déclaratif** : Le nom du hook est déclaré directement sur la classe
4. **Type-safe** : L'attribut garantit que seules les classes marquées explicitement sont enregistrées
5. **Autowiring** : Injection automatique des dépendances via le constructeur
6. **Maintenabilité** : Facilite la gestion des hooks dans les grandes bases de code
7. **IDE-friendly** : Les IDEs modernes reconnaissent les attributs PHP

## Migration depuis l'approche classique

### Avant (configuration manuelle)

```yaml
# config/services.yml
services:
  votre_module.hook.display_header:
    class: VotreModule\Hook\Display\DisplayHeader
    arguments:
      - '@griiv.prestashop.module.contracts.context'
    tags:
      - { name: 'prestashop.hook', hook: 'displayHeader' }
```

```php
<?php
namespace VotreModule\Hook\Display;

class DisplayHeader extends Hook implements DisplayHookInterface
{
    // ... code du hook
}
```

### Après (avec attribut et auto-découverte)

**Aucune configuration YAML nécessaire !**

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

Le service est automatiquement :
- Découvert en scannant le répertoire
- Enregistré dans le container
- Configuré avec autowiring
- Tagué avec `prestashop.hook`

## Dépannage

### Le hook n'est pas reconnu

1. Vérifiez que le `PrestaShopHookCompilerPass` est bien enregistré avec les bons répertoires
2. Vérifiez que le répertoire contenant votre classe existe et est bien scanné
3. Videz le cache : `php bin/console cache:clear`
4. Vérifiez que votre classe possède bien l'attribut `#[AsPrestaShopHook]`
5. Vérifiez que le nom de classe et le namespace sont corrects

### Erreur "Class not found"

Si vous obtenez une erreur "Class not found" :
- Assurez-vous que l'autoloader PSR-4 est correctement configuré dans `composer.json`
- Vérifiez que le namespace de la classe correspond à la structure de répertoires
- Exécutez `composer dump-autoload` pour régénérer l'autoloader

### Erreur de réflexion

Si vous obtenez une erreur de réflexion :
- La classe existe et est chargeable par l'autoloader PSR-4
- Le namespace est correct
- PHP 8.0+ est bien utilisé
- La classe n'est ni abstraite ni une interface (sauf si c'est volontaire)

### Le CompilerPass ne scanne pas mes hooks

Vérifiez que :
- Les répertoires passés au CompilerPass existent vraiment (utilisez `is_dir()`)
- Les chemins sont absolus (utilisez `$this->getLocalPath()`)
- Les classes sont dans des fichiers `.php`
- Le cache Symfony est bien vidé après chaque modification

### Debug : voir les services enregistrés

Pour voir tous les services enregistrés avec le tag `prestashop.hook` :

```bash
php bin/console debug:container --tag=prestashop.hook
```

## Compatibilité

- **PHP** : >= 8.0 (requis pour les attributs natifs)
- **PrestaShop** : 1.7+ (avec support Symfony natif)
- **Symfony** : Toutes versions supportées par PrestaShop
- **Symfony Finder** : Requis pour le scan de fichiers

## Performances

Le scan des répertoires n'a lieu qu'une seule fois lors de la compilation du container, pas à chaque requête. Le container compilé est ensuite mis en cache, donc aucun impact sur les performances en production.

## Voir aussi

- [README.md](README.md) - Documentation principale
- [Documentation PrestaShop sur les hooks](https://devdocs.prestashop.com/1.7/modules/concepts/hooks/)
- [Documentation Symfony sur les CompilerPasses](https://symfony.com/doc/current/service_container/compiler_passes.html)
- [Documentation Symfony sur l'autowiring](https://symfony.com/doc/current/service_container/autowiring.html)
