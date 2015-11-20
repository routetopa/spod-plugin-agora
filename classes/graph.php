<?php

class SPODPUBLIC_CLASS_Graph
{
    private $graph;
    private $normalizedNodeIds;

    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getGraph($id, $type){

            $id = intval($id);
            $pr = SPODPUBLIC_BOL_Service::getInstance()->getPublicRoomById($id);

            $this->graph =  new stdClass;
            $this->graph->nodes = array();
            $this->graph->links = array();
            $this->normalizedNodeIds = array();

            $node = new Node(0, $pr->subject, intval(SPODPUBLIC_BOL_Service::getInstance()->getEntityId($id)["id"]));
            $node->fixed = true;
            $node->color = "#519c76";
            $node->r = 30;
            $node->x = 200;
            $node->y = 200;

            array_push($this->graph->nodes, $node);

            $this->normalizedNodeIds[$node->originalId] = 0;

            $this->analyzeCommentsTree(BOL_CommentService::getInstance()->findFullCommentList(SPODPUBLIC_BOL_Service::ENTITY_TYPE, $id), 0, $type);

            return $this->graph;

    }

    private function analyzeCommentsTree($nodes, $level, $type)
    {

        for ($i = 0; $i < count($nodes); $i++)
        {
            $node = null;
            if ($type == "comment") {
                $node = new Node(count($this->graph->nodes),
                    BOL_UserService::getInstance()->getDisplayName($nodes[$i]->userId),
                    intval($nodes[$i]->id));

            }else {
                if (OW::getPluginManager()->isPluginActive('spodpr')) {
                    $datalet = ODE_BOL_Service::getInstance()->getDataletByPostId($nodes[$i]->id, "public-room");
                    if (count($datalet) > 0) {
                        $node = new Node(count($this->graph->nodes),
                            $datalet["component"],
                            intval($nodes[$i]->id));
                    }
                }
            }

            if($node == null){
                $node = new Node(count($this->graph->nodes),
                    "",
                    intval($nodes[$i]->id));

                $node->color = "#000000";
                $node->r = 1;

            }else {
                $node->content = $nodes[$i]->message;
                switch ($level) {
                    case 0:
                        $node->color = "#ff1e1e";
                        $node->r = 20;
                        break;
                    case 1:
                        $node->color = "#3399cc";
                        $node->r = 15;
                        break;
                    case 2:
                        $node->color = "#a7a1a1";
                        $node->r = 5;
                        break;
                }
            }

            array_push($this->graph->nodes, $node);
            @$this->normalizedNodeIds[SPODPUBLIC_BOL_Service::getInstance()->getEntityId($nodes[$i]->id)["id"]] = $node->id;

            $link = new Link($this->normalizedNodeIds[intval($nodes[$i]->commentEntityId)], intval($node->id));

            switch ($level) {
                case 0:
                    $link->value = 50;
                    break;
                case 1:
                    $link->value = 20;
                    break;
                case 2:
                    $link->value = 5;
                    break;
            }

            array_push($this->graph->links, $link);


            $this->analyzeCommentsTree(BOL_CommentService::getInstance()->findFullCommentList(SPODPUBLIC_BOL_Service::ENTITY_TYPE, $nodes[$i]->id), $level + 1, $type);
        }
    }

    public function getRoot($id){
        $comment = BOL_CommentService::getInstance()->findComment($id);
        if($comment == null) return $id;
        $root = null;
        while($comment)
        {
            $entity = BOL_CommentEntityDao::getInstance()->findById($comment->getCommentEntityId());
            $root = $entity->entityId;
            $comment = BOL_CommentService::getInstance()->findComment($entity->entityId);
        }
        return $root;
    }
}



class Node{
    public $id;
    public $originalId;
    public $name;
    public $content;
    public $color;
    public $r;

    function __construct($id, $name, $originalId){
        $this->id         = $id;
        $this->name       = $name;
        $this->originalId = $originalId;
    }
}

class Link{
    public $source;
    public $target;
    public $value;

    function __construct($source, $target){
        $this->source = $source;
        $this->target = $target;
    }

}