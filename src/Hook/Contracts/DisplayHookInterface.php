<?php


namespace Griiv\Prestashop\Module\Contracts\Hook\Contracts;

interface DisplayHookInterface
{

    public function display($params): string;
}