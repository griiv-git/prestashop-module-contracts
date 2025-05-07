<?php

namespace Griiv\Prestashop\Module\Contracts\Hook;

use Context;
use DgtxContactForm;

class Hook
{
    protected $context;
    protected $module;
    protected $tpl = null;
    private $tplExt = '.tpl';
    protected $tplName = null;

    public function __construct(\Module $module, \Context $context)
    {
        $this->context = $context;
        $this->module = $module;

        if (null !== $this->tplName) {
            $this->tpl = $this->module->tplPath.$this->tplName.$this->tplExt;
        }
    }
}
