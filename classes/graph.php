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

            //$this->analyzeCommentsTree(BOL_CommentService::getInstance()->findFullCommentList(SPODPUBLIC_BOL_Service::ENTITY_TYPE, $id), 0, $type);

            switch($type)
            {
                case "comment":
                    $this->getCommentsGraph(BOL_CommentService::getInstance()->findFullCommentList(SPODPUBLIC_BOL_Service::ENTITY_TYPE, $id), 0);
                    break;
                case "datalet":
                    $this->getDataletsGraph(BOL_CommentService::getInstance()->findFullCommentList(SPODPUBLIC_BOL_Service::ENTITY_TYPE, $id), $node, 0);
                    break;
            }

            return $this->graph;

    }

    private function getDataletsGraph($nodes, $father, $level)
    {
        $curr_father = $father;

        for ($i = 0; $i < count($nodes); $i++)
        {
            if (OW::getPluginManager()->isPluginActive('spodpr')) {
                $datalet = ODE_BOL_Service::getInstance()->getDataletByPostId($nodes[$i]->id, "public-room");
                if (count($datalet) > 0) {
                    $node = new Node(count($this->graph->nodes),
                        $datalet["component"],
                        intval($nodes[$i]->id));
                    $node->type = "datalet";
                }else{
                    $node = new Node(count($this->graph->nodes),
                        "",
                        intval($nodes[$i]->id));
                    $node->type = "comment";
                }
            }else{
                //ODE plugin is not active then the datalets graph cannot be created
                return;
            }


            if($node->type == "datalet"){

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
                        $node->r = 10;
                        break;
                }

                array_push($this->graph->nodes, $node);

                $link = new Link($father->id, intval($node->id));

                switch ($level) {
                    case 0:
                        $link->value = 70;
                        break;
                    case 1:
                        $link->value = 50;
                        break;
                    case 2:
                        $link->value = 30;
                        break;
                }

                array_push($this->graph->links, $link);

                $curr_father = $node;
            }

            $this->getDataletsGraph(BOL_CommentService::getInstance()->findFullCommentList(SPODPUBLIC_BOL_Service::ENTITY_TYPE, $nodes[$i]->id), $curr_father, $level + 1);
        }
    }

    private function getCommentsGraph($nodes ,$level)
    {
        for ($i = 0; $i < count($nodes); $i++) {
            $node = new Node(count($this->graph->nodes),
                BOL_UserService::getInstance()->getDisplayName($nodes[$i]->userId),
                intval($nodes[$i]->id));

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

            $this->getCommentsGraph(BOL_CommentService::getInstance()->findFullCommentList(SPODPUBLIC_BOL_Service::ENTITY_TYPE, $nodes[$i]->id), $level + 1);
        }
    }
}



class Node{
    public $id;
    public $originalId;
    public $name;
    public $content;
    public $color;
    public $r;
    public $type;

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