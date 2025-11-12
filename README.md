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
- **Trait utilitaire** : Méthodes helper pour les traductions et tokens de sécurité
- **Scripts d'installation automatisés** : Hooks Composer pour install/update/remove

## Prérequis

- PHP >= 7.2
- PrestaShop 1.7+ (avec support Symfony natif)
- Composer

### Compatibilité PrestaShop 1.6

Cette bibliothèque peut également être utilisée avec **PrestaShop 1.6** en installant en complément la bibliothèque `prestashop/module-lib-service-container` qui fournit un conteneur de services compatible.

## Installation

### Pour PrestaShop 1.7+

```bash
composer require griiv/prestashop-module-contracts
```

### Pour PrestaShop 1.6

```bash
composer require griiv/prestashop-module-contracts
composer require prestashop/module-lib-service-container
```

La bibliothèque `prestashop/module-lib-service-container` permet d'émuler le conteneur de services Symfony sur PrestaShop 1.6, rendant ainsi cette bibliothèque pleinement fonctionnelle.

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
            // ... autres hooks
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

        // Votre logique ici
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
        // Par exemple : log, mise à jour, synchronisation, etc.

        return true;
    }
}
```

#### Hook de filtrage (Filter)

```php
<?php

namespace VotreNamespace\Filter;

use Griiv\Prestashop\Module\Contracts\Hook\Hook;
use Griiv\Prestashop\Module\Contracts\Hook\Contracts\FilterHookInterface;

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

### 3. Convention de nommage

Le système de résolution automatique des hooks suit ces conventions :

- **Display hooks** : `hookDisplayNomDuHook` → classe `VotreNamespace\Display\DisplayNomDuHook`
- **Action hooks** : `hookActionNomDuHook` → classe `VotreNamespace\Action\ActionNomDuHook`
- **Filter hooks** : `hookFilterNomDuHook` → classe `VotreNamespace\Filter\FilterNomDuHook`
- **Additional hooks** : `hookAdditionalNomDuHook` → classe `VotreNamespace\Additional\AdditionalNomDuHook`

### 4. Accès aux services Symfony

La classe `ModuleAbstract` vous donne accès à plusieurs méthodes utilitaires :

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

### 5. Utiliser le trait ModuleTrait

```php
<?php

namespace VotreNamespace;

use Griiv\Prestashop\Module\Contracts\Module\ModuleAbstract;
use Griiv\Prestashop\Module\Contracts\Trait\ModuleTrait;

class VotreModule extends ModuleAbstract
{
    use ModuleTrait;

    // Maintenant vous avez accès à :
    // - getTranslationDomain() : retourne le domaine de traduction
    // - getModuleToken(string $controller) : génère un token de sécurité
}
```

## Structure du projet

```
src/
├── Hook/
│   ├── Contracts/
│   │   ├── ActionHookInterface.php      # Interface pour hooks d'action
│   │   ├── AdditionalHookInterface.php  # Interface pour hooks additionnels
│   │   ├── DisplayHookInterface.php     # Interface pour hooks d'affichage
│   │   └── FilterHookInterface.php      # Interface pour hooks de filtrage
│   └── Hook.php                         # Classe de base pour les hooks
├── Module/
│   ├── Contracts/
│   │   └── ModuleInterface.php          # Interface du module
│   └── ModuleAbstract.php               # Classe abstraite du module
└── Trait/
    └── ModuleTrait.php                  # Trait avec méthodes utilitaires
```

## Bénéfices

### Architecture et qualité du code

- **Respect des principes SOLID** :
  - Single Responsibility : un hook = une classe
  - Interface Segregation : interfaces séparées par type de hook
  - Dependency Inversion : programmation contre des abstractions
- **Tests unitaires facilités** : Interfaces permettant le mocking et injection de dépendances
- **Code maintenable** : Structure claire et facilement extensible
- **PSR-4** : Autoloading standard respectant les conventions PHP modernes

### Injection de dépendances et services

- **Chargement des services via DI/autowiring** : Les hooks peuvent recevoir leurs dépendances via le conteneur Symfony
- **Utilisation des services PrestaShop** : Accès aux services définis dans les fichiers YML de configuration
- **Lazy loading** : Les hooks ne sont instanciés que lorsqu'ils sont appelés, optimisant les performances

### Extensibilité et flexibilité

- **Surcharge/décoration des hooks** : Architecture permettant d'étendre ou modifier le comportement des hooks existants
- **Surcharge/décoration des services** : Possibilité de décorer n'importe quel service du module via le conteneur Symfony
- **Réutilisabilité** : Services et hooks réutilisables entre différents modules
- **Compatibilité écosystème Symfony** : Intégration native avec les bundles et composants Symfony

### Conventions et productivité

- **Conventions de nommage claires** : Réduction de la configuration grâce à des conventions explicites
- **Séparation des responsabilités** : Logique métier isolée de l'infrastructure PrestaShop
- **Type-safety** : Interfaces typées garantissant les signatures de méthodes

## Points d'attention

### Courbe d'apprentissage

- Nécessite une compréhension de Symfony et de l'injection de dépendances
- Changement de paradigme pour les développeurs habitués au style PrestaShop classique
- Convention de nommage stricte à respecter

### Considérations techniques

- **Magic methods (`__call`)** : Peut rendre le code moins découvrable pour les IDE sans configuration appropriée
- **Résolution dynamique** : Les erreurs de nommage de hooks sont détectées au runtime, pas à la compilation
- **Debugging** : La résolution dynamique peut compliquer le suivi de l'exécution
- **Prérequis PHP** : Nécessite PHP 7.2+ minimum, peut exclure certains projets legacy
- **Dépendance Symfony** : Requiert le kernel Symfony (natif en 1.7+, via lib en 1.6)

### Recommandations

- Utiliser un IDE avec support du PSR-4 et de l'autocomplétion Symfony (PHPStorm recommandé)
- Mettre en place des tests pour détecter les erreurs de nommage
- Documenter les hooks personnalisés pour faciliter la maintenance

## Scripts Composer

Le package inclut des scripts automatiques :

```json
{
  "scripts": {
    "post-install-cmd": "Installation automatique",
    "post-update-cmd": "Mise à jour automatique",
    "post-remove-cmd": "Désinstallation automatique"
  }
}
```

## Exemple complet

Consultez le code source pour voir un exemple complet d'implémentation. La structure suggérée pour votre module serait :

```
votremodule/
├── composer.json
├── votremodule.php (votre classe principale)
├── src/
│   ├── Display/
│   │   ├── DisplayHeader.php
│   │   └── DisplayFooter.php
│   ├── Action/
│   │   ├── ActionProductSave.php
│   │   └── ActionOrderStatusUpdate.php
│   └── Filter/
│       └── FilterProductSearch.php
└── views/
    └── templates/
        └── hook/
            ├── header.tpl
            └── footer.tpl
```

## Licence

MIT License - voir le fichier [LICENSE](LICENSE)

## Auteur

Arnaud Scoté - [arnaud@griiv.fr](mailto:arnaud@griiv.fr)

## Contributions

Les contributions sont les bienvenues ! N'hésitez pas à ouvrir une issue ou une pull request.

## Support

Pour toute question ou problème, veuillez ouvrir une issue sur le dépôt GitHub.
