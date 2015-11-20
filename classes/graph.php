<?php
define('MIN_SIZE', 3);

class SPODPUBLIC_CLASS_Graph
{
    private $graph;

    private $normalizedNodeIds;
    private $datasetsMap;
    private $usersMap;

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

            $node = new Node(0, $pr->subject, intval(SPODPUBLIC_BOL_Service::getInstance()->getEntityId($id)["id"]));
            $node->fixed = true;
            $node->color = "#519c76";
            $node->x = 200;
            $node->y = 200;

            array_push($this->graph->nodes, $node);

            //$this->analyzeCommentsTree(BOL_CommentService::getInstance()->findFullCommentList(SPODPUBLIC_BOL_Service::ENTITY_TYPE, $id), 0, $type);

            switch($type)
            {
                case "comments":
                    $this->normalizedNodeIds = array();
                    $this->normalizedNodeIds[$node->originalId] = 0;

                    $node->r = 20;
                    $this->getCommentsGraph(BOL_CommentService::getInstance()->findFullCommentList(SPODPUBLIC_BOL_Service::ENTITY_TYPE, $id), 0);
                    break;
                case "datalets":
                    $this->datasetsMap = array();

                    $root =  new Node($id, "", 0);
                    $root->type = "datalet";

                    $node->r = MIN_SIZE  * sqrt($this->getDataletsGraph($node, $root, 0));

                    break;
                case "users":
                    $this->graph->nodes = array();
                    $this->usersMap = array();

                    $root =  new Node($id, "", 0);
                    $root->type = "user";

                    $this->getUsersGraph(null, $root, 0);
                    break;
            }

            return $this->graph;
    }

    private function getUsersGraph($father, $curr_node, $level)
    {
        $nodes = BOL_CommentService::getInstance()->findFullCommentList(SPODPUBLIC_BOL_Service::ENTITY_TYPE, $curr_node->id);

        @$user = BOL_UserService::getInstance()->getDisplayName($curr_node->userId);
        $user_img = "http://192.168.164.128/ow_static/themes/rtpa_matter/images/no-avatar-big.png";//BOL_AvatarService::getInstance()->findByUserId($curr_node->userId);


        $node = null;
        if($user != "Deleted user") {
            if (@$this->usersMap[$user] == null) {
                $node = new Node(count($this->graph->nodes),
                    $user,
                    intval($curr_node->id));

                $node->type  = "user";
                $node->r     = MIN_SIZE * 4;
                $node->image = $user_img;
                array_push($this->graph->nodes, $node);

                $this->usersMap[$user] = $node;
            }
            $node = $this->usersMap[$user];
            //$this->usersMap[$user]->r++ ;

            if ($father != null && $this->usersMap[$father->name] != null) {
                $link = new Link($node->id, $this->usersMap[$father->name]->id);
                array_push($this->graph->links, $link);
            }

            $node->content = $curr_node->message;
            $node->color = "#ff1e1e";

        }
        $r = 0;
        for ($i = 0; $i < count($nodes); $i++)
            $r += $this->getUsersGraph($node,
                $nodes[$i],
                $level + 1);

    }

    private function getDataletsGraph($father, $curr_node, $level)
    {
        $nodes = BOL_CommentService::getInstance()->findFullCommentList(SPODPUBLIC_BOL_Service::ENTITY_TYPE, $curr_node->id);
        $curr_father = $father;
        if (OW::getPluginManager()->isPluginActive('spodpr')) {
            $datalet = ODE_BOL_Service::getInstance()->getDataletByPostId($curr_node->id, "public-room");
            if (count($datalet) > 0) {

                $node = new Node(count($this->graph->nodes),
                    $datalet["component"],
                    intval($curr_node->id));

                $node->type = "datalet";

                $url = json_decode($datalet["params"])->{'data-url'};
                @$this->datasetsMap[$url] = ($this->datasetsMap[$url] == null) ? array() : $this->datasetsMap[$url];
                array_push($this->datasetsMap[$url], $node->id);

                for($j=0; $j < count( $this->datasetsMap[$url]); $j++){
                    $link = new Link($node->id,  $this->datasetsMap[$url][$j]);
                    //$link->value = 70;
                    array_push($this->graph->links, $link);
                }

                array_push($this->graph->nodes, $node);
                $link = new Link($father->id, intval($node->id));
                array_push($this->graph->links, $link);

                $curr_father = $node;

                $node->content = $curr_node->message;
                switch ($level) {
                    case 1:
                        $node->color = "#ff1e1e";
                        break;
                    case 2:
                        $node->color = "#3399cc";
                        break;
                    case 3:
                        $node->color = "#a7a1a1";
                        break;
                }

            }else{
                $node = new Node(count($this->graph->nodes),
                    "",
                    intval($curr_node->id));
                $node->type = "comment";
            }
        }else{
            //ODE plugin is not active then the datalets graph cannot be created
            return;
        }

        $r = 0;
        for ($i = 0; $i < count($nodes); $i++)
            $r += $this->getDataletsGraph($curr_father,
                                          $nodes[$i],
                                          $level + 1);

        $node->r = MIN_SIZE *((count($nodes)==0)? 1 : sqrt($r));
        return  (count($nodes)==0)? 1 : $r + 1;
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
    public $image;

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