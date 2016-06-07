<?php

class SPODPUBLIC_CMP_PublicRoomCreator extends OW_Component
{
    public function __construct()
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'input-menu.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('cocreation')->getStaticCssUrl() . 'input-menu.css');

        $this->assign('components_url', SPODPR_COMPONENTS_URL);

        $form = new Form('PublicRoomCreatorForm');

        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);

        $subject = new TextField('subject');
        $subject->setRequired(true);

        $body = new TextField('body');
        $body->setRequired(true);

        $submit = new Submit('submit');
        $submit->setValue('submit');

        $form->addElement($subject);
        $form->addElement($body);
        $form->addElement($submit);

        $form->setAction( OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('SPODPUBLIC_CTRL_Ajax', 'addPublicRoom')) );

        $this->addForm($form);

        $js = UTIL_JsGenerator::composeJsString('
            owForms["PublicRoomCreatorForm"].bind( "success", function( r )
            {
                addRoom(r)

                if ( r.error )
                {
                   OW.error(r.error); return;
                }

                if ( r.message ) {
                    OW.info(r.message);
                }

        });', array());

        OW::getDocument()->addOnloadScript( $js );

    }
}