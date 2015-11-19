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

    public function addPrivateRoom($ownerId, $subject, $body)
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

    public function getEntityId($id){

        $dbo = OW::getDbo();

        $query = "SELECT * FROM ow_base_comment_entity WHERE entityId = " . $id . ";";

        return $dbo->queryForRow($query);

    }
}