# PrestaShop Module Contracts

Bibliothèque PHP fournissant des interfaces et une classe abstraite pour faciliter le développement de modules PrestaShop modernes avec une gestion automatique des hooks et l'intégration de Symfony.

## Description

Ce package propose une architecture structurée pour créer des modules PrestaShop en utilisant les bonnes pratiques de développement PHP moderne. Il simplifie la gestion des hooks PrestaShop en automatisant leur résolution et en permettant une organisation claire du code basée sur des interfaces.

## Fonctionnalités

- **Classe abstraite ModuleAbstract** : Base pour créer vos modules avec accès au kernel Symfony et aux services
- **Gestion automatique des hooks** : Résolution automatique des hooks basée sur des conventions de nommage
- **Interfaces de hooks typées** :
  - `DisplayHookInterface` : Pour les hooks d'affichage (retournent du HTML)
  - `ActionHookInterface` : Pour les hooks d'action (retournent un booléen)
  - `FilterHookInterface` : Pour les hooks de filtrage
  - `AdditionalHookInterface` : Pour les hooks additionnels
- **Intégration Symfony** : Accès direct au kernel, aux services et à l'EntityManager Doctrine
- **Traits utilitaires** (Concerns) : Méthodes helper pour les traductions, tokens de sécurité et configuration module
- **Attribut PHP 8.0+** : `#[AsPrestaShopHook]` pour déclarer les hooks de manière déclarative (optionnel, rétrocompatible PHP 7.2)
- **CompilerPass Symfony** : Enregistrement automatique des hooks via attributs dans le conteneur de services
- **Scripts d'installation automatisés** : Hooks Composer pour install/update/remove

## Prérequis

- PHP >= 7.2
- PrestaShop >= 1.7.6 (avec support Symfony natif et accès au conteneur de services)
- Composer

> **Note :** Les fonctionnalités basées sur les attributs PHP (`#[AsPrestaShopHook]` et `PrestaShopHookCompilerPass`) nécessitent PHP 8.0+. Sur PHP 7.2 à 7.4, le reste de la bibliothèque fonctionne normalement.

### Compatibilité PrestaShop 1.6

Cette bibliothèque peut également être utilisée avec **PrestaShop 1.6** en installant en complément la bibliothèque `prestashop/module-lib-service-container` qui fournit un conteneur de services compatible.

## Installation

### Pour PrestaShop 1.7.6+

```bash
composer require griiv/prestashop-module-contracts
```

### Pour PrestaShop 1.6

```bash
composer require griiv/prestashop-module-contracts
composer require prestashop/module-lib-service-container
```

## Utilisation

### 1. Créer votre module

Créez votre classe de module en étendant `ModuleAbstract` :

```php
<?php

namespace VotreNamespace;

use Griiv\Prestashop\Module\Contracts\Module\ModuleAbstract;

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
    }

    public function getHooks(): array
    {
        return [
            'displayHeader',
            'actionProductUpdate',
        ];
    }
}
```

### 2. Créer des handlers de hooks

#### Hook d'affichage (Display)

```php
<?php

namespace VotreNamespace\Display;

use Griiv\Prestashop\Module\Contracts\Hook\Hook;
use Griiv\Prestashop\Module\Contracts\Hook\Contracts\DisplayHookInterface;

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

#### Hook d'action (Action)

```php
<?php

namespace VotreNamespace\Action;

use Griiv\Prestashop\Module\Contracts\Hook\Hook;
use Griiv\Prestashop\Module\Contracts\Hook\Contracts\ActionHookInterface;

class ActionProductUpdate extends Hook implements ActionHookInterface
{
    public function action($params): bool
    {
        $product = $params['product'];
        // Votre logique métier ici
        return true;
    }
}
```

### 3. Utiliser les Concerns (Traits)

```php
<?php

namespace VotreNamespace;

