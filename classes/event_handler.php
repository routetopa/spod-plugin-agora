<?php

class SPODPUBLIC_CLASS_EventHandler{

    private static $classInstance;

    public static function getInstance()
    {
        if(self::$classInstance === null)
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    // Handle event
    public function init()
    {
        // event raised just before rendering a comment
        OW::getEventManager()->bind('base.comment_item_process', array($this, 'onCommentItemProcess'), 10000);
    }

    // Render comment
    public function onCommentItemProcess(BASE_CLASS_EventProcessCommentItem $event)
    {
        $comment = $event->getItem();
        $id = $comment->getId();

        OW::getDocument()->addOnloadScript('$("#datalet_placeholder_' . $id . '_comment").css("display", "none");');

        $datalet = ODE_BOL_Service::getInstance()->getDataletByPostIdWhereArray($id, array("comment", "public-room"));

        if(!empty($datalet)) {

            OW::getDocument()->addOnloadScript('
               $("#comment_bar_' . $id . '").append("<paper-fab mini class=\'show_datalet\' icon=\'assessment\' style=\'float:left;\' id=\'show_datalet_comment_' . $id .'\'></paper-fab>");
               $("#show_datalet_comment_' . $id .'").click(function(){
                     $("#datalet_placeholder_' . $id . '_comment").toggle(\'fade\', {direction: \'top\'}, 500);
               });
            ');
        }
    }

}