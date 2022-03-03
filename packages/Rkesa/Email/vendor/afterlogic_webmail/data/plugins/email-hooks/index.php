<?php

// https://afterlogic.com/docs/webmail-pro/integration-and-development/javascript-api

class_exists('CApi') or die();

class CMyCustomPlugin extends AApiPlugin
{
    /**
    * @param CApiPluginManager $oPluginManager
    */
    public function __construct(CApiPluginManager $oPluginManager)
    {
        parent::__construct('1.0', $oPluginManager);
    }

    public function Init()
    {
        parent::Init();

        $this->AddJsFile('js/send-hook.js');
    }
}

return new CMyCustomPlugin($this);
