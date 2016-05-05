<?php

class SPODPUBLIC_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function settings($params)
    {
        $this->setPageTitle(OW::getLanguage()->text('spodpublic', 'admin_title'));
        $this->setPageHeading(OW::getLanguage()->text('spodpublic', 'admin_heading'));

        $this->assign('publicRoom', SPODPUBLIC_BOL_Service::getInstance()->getAgora());

        $deleteUrl = OW::getRouter()->urlFor(__CLASS__, 'delete');
        $this->assign('deleteUrl', $deleteUrl);

        $editUrl = OW::getRouter()->urlFor(__CLASS__, 'edit');
        $this->assign('editUrl', $editUrl);
    }

    public function delete()
    {
        if ( isset($_REQUEST['id']))
        {
            $id = $_REQUEST['id'];
            SPODPUBLIC_BOL_Service::getInstance()->removeRoom($id);
        }

        $this->redirect(OW::getRouter()->urlForRoute('public-room-settings'));
    }

    public function edit()
    {
        if ( isset($_REQUEST['id']))
        {
            $id = $_REQUEST['id'];
            $title = $_REQUEST['title'];
            SPODPUBLIC_BOL_Service::getInstance()->editRoom($id, $title);
        }

        $this->redirect(OW::getRouter()->urlForRoute('public-room-settings'));
    }
}