<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

class SPODPUBLIC_CMP_CommentsList extends BASE_CMP_CommentsList
{
	protected $actionArr = array('comments' => array(), 'users' => array(), 'abuses' => array(), 'remove_abuses' => array());

	
	protected function init()
    {
        if ( $this->commentCount === 0 && $this->params->getShowEmptyList() )
        {
            $this->assign('noComments', true);
        }

        $countToLoad = 0;

        if ( $this->commentCount === 0 )
        {
            $commentList = array();
        }
        else if ( in_array($this->params->getDisplayType(), array(BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST, BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST_MINI)) )
        {	
            $commentList = empty($this->batchData['commentsList']) ? $this->commentService->findCommentList($this->params->getEntityType(), $this->params->getEntityId(), 1, $this->params->getInitialCommentsCount()) : $this->batchData['commentsList'];
            $commentList = array_reverse($commentList);
            $countToLoad = $this->commentCount - $this->params->getInitialCommentsCount();
            $this->assign('countToLoad', $countToLoad);
        }
        else
        {
            $commentList = $this->commentService->findCommentList($this->params->getEntityType(), $this->params->getEntityId(), $this->page, $this->params->getCommentCountOnPage());
        }

        OW::getEventManager()->trigger(new OW_Event('base.comment_list_prepare_data', array('list' => $commentList, 'entityType' => $this->params->getEntityType(), 'entityId' => $this->params->getEntityId())));
        OW::getEventManager()->bind('base.comment_item_process', array($this, 'itemHandler'));
        $this->assign('comments', $this->processList($commentList));

        $pages = false;

        if ( $this->params->getDisplayType() === BASE_CommentsParams::DISPLAY_TYPE_WITH_PAGING )
        {
            $pagesCount = $this->commentService->findCommentPageCount($this->params->getEntityType(), $this->params->getEntityId(), $this->params->getCommentCountOnPage());

            if ( $pagesCount > 1 )
            {
                $pages = $this->getPages($this->page, $pagesCount, 8);
                $this->assign('pages', $pages);
            }
        }
        else
        {
            $pagesCount = 0;
        }

        $this->assign('loadMoreLabel', OW::getLanguage()->text('base', 'comment_load_more_label'));

        static $dataInit = false;

        if ( !$dataInit )
        {
            $staticDataArray = array(
                'respondUrl'      => OW::getRouter()->urlFor('SPODPUBLIC_CTRL_Comments', 'getCommentList'),//when page button is being pressed
                'delUrl'          => OW::getRouter()->urlFor('SPODPUBLIC_CTRL_Comments', 'deleteComment'),
                'delAtchUrl'      => OW::getRouter()->urlFor('SPODPUBLIC_CTRL_Comments', 'deleteCommentAtatchment'),
                'delConfirmMsg'   => OW::getLanguage()->text('base', 'comment_delete_confirm_message'),
                'preloaderImgUrl' => OW::getThemeManager()->getCurrentTheme()->getStaticImagesUrl() . 'ajax_preloader_button.gif'
            );
            OW::getDocument()->addOnloadScript("window.owCommentListCmps.staticData=" . json_encode($staticDataArray) . ";");
            $dataInit = true;
        }

        $jsParams = json_encode(
            array(
                'totalCount'         => $this->commentCount,
                'contextId'          => $this->cmpContextId,
                'displayType'        => $this->params->getDisplayType(),
                'entityType'         => $this->params->getEntityType(),
                'entityId'           => $this->params->getEntityId(),
                'pagesCount'         => $pagesCount,
                'initialCount'       => $this->params->getInitialCommentsCount(),
                'loadMoreCount'      => $this->params->getLoadMoreCount(),
                'commentIds'         => $this->commentIdList,
                'pages'              => $pages,
                'pluginKey'          => $this->params->getPluginKey(),
                'ownerId'            => $this->params->getOwnerId(),
                'commentCountOnPage' => $this->params->getCommentCountOnPage(),
                'cid'                => $this->id,
                'actionArray'        => $this->actionArr,
                'countToLoad'        => $countToLoad
            )
        );

        OW::getDocument()->addOnloadScript(
            "window.owCommentListCmps.items['$this->id'] = new SpodpublicCommentsList($jsParams);
             window.owCommentListCmps.items['$this->id'].init();"
        );
    }
	
	
	public function itemHandler( BASE_CLASS_EventProcessCommentItem $e )
    {
        $language = OW::getLanguage();

        $deleteButton = false;
        $cAction = null;
        $value = $e->getItem();

        if ( $this->isOwnerAuthorized || $this->isModerator || (int) OW::getUser()->getId() === (int) $value->getUserId() )
        {
            $deleteButton = true;
        }

        if ( $this->isBaseModerator || $deleteButton ) {
            $cAction = new BASE_CMP_ContextAction();
            $parentAction = new BASE_ContextAction();
            $parentAction->setKey('parent');
            $parentAction->setClass('ow_comments_context');
            $cAction->addAction($parentAction);

            if ($deleteButton) {
                $delAction = new BASE_ContextAction();
                $delAction->setLabel($language->text('base', 'contex_action_comment_delete_label'));
                $delAction->setKey('udel');
                $delAction->setParentKey($parentAction->getKey());
                $delId = 'del-' . $value->getId();
                $delAction->setId($delId);
                $this->actionArr['comments'][$delId] = $value->getId();
                $cAction->addAction($delAction);
            }

            if ($this->isBaseModerator && $value->getUserId() != OW::getUser()->getId()) {
                $modAction = new BASE_ContextAction();
                $modAction->setLabel($language->text('base', 'contex_action_user_delete_label'));
                $modAction->setKey('cdel');
                $modAction->setParentKey($parentAction->getKey());
                $delId = 'udel-' . $value->getId();
                $modAction->setId($delId);
                $this->actionArr['users'][$delId] = $value->getUserId();
                $cAction->addAction($modAction);
            }
        }

        if ( $this->params->getCommentPreviewMaxCharCount() > 0 && mb_strlen($value->getMessage()) > $this->params->getCommentPreviewMaxCharCount() )
        {
            $e->setDataProp('previewMaxChar', $this->params->getCommentPreviewMaxCharCount());
        }

        $e->setDataProp('cnxAction', empty($cAction) ? '' : $cAction->render());
    }

