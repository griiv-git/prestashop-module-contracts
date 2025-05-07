#!/usr/bin/env php
<?php
/**
 * Script de gestion du fichier services_griiv.yml
 * Usage :
 *   php config/install.php install   // copie + import
 *   php config/install.php update    // idem install
 *   php config/install.php remove    // suppression + nettoyage import
 */

$projectDir = realpath(__DIR__ . '/..');
$vendorConfig = $projectDir . '/vendor/griiv/prestashop-module-contracts/config/services_griiv.yml';
$dest1       = $projectDir . '/config/services/services_griiv.yml';
$dest2       = $projectDir . '/app/config/services_griiv.yml';
$appServices = $projectDir . '/app/config/services.yml';
$commonYaml  = $projectDir . '/config/services/common.yml';

$cmd = $argv[1] ?? null;

switch ($cmd) {
    case 'install':
    case 'update':
        copyServices();
        importServices();
        exit(0);

    case 'remove':
        removeServices();
        exit(0);

    default:
        fwrite(STDERR, "Usage: php config/install.php [install|update|remove]\n");
        exit(1);
}

function copyServices(): void
{
    global $vendorConfig, $dest1, $dest2;
    if (!file_exists($vendorConfig)) {
        fwrite(STDERR, "Le fichier source n'existe pas: $vendorConfig\n");
        return;
    }
    foreach ([$dest1, $dest2] as $dest) {
        $dir = dirname($dest);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        copy($vendorConfig, $dest);
        echo "Copié : $vendorConfig → $dest\n";
    }
}

function importServices(): void
{
    global $appServices, $commonYaml;
    $importBlock = "\n# Griiv services\nimports:\n  - { resource: services_griiv.yml }\n";

    foreach ([$appServices, $commonYaml] as $file) {
        if (!is_file($file)) {
            continue;
        }
        $content = file_get_contents($file);
        if (strpos($content, 'services_griiv.yml') !== false) {
            continue;
        }
        $new = preg_replace(
            '/^services:/m',
            $importBlock . "services:",
            $content,
            1
        );
        file_put_contents($file, $new);
        echo "Import ajouté dans : $file\n";
    }
}

function removeServices(): void
{
    global $dest1, $dest2, $appServices, $commonYaml;
    // suppression des fichiers copiés
    foreach ([$dest1, $dest2] as $f) {
        if (is_file($f)) {
            unlink($f);
            echo "Supprimé : $f\n";
        }
    }
    // retrait du bloc d'import
    $removeBlock = "\n# Griiv services\nimports:\n  - { resource: services_griiv.yml }\n";
    foreach ([$appServices, $commonYaml] as $file) {
        if (!is_file($file)) {
            continue;
        }
        $content = file_get_contents($file);
        $clean   = str_replace($removeBlock, '', $content);
        file_put_contents($file, $clean);
        echo "Import retiré dans : $file\n";
    }
}
