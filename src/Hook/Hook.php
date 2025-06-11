<?php

namespace Griiv\Prestashop\Module\Contracts\Hook;

class Hook
{
    protected $context;

    protected \Module $module;
    protected $tpl = null;
    private $tplExt = '.tpl';
    protected $tplName = null;

    public function __construct(\Context $context)
    {
        $this->context = $context;
    }

    protected function getModule()
    {
        return $this->module;
    }

    public function setModule(\Module $module)
    {
        $this->module = $module;
    }

    protected function getTpl()
    {
        if (!$this->getModule() instanceof \Module) {
            throw new \Exception('Module not set');
        }

        if (null !== $this->tplName) {
            $this->tpl = $this->module->tplPath.$this->tplName.$this->tplExt;
        }

        if ($this->tpl === null) {
            throw new \Exception('Template not set, please set it with setTplName() method');;
        }

        return $this->tpl;
    }

    protected function setTplName($tplName)
    {
        $this->tplName = $tplName;
    }

    protected function getTplName()
    {
        return $this->tplName;
    }

    private function insertElementIntoArrayAfterSpecificKey(array $array, $key, array $new)
    {
        $keys = array_keys($array);
        $index = array_search($key, $keys, true);
        $pos = false === $index ? count($array) : $index + 1;
        return array_merge(array_slice($array, 0, $pos), $new, array_slice($array, $pos));
    }
}
