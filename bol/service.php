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
        return SPODPUBLIC_BOL_PublicRoomDao::getInstance()->findAll();
    }

    public function addPrivateRoom($ownerId, $subject, $body)
    {
        $pr = new SPODPUBLIC_BOL_PublicRoom();
        $pr->ownerId   = $ownerId;
        $pr->subject   = $subject;
        $pr->body      = $body;
        $pr->views     = 0;
        $pr->status    = 'approved';
        $pr->privacy   = 'everybody';

        SPODPUBLIC_BOL_PublicRoomDao::getInstance()->save($pr);

        return $pr->id;
    }



}