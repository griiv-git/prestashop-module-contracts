<?php

namespace Griiv\Prestashop\Module\Contracts\Module\Contracts;

use Symfony\Component\HttpKernel\KernelInterface;

interface ModuleInterface
{
    public function getHooks(): array;

    public function __call(string $name, array $args);

    public static function getKernel(): KernelInterface;

    public static function getService(string $serviceName): object;

    public static function getParameter(string $key): string;

    public static function getEntityManager(): \Doctrine\ORM\EntityManagerInterface;

}