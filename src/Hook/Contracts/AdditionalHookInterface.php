<?php

namespace Griiv\Prestashop\Module\Contracts\Hook\Contracts;

interface AdditionalHookInterface
{
    public function additional($params): void;
}