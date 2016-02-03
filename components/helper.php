<?php
class SPODPUBLIC_CMP_Helper extends OW_Component
{
    public function __construct()
    {
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodpublic')->getStaticCssUrl() . 'perfect-scrollbar.min.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'perfect-scrollbar.jquery.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'helper.js');

        $this->assign("staticResourcesUrl", OW::getPluginManager()->getPlugin('spodpublic')->getStaticUrl());
        $this->assign('components_url', SPODPR_COMPONENTS_URL);
    }
}