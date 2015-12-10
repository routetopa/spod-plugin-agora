<?php
class SPODPUBLIC_BOL_PublicRoomCommentSentimentDao extends OW_BaseDao
{
    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var SPODPUBLIC_BOL_PublicRoomCommentSentimentDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return SPODPUBLIC_BOL_PublicRoomCommentSentimentDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'SPODPUBLIC_BOL_PublicRoomCommentSentiment';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'spod_public_room_comment_sentiment';
    }
}