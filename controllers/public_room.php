<?php

class SPODPUBLIC_CTRL_PublicRoom extends OW_ActionController
{
    public static $commentNodes = array();
    public static $commentLinks = array();

    public static $dataletNodes = array();
    public static $dataletLinks = array();

    private $public_room = null;

    public function index(array $params)
    {
        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('spodpublic')->getRootDir() . 'master_pages/empty.html');

        if ( isset($params['prId']) )
        {
            OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodpublic')->getStaticCssUrl() . 'perfect-scrollbar.min.css');

            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'commentsList.js');
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'public_room.js');
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'jquery-ui.min.js');
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'perfect-scrollbar.jquery.js');

            //add deep component url
            $this->assign('components_url', SPODPR_COMPONENTS_URL);

            $public_room_id = $params['prId'];
            $this->public_room = SPODPUBLIC_BOL_Service::getInstance()->getPublicRoomById($public_room_id);
            $this->assign('public_room', $this->public_room);

            /* ODE */
            if(OW::getPluginManager()->isPluginActive('spodpr'))
                $this->addComponent('private_room', new SPODPR_CMP_PrivateRoomCard('ow_attachment_btn'));
            /* ODE */

            SPODPUBLIC_BOL_Service::getInstance()->addStat($this->public_room->id, 'views');

            //comment and rate
            $commentsParams = new BASE_CommentsParams('spodpublic', SPODPUBLIC_BOL_Service::ENTITY_TYPE);
            $commentsParams->setEntityId($public_room_id);
            $commentsParams->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST);
            $commentsParams->setCommentCountOnPage(5);
            $commentsParams->setOwnerId((OW::getUser()->getId()));
            $commentsParams->setAddComment(TRUE);
            $commentsParams->setWrapInBox(false);
            $commentsParams->setShowEmptyList(false);

            $commentsParams->level = 0;
            $commentsParams->nodeId = 0;

            $commentCmp = new SPODPUBLIC_CMP_Comments($commentsParams);
            $this->addComponent('comments', $commentCmp);

            $js = UTIL_JsGenerator::composeJsString('
                    SPODPUBLICROOM.get_graph_url              = {$get_graph_url};
                    SPODPUBLICROOM.public_room_id             = {$public_room_id};
                    SPODPUBLICROOM.suggested_datasets         = {$suggested_datasets};
                ', array(
                'get_graph_url'              => OW::getRouter()->urlFor('SPODPUBLIC_CTRL_Ajax', 'getGraph'),
                'public_room_id'             => $this->public_room->id,
                'suggested_datasets'         => SPODPUBLIC_BOL_Service::getInstance()->getJsPublicRoomSuggestionByIdAndOwner($public_room_id,OW::getUser()->getId())
            ));

            OW::getDocument()->addOnloadScript($js);
        }
    }
}