<?php

namespace Griiv\Prestashop\Module\Contracts\Hook\Contracts;

interface AdditionalHookInterface
{
    /**
     * @param array $params
     *
     * @return array Les elements a ajouter, agreges par le coeur de PrestaShop
     */
    public function additional($params): array;
}