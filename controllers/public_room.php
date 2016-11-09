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
        //Check if user can view this page
        $preference = BOL_PreferenceService::getInstance()->findPreference('agora_is_visible_not_logged');
        $is_visible_pref = empty($preference) ? "false" : $preference->defaultValue;

        if ( !$is_visible_pref && !OW::getUser()->isAuthenticated())
        {
            throw new AuthenticateException();
        }
        else
        {
            /*if(!OW::getUser()->isAuthenticated() && OW::getPluginManager()->isPluginActive('openidconnect'))
            {
                $this->addComponent('authentication_component', new SPODPUBLIC_CMP_AuthenticationComponent());
            }*/
        }

        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('spodpublic')->getRootDir() . 'master_pages/empty.html');

        if ( isset($params['prId']) )
        {
            OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodpublic')->getStaticCssUrl() . 'perfect-scrollbar.min.css');

            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'commentsList.js');
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'agora.js');
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'public_room.js');
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'jquery-ui.min.js');
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'perfect-scrollbar.jquery.js');

            OW::getLanguage()->addKeyForJs('spodpublic', 'comments_graph');
            OW::getLanguage()->addKeyForJs('spodpublic', 'datalets_graph');
            OW::getLanguage()->addKeyForJs('spodpublic', 'users_graph');
            OW::getLanguage()->addKeyForJs('spodpublic', 'opinions_graph');
            OW::getLanguage()->addKeyForJs('spodpublic', 'graph_panel');
            OW::getLanguage()->addKeyForJs('spodpublic', 'open_graph_panel');

            SPODPUBLIC_CLASS_EventHandler::getInstance()->init();

            //add deep component url
            $this->assign('components_url', SPODPR_COMPONENTS_URL);

            $public_room_id = $params['prId'];
            $this->public_room = SPODPUBLIC_BOL_Service::getInstance()->getPublicRoomById($public_room_id);

            if($this->public_room != null)
            {
                $this->assign('public_room', $this->public_room);

                /* ODE */
                if (OW::getPluginManager()->isPluginActive('spodpr'))
                    $this->addComponent('private_room', new SPODPR_CMP_PrivateRoomCard('ow_attachment_btn', array('datalet', 'link'), "public-room"));
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
                $commentsParams->setCommentPreviewMaxCharCount(5000);
                //$commentsParams->setInitialCommentsCount(1000);

                $commentsParams->level = 0;
                $commentsParams->nodeId = 0;

                //$helperCmp = new SPODPUBLIC_CMP_HelperPublicRoom();
                //$helperCmp = new SPODPUBLIC_CMP_HelperAgora();
                //$helperCmp = new SPODPUBLIC_CMP_HelperMySpace();
                //$this->addComponent('helper', $helperCmp);

                $commentCmp = new SPODPUBLIC_CMP_Comments($commentsParams);
                //$commentCmp = new SPODTCHAT_CMP_Comments($commentsParams);
                $this->addComponent('comments', $commentCmp);

                $js = UTIL_JsGenerator::composeJsString('
                    SPODPUBLICROOM.get_graph_url              = {$get_graph_url};
                    SPODPUBLICROOM.public_room_id             = {$public_room_id};
                    SPODPUBLICROOM.suggested_datasets         = {$suggested_datasets};
                    SPODPUBLICROOM.current_user_id            = {$current_user_id};
                    SPODPUBLICROOM.staticResourceUrl          = {$staticResourceUrl}
                ', array(
                    'get_graph_url'      => OW::getRouter()->urlFor('SPODPUBLIC_CTRL_Ajax', 'getGraph'),
                    'public_room_id'     => $this->public_room->id,
                    'suggested_datasets' => SPODPUBLIC_BOL_Service::getInstance()->getJsPublicRoomSuggestionByIdAndOwner($public_room_id, OW::getUser()->getId()),
                    'current_user_id'    => OW::getUser()->getId(),
                    'staticResourceUrl'  => OW::getPluginManager()->getPlugin('spodpublic')->getStaticUrl()
                ));

                OW::getDocument()->addOnloadScript($js);
            }
            else
            {
                $this->redirect(OW::getRouter()->urlFor("SPODPUBLIC_CTRL_Room404", "index"));
            }
        }
    }
}