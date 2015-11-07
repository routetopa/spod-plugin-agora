<?php

class SPODPUBLIC_CTRL_Main extends OW_ActionController
{
    public function index()
    {
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodpublic')->getStaticUrl() . 'css/public_room.css');
        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('spodpublic')->getRootDir() . 'master_pages/general.html');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'masonry.pkgd.min.js', 'text/javascript');

        $this->assign('components_url', SPODPR_COMPONENTS_URL);
    }

}