use Griiv\Prestashop\Module\Contracts\Module\ModuleAbstract;
use Griiv\Prestashop\Module\Contracts\Concern\ModuleTrait;
use Griiv\Prestashop\Module\Contracts\Concern\IsUsingNewTranslationSystem;
use Griiv\Prestashop\Module\Contracts\Concern\IsMcpCompliant;

class VotreModule extends ModuleAbstract
{
    use ModuleTrait;
    use IsUsingNewTranslationSystem;
    use IsMcpCompliant;

    // Vous avez maintenant accès à :
    // - getTranslationDomain() : retourne le domaine de traduction
    // - getModuleToken(string $controller) : génère un token de sécurité
    // - isUsingNewTranslationSystem() : active le nouveau système de traduction
    // - isMcpCompliant() : déclare la compatibilité MCP
}
```

### 4. Attribut AsPrestaShopHook (PHP 8.0+)

Sur PHP 8.0+, vous pouvez déclarer vos hooks de manière déclarative avec l'attribut `#[AsPrestaShopHook]` :

```php
<?php

namespace VotreNamespace\Display;

use Griiv\Prestashop\Module\Contracts\Attribute\AsPrestaShopHook;
use Griiv\Prestashop\Module\Contracts\Hook\Hook;
use Griiv\Prestashop\Module\Contracts\Hook\Contracts\DisplayHookInterface;

#[AsPrestaShopHook(name: 'displayHeader', module: 'votremodule')]
class DisplayHeader extends Hook implements DisplayHookInterface
{
    public function display($params): string
    {
        // ...
    }
}
```

Couplé au `PrestaShopHookCompilerPass`, les hooks sont automatiquement enregistrés dans le conteneur Symfony.

### 5. Convention de nommage

Le système de résolution automatique des hooks suit ces conventions :

- **Display hooks** : `hookDisplayNomDuHook` → classe `VotreNamespace\Display\DisplayNomDuHook`
- **Action hooks** : `hookActionNomDuHook` → classe `VotreNamespace\Action\ActionNomDuHook`
- **Filter hooks** : `hookFilterNomDuHook` → classe `VotreNamespace\Filter\FilterNomDuHook`
- **Additional hooks** : `hookAdditionalNomDuHook` → classe `VotreNamespace\Additional\AdditionalNomDuHook`

### 6. Accès aux services Symfony

```php
// Obtenir le kernel Symfony
$kernel = static::getKernel();

// Récupérer un service
$service = static::getService('nom.du.service');

// Obtenir un paramètre
$parameter = static::getParameter('nom_du_parametre');

// Accéder à l'EntityManager Doctrine
$em = static::getEntityManager();
```

## Structure du projet

```
src/
├── Attribute/
│   └── AsPrestaShopHook.php              # Attribut PHP 8.0+ pour hooks
├── Concern/
│   ├── IsMcpCompliant.php                # Trait de compatibilité MCP
│   ├── IsUsingNewTranslationSystem.php   # Trait nouveau système de traduction
│   └── ModuleTrait.php                   # Trait avec méthodes utilitaires
├── DependencyInjection/
│   └── CompilerPass/
│       └── PrestaShopHookCompilerPass.php # CompilerPass Symfony pour hooks
├── Hook/
│   ├── Contracts/
│   │   ├── ActionHookInterface.php       # Interface pour hooks d'action
│   │   ├── AdditionalHookInterface.php   # Interface pour hooks additionnels
│   │   ├── DisplayHookInterface.php      # Interface pour hooks d'affichage
│   │   └── FilterHookInterface.php       # Interface pour hooks de filtrage
│   └── Hook.php                          # Classe de base pour les hooks
└── Module/
    ├── Contracts/
    │   └── ModuleInterface.php           # Interface du module
    └── ModuleAbstract.php                # Classe abstraite du module
```

## Licence

MIT License - voir le fichier [LICENSE](LICENSE)

## Auteur

Arnaud Scoté - [arnaud@griiv.fr](mailto:arnaud@griiv.fr)
