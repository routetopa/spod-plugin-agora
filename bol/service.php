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

    //ACOMMENT - abuse comment
    public function getAbuseCommentList()
    {
        return SPODPUBLIC_BOL_AcommentDao::getInstance()->findAll();
    }

    public function addAbuseComment( $cid )
    {
        $acomment            = new SPODPUBLIC_BOL_Acomment;
        $acomment->commentId = $cid;

        return SPODPUBLIC_BOL_AcommentDao::getInstance()->save($acomment);

    }

    public function deleteAbuseComment( $cid )
    {
        $example = new OW_Example();
        $example->andFieldEqual('commentId', $cid);

        return SPODPUBLIC_BOL_AcommentDao::getInstance()->deleteByExample($example);
    }



}