    protected function processList( $commentList )
    {

        /* @var $value BOL_Comment */
        foreach ( $commentList as $value )
        {
            $this->userIdList[] = $value->getUserId();
            $this->commentIdList[] = $value->getId();
        }

        $userAvatarArrayList = empty($this->staticData['avatars']) ? $this->avatarService->getDataForUserAvatars($this->userIdList) : $this->staticData['avatars'];


        foreach ( $commentList as $value )
        {
            /*Add nasted level*/
            if($this->params->level <= 2) {
                //nasted comment
                $commentsParams = new BASE_CommentsParams('spodpublic', SPODPR_BOL_Service::ENTITY_TYPE);
                $commentsParams->setEntityId($value->getId());
                $commentsParams->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST_MINI);
                $commentsParams->setCommentCountOnPage(5);
                $commentsParams->setOwnerId((OW::getUser()->getId()));
                $commentsParams->setAddComment(TRUE);
                $commentsParams->setWrapInBox(false);
                $commentsParams->setShowEmptyList(false);
                $commentsParams->level = $this->params->level + 1;



                array_push(SPODPUBLIC_CTRL_Test::$nodes, array($value->getId(), $value->getMessage(), $this->params->level));
                array_push(SPODPUBLIC_CTRL_Test::$links, array($this->params->getEntityId(), $value->getId(), $this->params->level));

                $this->addComponent('nestedComments' . $value->getId(), new SPODPUBLIC_CMP_Comments($commentsParams));

                OW::getDocument()->addOnloadScript(
                    "$(document).ready(function(){
                        $('#spod_public_room_nested_comment_show_" . $value->getId() . "').click(function(){
                              if($('#nc_" . $value->getId() . "').css('display') == 'none')
                              {
                                 $('#nc_" . $value->getId() . "').css('display', 'block');
                              }else{
                                 $('#nc_" . $value->getId() . "').css('display', 'none');
                              }
                           });
                    });"
                );

                $this->assign('commentsCount' . $value->getId(), BOL_CommentService::getInstance()->findCommentCount(SPODPR_BOL_Service::ENTITY_TYPE, $value->getId()));
                $this->assign('commentsLevel' . $value->getId(), $this->params->level);
            }

            /*End adding nasted level*/

            $cmItemArray = array(
                'displayName' => $userAvatarArrayList[$value->getUserId()]['title'],
                'avatarUrl'   => $userAvatarArrayList[$value->getUserId()]['src'],
                'profileUrl'  => $userAvatarArrayList[$value->getUserId()]['url'], 
                'content'     => $value->getMessage(),
                'date'        => UTIL_DateTime::formatDate($value->getCreateStamp()),
                'userId'      => $value->getUserId(),
                'commentId'   => $value->getId(),
                'avatar'      => $userAvatarArrayList[$value->getUserId()]
            );

            $contentAdd = '';

            if ( $value->getAttachment() !== null )
            {
                $tempCmp = new BASE_CMP_OembedAttachment((array) json_decode($value->getAttachment()), $this->isOwnerAuthorized);
                $contentAdd .= '<div class="ow_attachment ow_small" id="att' . $value->getId() . '">' . $tempCmp->render() . '</div>';
            }

            $cmItemArray['content_add'] = $contentAdd;

            $event = new BASE_CLASS_EventProcessCommentItem('base.comment_item_process', $value, $cmItemArray);
            OW::getEventManager()->trigger($event);
            $arrayToAssign[] = $event->getDataArr();

        }

        return $arrayToAssign;
    }
}

?>