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

        $form = new Form('settings');
        $this->addForm($form);

        /* IsVisible */
        $is_visible = new CheckboxField('isVisible');
        $preference = BOL_PreferenceService::getInstance()->findPreference('agora_is_visible_not_logged');
        $is_visible_pref = empty($preference) ? "0" : $preference->defaultValue;
        $is_visible->setValue($is_visible_pref);
        $form->addElement($is_visible);

        $submit = new Submit('add');
        $submit->setValue('SAVE');
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST))
        {
            $data = $form->getValues();

            /* ode_deep_url */
            $preference = BOL_PreferenceService::getInstance()->findPreference('agora_is_visible_not_logged');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'agora_is_visible_not_logged';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['isVisible'];
            $preference->sortOrder = 1;
            BOL_PreferenceService::getInstance()->savePreference($preference);
        }

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