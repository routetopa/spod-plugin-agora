<?php

class SPODPUBLIC_BOL_Service
{
    const ENTITY_TYPE         = 'spodpublic_topic_entity';
    const ENTITY_TYPE_COMMENT = 'spodpublic_topic_entity_comment';

    /**
     * Singleton instance.
     *
     * @var SPODPUBLIC_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return SPODPUBLIC_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    // READER

    public function getAgora()
    {
        //return SPODPUBLIC_BOL_PublicRoomDao::getInstance()->findAll();
        $example = new OW_Example();
        $example->setOrder('timestamp DESC');
        return SPODPUBLIC_BOL_PublicRoomDao::getInstance()->findListByExample($example);
    }

    public function getPublicRoomById($id)
    {
        $example = new OW_Example();
        $example->andFieldEqual('id', $id);
        return SPODPUBLIC_BOL_PublicRoomDao::getInstance()->findObjectByExample($example);
    }

    public function getPublicRoomsByOwner($ownerId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('ownerId', $ownerId);
        $example->setOrder('timestamp DESC');
        return SPODPUBLIC_BOL_PublicRoomDao::getInstance()->findListByExample($example);
    }

    public function getPublicRoomSuggestionByIdAndOwner($publicRoomId, $ownerId)
    {
        $example = new OW_Example();
        //$example->andFieldEqual('ownerId', intval($ownerId));
        $example->andFieldEqual('publicRoomId', intval($publicRoomId));
        return SPODPUBLIC_BOL_PublicRoomSuggestionDao::getInstance()->findListByExample($example);
    }

    public function getJsPublicRoomSuggestionByIdAndOwner($publicRoomId, $ownerId)
    {
        $s = $this->getPublicRoomSuggestionByIdAndOwner($publicRoomId, $ownerId);
        $a = array();

        foreach($s as $suggestion)
        {
            array_push($a, array("resource_name" => $suggestion->comment,
                "url" => $suggestion->dataset,
                "metas" => json_encode(["description" => ""])));
        }

        return json_encode($a);
    }

    public function getEntityId($id)
    {
        $dbo = OW::getDbo();
        $query = "SELECT * FROM ow_base_comment_entity WHERE entityId = " . $id . ";";
        return $dbo->queryForRow($query);
    }

    public function getCommentSentiment($commentId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('commentId', $commentId);
        return SPODPUBLIC_BOL_PublicRoomCommentSentimentDao::getInstance()->findObjectByExample($example);
    }

    // WRITER

    public function addPublicRoomSuggestion($ownewId, $publicRoomId, $dataset, $comment)
    {
        $publicRoomSuggestion = new SPODPUBLIC_BOL_PublicRoomSuggestion();
        $publicRoomSuggestion->ownerId      = intval($ownewId);
        $publicRoomSuggestion->publicRoomId = intval($publicRoomId);
        $publicRoomSuggestion->dataset      = $dataset;
        $publicRoomSuggestion->comment      = $comment;
        SPODPUBLIC_BOL_PublicRoomSuggestionDao::getInstance()->save($publicRoomSuggestion);
        return $publicRoomSuggestion->id;
    }

    public function removePublicRoomSuggestion($ownerId, $id)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('ownerId', $ownerId);
        $ex->andFieldEqual('id', $id);
        SPODPUBLIC_BOL_PublicRoomSuggestionDao::getInstance()->deleteByExample($ex);
    }

    public function addPublicRoom($ownerId, $subject, $body)
    {
        $pr = new SPODPUBLIC_BOL_PublicRoom();
        $pr->ownerId   = $ownerId;
        $pr->subject   = strip_tags($subject);
        $pr->body      = strip_tags($body);
        $pr->views     = 0;
        $pr->comments  = 0;
        $pr->opendata  = 0;
        $pr->post      = json_encode(["timestamp"=>time(), "opendata"=>$pr->opendata, "comments"=>$pr->comments, "views"=>$pr->views]);
        SPODPUBLIC_BOL_PublicRoomDao::getInstance()->save($pr);

        $event = new OW_Event('feed.action', array(
            'pluginKey' => 'spodpublic',
            'entityType' => 'spodpublic_public-room',
            'entityId' => $pr->id,
            'userId' => $ownerId
        ), array(

            'time' => time(),
            'string' => array('key' => 'spodpublic+create_new_room', 'vars'=>array('roomId' => $pr->id, 'roomSubject' => $subject))
            /*,'view' => array(
                'iconClass' => 'ow_ic_add'
            )*/
        ));
        OW::getEventManager()->trigger($event);

        return $pr->id;
    }

    public function addStat($id, $stat)
    {
        $pr = $this->getPublicRoomById($id);
        $pr->$stat += 1;
        SPODPUBLIC_BOL_PublicRoomDao::getInstance()->save($pr);

        $room = SPODPUBLIC_BOL_Service::getInstance()->getPublicRoomById($id);
        $room->post = json_decode($room->post);

        $delta = $room->$stat - $room->post->$stat;
        $prctg = ($delta * 100) / ($room->$stat > 0 ? $room->$stat : 1);

        if( $delta > constant('SPODPUBLIC_'.strtoupper($stat).'_THRESHOLD') ||
            ($prctg > constant('SPODPUBLIC_'.strtoupper($stat).'_PRCTG')
                                        && 
             $delta > constant('SPODPUBLIC_'.strtoupper($stat).'_MIN'))
        )
        {
            $room->post->$stat = $room->$stat;
            $this->addPostStat($room);
            return $delta;
        }

        return false;
    }

    public function addPostStat($room)
    {
        $room->timestamp = time();
        $room->post = json_encode($room->post);
        SPODPUBLIC_BOL_PublicRoomDao::getInstance()->save($room);
    }

    public function addCommentSentiment($publicRoom, $commentId, $sentiment)
    {
        $sent = new SPODPUBLIC_BOL_PublicRoomCommentSentiment();
        $sent->commentId = $commentId;
        $sent->publicRoomId = $publicRoom;
        $sent->sentiment = $sentiment;

        SPODPUBLIC_BOL_PublicRoomCommentSentimentDao::getInstance()->save($sent);
    }

    public function removeRoom($roomId)
    {
        $this->deleteRoomComments($roomId);
        SPODPUBLIC_BOL_PublicRoomDao::getInstance()->deleteById($roomId);
    }

    public function deleteRoomComments($id, $level=0)
    {
        $comments = BOL_CommentService::getInstance()->findFullCommentList(($level == 0 ) ? SPODPUBLIC_BOL_Service::ENTITY_TYPE : SPODPUBLIC_BOL_Service::ENTITY_TYPE_COMMENT, $id);

        for ($i = 0; $i < count($comments); $i++)
            $this->deleteRoomComments($comments[$i]->id, $level + 1);

        BOL_CommentService::getInstance()->deleteComment($id);
    }

    public function getOrderedComments($id, $comment_number, $dataletOnly = false)
    {
        $comments = array();
        $this->getFlatComment($id, 0, $comments, $dataletOnly);
        usort($comments, array($this, "commentComparator"));
        $comments = array_slice($comments,count($comments)-$comment_number);
        return $comments;
    }

    public function commentComparator($c1, $c2)
    {
        if ($c1->createStamp == $c2->createStamp) return 0;
        return ($c1->createStamp < $c2->createStamp) ? -1 : 1;
    }

    public function getFlatComment($id, $level=0, &$flat_comment=array(), $dataletOnly = false)
    {
        $comments = BOL_CommentService::getInstance()->findFullCommentList(($level == 0 ) ? SPODPUBLIC_BOL_Service::ENTITY_TYPE : SPODPUBLIC_BOL_Service::ENTITY_TYPE_COMMENT, $id);

        for ($i = 0; $i < count($comments); $i++)
        {
            if ($dataletOnly && ODE_BOL_Service::getInstance()->getDataletByPostId($comments[$i]->id, 'public-room') != null)
                $flat_comment = array_merge($flat_comment, array($comments[$i]));
            else if(ODE_BOL_Service::getInstance()->getDataletByPostId($comments[$i]->id, 'public-room') == null)
                $flat_comment = array_merge($flat_comment, array($comments[$i]));

            $this->getFlatComment($comments[$i]->id, $level + 1, $flat_comment, $dataletOnly);
        }
    }

    public function editRoom($roomId, $title)
    {
        $pr = $this->getPublicRoomById($roomId);
        $pr->subject = $title;
        SPODPUBLIC_BOL_PublicRoomDao::getInstance()->save($pr);
    }


}