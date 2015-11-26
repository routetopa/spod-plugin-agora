<?php

class SPODPUBLIC_CTRL_Main extends OW_ActionController
{
    private $field;

    public function index()
    {
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodpublic')->getStaticUrl() . 'css/public_room.css');
        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('spodpublic')->getRootDir() . 'master_pages/general.html');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'masonry.pkgd.min.js', 'text/javascript');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'public_room.js', 'text/javascript');

        $agora = SPODPUBLIC_BOL_Service::getInstance()->getAgora();
        $timeSortedAgora = $agora;


        $this->field = "opendata";
        usort($agora, array($this, "compare"));
        $this->assign('openDataMedian', $this->median($agora, 'opendata'));

        $this->field = "comments";
        usort($agora, array($this, "compare"));
        $this->assign('commentsMedian', $this->median($agora, 'comments'));

        $this->assign('components_url', SPODPR_COMPONENTS_URL);
        $this->assign('rooms', $timeSortedAgora);
    }

    private function median($a, $value)
    {
        $count = count($a);

        if ($count%2) {
            return $a[($count+1)/2]->$value;
        } else {
            return ($a[$count/2]->$value + $a[$count/2-1]->$value) / 2;
        }
    }

    private function compare($a, $b)
    {
        $field = $this->field;

        if($a->opendata == $b->opendata) return 0;
            return ($a->$field < $b->$field) ? -1:1;
    }

}