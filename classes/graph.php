<?php
define('MIN_SIZE', 4);
define('ROOTNODECOLOR', '#FFBB78');
define('L1NODECOLOR','#2196F3');
define('L2NODECOLOR','#346db7');
define('L3NODECOLOR','#90b2e0');
define('AGREEEDGECOLOR','#60df20');
define('DISAGREEEDGECOLOR','#FF1E1E');
define('NEUTRALEDGECOLOR','#A7B1B7');

class SPODPUBLIC_CLASS_Graph
{
    private $graph;

    private $normalizedNodeIds;
    private $datasetsMap;
    private $usersMap;
    private $linksMap;

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
            $node->content = $pr->body;
            $node->color = ROOTNODECOLOR;
            /*$node->fixed = true;
            $node->x = 200;
            $node->y = 200;*/

            array_push($this->graph->nodes, $node);

            switch($type)
            {
                case "comments":
                    $this->normalizedNodeIds = array();
                    $this->normalizedNodeIds[$node->originalId] = 0;

                    $root =  new Node($id, "", 0);
                    $root->type = "comment";

                    $node->r = MIN_SIZE  * sqrt($this->getCommentsGraph($node, $root, 0));
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
                    $this->linksMap = array();

                    $root =  new Node($id, "", 0);
                    $root->type = "user";

                    $this->getUsersGraph(null, $root, 0);
                    break;
            }

