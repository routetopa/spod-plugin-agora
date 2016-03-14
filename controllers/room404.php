<?php

class SPODPUBLIC_CTRL_Room404 extends OW_ActionController
{
    public function index()
    {
        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('spodpublic')->getRootDir() . 'master_pages/empty.html');
        $this->assign('error_message', "The Room you are looking for was deleted");
    }
}