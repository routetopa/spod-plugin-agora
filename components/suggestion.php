<?php

class SPODPUBLIC_CMP_Suggestion extends OW_Component
{
    public function __construct($publicRoomId)
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'input-menu.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('cocreation')->getStaticCssUrl() . 'input-menu.css');
        
        $form = new Form('PublicRoomSuggestionForm');

        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);

        $publicRoom = new HiddenField('publicRoomId');
        $publicRoom->setValue($publicRoomId);

        $dataset = new TextField('dataset');
        $dataset->setRequired(true);

        $comment = new TextField('comment');
        $comment->setRequired(true);

        $submit = new Submit('submit');
        $submit->setValue('submit');

        $form->addElement($publicRoom);
        $form->addElement($dataset);
        $form->addElement($comment);
        $form->addElement($submit);

        $form->setAction( OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('SPODPUBLIC_CTRL_Ajax', 'addPublicRoomSuggestion')) );

        $this->addForm($form);

        $this->assign('suggestions', SPODPUBLIC_BOL_Service::getInstance()->getPublicRoomSuggestionByIdAndOwner(
            $publicRoomId,OW::getUser()->getId()));

        $this->assign('themeImagesUrl', OW::getThemeManager()->getThemeImagesUrl());

        $js = UTIL_JsGenerator::composeJsString('
            owForms["PublicRoomSuggestionForm"].bind( "success", function( r )
            {
                addRow(r)

                if ( r.error )
                {
                   OW.error(r.error); return;
                }

                if ( r.message ) {
                    OW.info(r.message);
                }

        });', array());

        OW::getDocument()->addOnloadScript($js);

        $js = UTIL_JsGenerator::composeJsString('
            SPODPUBLICROOM.ajax_remove_suggestion = {$ajax_remove_suggestion}', array(
            'ajax_remove_suggestion' => OW::getRouter()->urlFor('SPODPUBLIC_CTRL_Ajax', 'removePublicRoomSuggestion')
        ));

        OW::getDocument()->addOnloadScript($js);

    }
}