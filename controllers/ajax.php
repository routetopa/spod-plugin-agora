<?php


class SPODPUBLIC_CTRL_Ajax extends OW_ActionController
{
    private $graphNodes = array();
    private $graphLinks = array();

    private $graph;
    private $normalizedNodeIds;


    public function addPublicRoom()
    {
        $id = SPODPUBLIC_BOL_Service::getInstance()->addPrivateRoom(OW::getUser()->getId(),
            $_REQUEST['subject'],
            $_REQUEST['body']);

        echo json_encode(array("status" => "ok",
                               "id" => $id,
                               "subject" => $_REQUEST['subject'],
                               "body" => $_REQUEST['body']));
        exit;
    }

    public function getGraph(){

        if(isset($_REQUEST['id']) && isset($_REQUEST['type'])){
            $id = intval($_REQUEST['id']);
            $pr = SPODPUBLIC_BOL_Service::getInstance()->getPublicRoomById($id);

            $this->graph =  new stdClass;
            $this->graph->nodes = array();
            $this->graph->links = array();
            $this->normalizedNodeIds = array();

            $node = new Node(0, $pr->subject, intval($pr->id));
            $node->fixed = true;
            $node->color = "#519c76";
            $node->r = 30;
            $node->x = 200;
            $node->y = 200;

            array_push($this->graph->nodes, $node);

            $this->normalizedNodeIds[intval($pr->id)] = 0;

            $this->analyzeCommentsTree(BOL_CommentService::getInstance()->findFullCommentList(SPODPR_BOL_Service::ENTITY_TYPE, $id), 0, $_REQUEST['type']);

            echo json_encode(array("status"        => "ok",
                "id"            => $id,
                "graph"         => json_encode($this->graph)));

            exit;

        }

        echo json_encode(array("status" => "error",
            "message" => "Problem in graph creation."));

        exit;

    }

    private function findNodeIdByOriginalId($id){
        for($i = 0; $i < count($this->graph->nodes); $i++)
        {
            if(intval($this->graph->nodes[$i]->originalId) == intval($id)) return $this->graph->nodes[$i]->id;
        }
        return null;
    }

    private function analyzeCommentsTree($nodes, $level, $type)
    {

        for ($i = 0; $i < count($nodes); $i++)
        {
            $node = null;
            if ($type == "comment") {
                $node = new Node(count($this->graph->nodes) + 1,
                                 BOL_UserService::getInstance()->getDisplayName($nodes[$i]->userId),
                                 intval($nodes[$i]->id));

            }else {
                if (OW::getPluginManager()->isPluginActive('spodpr')) {
                    $datalet = ODE_BOL_Service::getInstance()->getDataletByPostId($nodes[$i]->id, "public-room");
                    if (count($datalet) > 0) {
                        $node = new Node(count($this->graph->nodes) + 1,
                                               $datalet["component"],
                                               intval($nodes[$i]->id));
                    }
                }
            }

            $node->content = $nodes[$i]->message;
            switch ($level) {
                case 0:
                    $node->color = "#ff1e1e";
                    $node->r     = 20;
                    break;
                case 1:
                    $node->color = "#3399cc";
                    $node->r     = 15;
                    break;
                case 2:
                    $node->color = "#a7a1a1";
                    $node->r     = 5;
                    break;
            }

            array_push($this->graph->nodes, $node);
            $this->normalizedNodeIds[$nodes[$i]->id] = $node->id;

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


            $this->analyzeCommentsTree(BOL_CommentService::getInstance()->findFullCommentList(SPODPR_BOL_Service::ENTITY_TYPE, $nodes[$i]->id), $level + 1, $type);
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