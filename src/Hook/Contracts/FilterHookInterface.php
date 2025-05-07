<?php

namespace Griiv\Prestashop\Module\Contracts\Hook\Contracts;

interface FilterHookInterface
{

    public function filter($params): string;
}