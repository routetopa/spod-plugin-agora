<?php

class SPODPUBLIC_BOL_Service
{
    const ENTITY_TYPE = 'spodpublic_topic_entity';

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
            array_push($a, array("name" => $suggestion->comment,
                "url" => $suggestion->dataset,
                "description" => ""));
        }

        return json_encode($a);
    }

    public function getEntityId($id)
    {
        $dbo = OW::getDbo();
        $query = "SELECT * FROM ow_base_comment_entity WHERE entityId = " . $id . ";";
        return $dbo->queryForRow($query);
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
        $pr->subject   = $subject;
        $pr->body      = $body;
        $pr->views     = 0;
        $pr->comments  = 0;
        $pr->opendata  = 0;
        $pr->status    = 'approved';
        $pr->privacy   = 'everybody';
        SPODPUBLIC_BOL_PublicRoomDao::getInstance()->save($pr);
        return $pr->id;
    }

    public function addStat($id, $stat)
    {
        $pr = $this->getPublicRoomById($id);
        $pr->$stat += 1;
        SPODPUBLIC_BOL_PublicRoomDao::getInstance()->save($pr);
    }
}