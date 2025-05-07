<?php

namespace Griiv\Prestashop\Module\Contracts\Hook\Contracts;

interface ActionHookInterface
{
    public function action($params): bool;
}