            return $this->graph;
    }

    private function getUsersGraph($father, $curr_comment, $level)
    {
        $comments = BOL_CommentService::getInstance()->findFullCommentList(SPODPUBLIC_BOL_Service::ENTITY_TYPE, $curr_comment->id);

        @$user = BOL_UserService::getInstance()->getDisplayName($curr_comment->userId);
        $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($curr_comment->userId));
        $user_img = $avatar[$curr_comment->userId]['src'];


        $node = null;
        if($user != OW::getLanguage()->text('base', 'deleted_user')) {
            if (@$this->usersMap[$user] == null) {
                $node = new Node(count($this->graph->nodes),
                    $user,
                    intval($curr_comment->id));

                $node->type     = "user";
                $node->userId  = $curr_comment->userId;
                $node->r        = MIN_SIZE * 4;
                $node->image    = $user_img;
                $node->content  = $curr_comment->message;
                $node->color    = "#ff1e1e";
                array_push($this->graph->nodes, $node);

                $this->usersMap[$user] = $node;
            }
            $node = $this->usersMap[$user];
            //$this->usersMap[$user]->r++ ;

            if ($father != null && $this->usersMap[$father->name] != null && $father->userId != $curr_comment->userId && !isset($this->linksMap[$node->id."-".$this->usersMap[$father->name]->id]) ) {
                $link = new Link($node->id, $this->usersMap[$father->name]->id);
                $link->size = 0;
                array_push($this->graph->links, $link);
                $this->linksMap[$node->id."-".$this->usersMap[$father->name]->id] = $link;
            }

            if(@isset($this->linksMap[$node->id."-".$this->usersMap[$father->name]->id])){
                $this->linksMap[$node->id."-".$this->usersMap[$father->name]->id]->size += 2;
            }
        }
        for ($i = 0; $i < count($comments); $i++)
            $this->getUsersGraph($node,
                                 $comments[$i],
                                 $level + 1);

    }

    private function getDataletsGraph($father, $curr_comment, $level)
    {
        $comments = BOL_CommentService::getInstance()->findFullCommentList(SPODPUBLIC_BOL_Service::ENTITY_TYPE, $curr_comment->id);
        $curr_father = $father;
        if (OW::getPluginManager()->isPluginActive('spodpr')) {
            $datalet = ODE_BOL_Service::getInstance()->getDataletByPostId($curr_comment->id, "public-room");
            $sentiment = SPODPUBLIC_BOL_Service::getInstance()->getCommentSentiment($curr_comment->id);
            if (count($datalet) > 0) {

                $params = json_decode($datalet["params"]);


                if(!empty($params->title))
                   $nodeName = $params->title;
                else if (!empty($params->description))
                      $nodeName = $params->description;
                     else
                         $nodeName = substr($curr_comment->message,0,10). "...";


                $node = new Node(count($this->graph->nodes),
                                 $nodeName,
                                 intval($curr_comment->id));

                $node->content = strip_tags($curr_comment->message)."<br><br><b>".parse_url($params->{'data-url'})['host']."</b>";


                if(!empty($sentiment->sentiment)) {
                    $node->sentiment = $sentiment->sentiment;
                }

                $node->type = "datalet";

                $url = $params->{'data-url'};
                @$this->datasetsMap[$url] = ($this->datasetsMap[$url] == null) ? array() : $this->datasetsMap[$url];
                array_push($this->datasetsMap[$url], $node->id);

                for($j=0; $j < count( $this->datasetsMap[$url]); $j++){
                    $link = new Link($node->id,  $this->datasetsMap[$url][$j]);
                    array_push($this->graph->links, $link);
                }

                array_push($this->graph->nodes, $node);
                $link = new Link($father->id, intval($node->id));
                array_push($this->graph->links, $link);

                $curr_father = $node;

            }else{
                $node = new Node(count($this->graph->nodes),
                    "",
                    intval($curr_comment->id));
                $node->type = "comment";
            }
        }else{
            //ODE plugin is not active then the datalets graph cannot be created
            return 0;
        }

        $r = 0;
        for ($i = 0; $i < count($comments); $i++)
            $r += $this->getDataletsGraph($curr_father,
                                          $comments[$i],
                                          $level + 1);

        $node->r = MIN_SIZE *((count($comments)==0)? 1 : sqrt($r));
        return (count($comments)==0)? 1 : $r + 1;
    }

    private function getCommentsGraph($father ,$curr_comment, $level)
    {
        $comments = BOL_CommentService::getInstance()->findFullCommentList(SPODPUBLIC_BOL_Service::ENTITY_TYPE, $curr_comment->id);
        $node = new Node(count($this->graph->nodes),
                         @BOL_UserService::getInstance()->getDisplayName($curr_comment->userId),
                         intval($curr_comment->id));

        $sentiment = SPODPUBLIC_BOL_Service::getInstance()->getCommentSentiment($curr_comment->id);

        @$node->level     = $level;
        @$node->content   = $curr_comment->message;
        @$node->father    = $father;
        switch ($level) {
            case 1:
                $node->color = L1NODECOLOR;
                $node->r = 30;
                break;
            case 2:
                $node->color = L2NODECOLOR;
                $node->r = 20;
                break;
            case 3:
                $node->color = L3NODECOLOR;
                $node->r = 10;
                break;
        }

        @$link = new Link(intval($father->id), intval($node->id));

        switch ($level) {
            case 1:
                $link->value = 20;
                break;
            case 2:
                $link->value = 5;
                break;
            case 3:
                $link->value = 1;
                break;
        }

        if(!empty($sentiment->sentiment)) {
            $node->sentiment = $sentiment->sentiment;
            switch ($sentiment->sentiment) {
                case 1:
                    $link->color = NEUTRALEDGECOLOR;
                    break;
                case 2:
                    $link->color = AGREEEDGECOLOR;
                    break;
                case 3:
                    $link->color = DISAGREEEDGECOLOR;
                    break;
            }
        }

        if($level > 0){
            array_push($this->graph->nodes, $node);
            array_push($this->graph->links, $link);
        }

        $r = 0;
        for ($i = 0; $i < count($comments); $i++)
            $r += $this->getCommentsGraph(($level > 0) ? $node : $father,
                                          $comments[$i],
                                          $level + 1);

        $node->r = MIN_SIZE *((count($comments)==0)? 1 : sqrt($r));
        return  (count($comments)==0)? 1 : $r + 1;
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
    public $sentiment;
    public $level;
    public $father;
    public $userId;

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
    public $color;
    public $size;

    function __construct($source, $target){
        $this->source = $source;
        $this->target = $target;
    }

}