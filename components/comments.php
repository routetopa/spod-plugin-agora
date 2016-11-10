<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

class SPODPUBLIC_CMP_Comments extends BASE_CMP_Comments
{
    /**
     * @var BASE_CommentsParams
     */
    public $params;
    public $batchData;
    public $staticData;
    public $id;
    public $cmpContextId;
    //protected $formName;
    public $isAuthorized;


    public function initForm()
    {
        $jsParams = array(
            'entityType'     => $this->params->getEntityType(),
            'entityId'       => $this->params->getEntityId(),
            'pluginKey'      => $this->params->getPluginKey(),
            'contextId'      => $this->cmpContextId,
            'userAuthorized' => $this->isAuthorized,
            'customId'       => $this->params->getCustomId(),
        );

        if ( $this->isAuthorized )
        {
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery.autosize.js');
            $taId = 'cta' . $this->id;
            $attchId = 'attch' . $this->id;
            $attchUid = BOL_CommentService::getInstance()->generateAttachmentUid($this->params->getEntityType(), $this->params->getEntityId());

            $jsParams['ownerId'] = $this->params->getOwnerId();
            $jsParams['cCount'] = isset($this->batchData['countOnPage']) ? $this->batchData['countOnPage'] : $this->params->getCommentCountOnPage();
            $jsParams['initialCount'] = $this->params->getInitialCommentsCount();
            $jsParams['loadMoreCount'] = $this->params->getLoadMoreCount();
            $jsParams['countOnPage'] = $this->params->getCommentCountOnPage();
            $jsParams['uid'] = $this->id;
            $jsParams['addUrl'] = OW::getRouter()->urlFor('SPODPUBLIC_CTRL_Comments', 'addComment');
            $jsParams['displayType'] = $this->params->getDisplayType();
            $jsParams['textAreaId'] = $taId;
            $jsParams['attchId'] = $attchId;
            $jsParams['attchUid'] = $attchUid;
            $jsParams['enableSubmit'] = true;
            $jsParams['mediaAllowed'] = BOL_TextFormatService::getInstance()->isCommentsRichMediaAllowed();
            $jsParams['labels'] = array(
                'emptyCommentMsg' => OW::getLanguage()->text('base', 'empty_comment_error_msg'),
                'disabledSubmit' => OW::getLanguage()->text('base', 'submit_disabled_error_msg'),
                'attachmentLoading' => OW::getLanguage()->text('base', 'submit_attachment_not_loaded'),
            );

            if ( !empty($this->staticData['currentUserInfo']) )
            {
                $userInfoToAssign = $this->staticData['currentUserInfo'];
            }
            else
            {
                $currentUserInfo = BOL_AvatarService::getInstance()->getDataForUserAvatars(array(OW::getUser()->getId()));
                $userInfoToAssign = $currentUserInfo[OW::getUser()->getId()];
            }

            $buttonContId = 'bCcont' . $this->id;

            if ( BOL_TextFormatService::getInstance()->isCommentsRichMediaAllowed() )
            {
                $this->addComponent('attch', new BASE_CLASS_Attachment($this->params->getPluginKey(), $attchUid, $buttonContId));
                //$this->addComponent('file_attch', new SPODTCHAT_CLASS_FileAttachment($this->params->getPluginKey(), $attchUid, $buttonContId));
            }

            $this->assign('buttonContId', $buttonContId);
            $this->assign('currentUserInfo', $userInfoToAssign);
            $this->assign('formCmp', true);
            $this->assign('taId', $taId);
            $this->assign('attchId', $attchId);
            $this->assign('commentId', $this->params->getEntityId());

            $this->assign("temp_attach_uid", $attchUid);
            $this->assign('theme_image_url', OW::getThemeManager()->getInstance()->getThemeImagesUrl());
        }

        OW::getDocument()->addOnloadScript("new OwComments(" . json_encode($jsParams) . ");");

        $this->assign('displayType', $this->params->getDisplayType());

        // add comment list cmp
        $this->addComponent('commentList', new SPODPUBLIC_CMP_CommentsList($this->params, $this->id));
    }

}
