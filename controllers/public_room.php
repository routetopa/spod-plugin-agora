<?php

class SPODPUBLIC_CTRL_PublicRoom extends OW_ActionController
{
    public static $commentNodes = array();
    public static $commentLinks = array();

    public static $dataletNodes = array();
    public static $dataletLinks = array();

    private $public_room = null;

    public function index(array $params)
    {

        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('spodpublic')->getRootDir() . 'master_pages/empty.html');

        if ( isset($params['prId']) )
        {
            OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodpublic')->getStaticCssUrl() . 'perfect-scrollbar.min.css');

            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'commentsList.js');
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'public_room.js');
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'jquery-ui.min.js');
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'perfect-scrollbar.jquery.js');

            $public_room_id = $params['prId'];
            $this->public_room = SPODPUBLIC_BOL_Service::getInstance()->getPublicRoomById($public_room_id);
            $this->assign('public_room', $this->public_room);

            /* ODE */
            if(OW::getPluginManager()->isPluginActive('spodpr'))
                $this->addComponent('private_room', new SPODPR_CMP_PrivateRoomCard('ow_attachment_btn'));
            /* ODE */

            SPODPUBLIC_BOL_Service::getInstance()->addStat($this->public_room->id, 'views');

            //comment and rate
            $commentsParams = new BASE_CommentsParams('spodpublic', SPODPR_BOL_Service::ENTITY_TYPE);
            $commentsParams->setEntityId($public_room_id);
            $commentsParams->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST);
            $commentsParams->setCommentCountOnPage(5);
            $commentsParams->setOwnerId((OW::getUser()->getId()));
            $commentsParams->setAddComment(TRUE);
            $commentsParams->setWrapInBox(false);
            $commentsParams->setShowEmptyList(false);

            $commentsParams->level = 0;
            $commentsParams->nodeId = 0;

            $commentCmp = new SPODPUBLIC_CMP_Comments($commentsParams);
            $this->addComponent('comments', $commentCmp);

            /*$topicRates = new BASE_CMP_Rate('photomap', "photomap_rates", 10101, OW::getUser()->getId());
            $this->addComponent('rate', $topicRates);*/

            /*$normalized_nodes_ids = null;
            $original_nodes_ids = null;
            for ($i = 0; $i < count(SPODPUBLIC_CTRL_PublicRoom::$commentNodes); $i++) {
                $normalized_nodes_ids[SPODPUBLIC_CTRL_PublicRoom::$commentNodes[$i][0]] = $i;
                $original_nodes_ids[$i] = SPODPUBLIC_CTRL_PublicRoom::$commentNodes[$i][0];
            }

            $js = UTIL_JsGenerator::composeJsString('
                    SPODPUBLICROOM.original_nodes_ids = {$components_url};
                ', array(
                'components_url' => $original_nodes_ids
            ));

            OW::getDocument()->addOnloadScript($js);

            $json_graph = '{"nodes": [{"id": ' . $normalized_nodes_ids[SPODPUBLIC_CTRL_PublicRoom::$commentNodes[0][0]] . ',"name": "' . SPODPUBLIC_CTRL_PublicRoom::$commentNodes[0][1] . '","fixed": true,"x": 200,"y": 200,"color": "#519c76", "r" : 30},';

            for ($i = 1; $i < count(SPODPUBLIC_CTRL_PublicRoom::$commentNodes); $i++) {
                $json_graph .= '{"id": ' . $normalized_nodes_ids[SPODPUBLIC_CTRL_PublicRoom::$commentNodes[$i][0]] . ',"name": "' . str_replace("'", '', SPODPUBLIC_CTRL_PublicRoom::$commentNodes[$i][1]) . '"';

                $json_graph .= ',"content": "' . str_replace("'", '', SPODPUBLIC_CTRL_PublicRoom::$commentNodes[$i][2]) . '"';

                switch (SPODPUBLIC_CTRL_PublicRoom::$commentNodes[$i][3]) {
                    case 0:
                        $json_graph .= ',"color": "#ff1e1e", "r" : 20';
                        break;
                    case 1:
                        $json_graph .= ',"color": "#3399cc", "r" : 15';
                        break;
                    case 2:
                        $json_graph .= ',"color": "#a7a1a1", "r" : 5';
                        break;
                }

                if ($i == count(SPODPUBLIC_CTRL_PublicRoom::$commentNodes) - 1) {
                    $json_graph .= '}';
                } else {
                    $json_graph .= '},';
                }
            }

            $json_graph .= '],"links": [';

            for ($i = 0; $i < count(SPODPUBLIC_CTRL_PublicRoom::$commentLinks); $i++) {
                $json_graph .= '{"source": ' . $normalized_nodes_ids[SPODPUBLIC_CTRL_PublicRoom::$commentLinks[$i][0]] . ',"target": ' . $normalized_nodes_ids[SPODPUBLIC_CTRL_PublicRoom::$commentLinks[$i][1]];

                switch (SPODPUBLIC_CTRL_PublicRoom::$commentLinks[$i][2]) {
                    case 0:
                        $json_graph .= ',"value": "50"';
                        break;
                    case 1:
                        $json_graph .= ',"value": "20"';
                        break;
                    case 2:
                        $json_graph .= ',"value": "5"';
                        break;
                }

                if ($i == count(SPODPUBLIC_CTRL_PublicRoom::$commentLinks) - 1) {
                    $json_graph .= '}';
                } else {
                    $json_graph .= '},';
                }
            }
            $json_graph .= ']}';

            $this->assign('commentGraphData', json_encode($json_graph));*/

            //Create comment graph
            /*array_unshift(SPODPUBLIC_CTRL_PublicRoom::$commentNodes, array($this->public_room->id, $this->public_room->subject, 0));
            $commentGraphData = $this->createGraph(SPODPUBLIC_CTRL_PublicRoom::$commentNodes, SPODPUBLIC_CTRL_PublicRoom::$commentLinks);

            $this->assign('commentGraphData', $commentGraphData[1]);

            $js = UTIL_JsGenerator::composeJsString('
                    SPODPUBLICROOM.comment_original_nodes_ids = {$comment_original_nodes_ids};
                ', array(
                'comment_original_nodes_ids' => $commentGraphData[0]
            ));

            //Create datalet graph
            array_unshift(SPODPUBLIC_CTRL_PublicRoom::$dataletNodes, array($this->public_room->id, $this->public_room->subject, 0));
            $dataletGraphData = $this->createGraph(SPODPUBLIC_CTRL_PublicRoom::$dataletNodes, SPODPUBLIC_CTRL_PublicRoom::$dataletLinks);

            $this->assign('dataletGraphData', $dataletGraphData[1]);

            $js = UTIL_JsGenerator::composeJsString('
                    SPODPUBLICROOM.datalet_original_nodes_ids = {$datalet_original_nodes_ids};
                    SPODPUBLICROOM.get_graph_url              = {$get_graph_url};
                    SPODPUBLICROOM.public_room_id             = {$public_room_id};
                ', array(
                'datalet_original_nodes_ids' => $dataletGraphData[0],
                'get_graph_url'              => OW::getRouter()->urlFor('SPODPUBLIC_CTRL_Ajax', 'getGraph'),
                'public_room_id'             => $this->public_room->id
            ));

            OW::getDocument()->addOnloadScript($js);*/

            $this->assign('dataletGraphData', $dataletGraphData[1]);

            $js = UTIL_JsGenerator::composeJsString('
                    SPODPUBLICROOM.get_graph_url              = {$get_graph_url};
                    SPODPUBLICROOM.public_room_id             = {$public_room_id};
                ', array(
                'get_graph_url'              => OW::getRouter()->urlFor('SPODPUBLIC_CTRL_Ajax', 'getGraph'),
                'public_room_id'             => $this->public_room->id
            ));
            OW::getDocument()->addOnloadScript($js);

            //add deep component url
            $this->assign('components_url', SPODPR_COMPONENTS_URL);
        }
    }

    private function createGraph($nodes, $links){

        $normalized_nodes_ids = null;
        $original_nodes_ids = null;
        for ($i = 0; $i < count($nodes); $i++) {
            $normalized_nodes_ids[$nodes[$i][0]] = $i;
            $original_nodes_ids[$i] = $nodes[$i][0];
        }

        $json_graph = '{"nodes": [{"id": ' . $normalized_nodes_ids[$nodes[0][0]] . ',"name": "' . $nodes[0][1] . '","fixed": true,"x": 200,"y": 200,"color": "#519c76", "r" : 30},';

        for ($i = 1; $i < count($nodes); $i++) {
            $json_graph .= '{"id": ' . $normalized_nodes_ids[$nodes[$i][0]] . ',"name": "' . str_replace("'", '', $nodes[$i][1]) . '"';

            $json_graph .= ',"content": "' . str_replace("'", '', $nodes[$i][2]) . '"';

            switch ($nodes[$i][3]) {
                case 0:
                    $json_graph .= ',"color": "#ff1e1e", "r" : 20';
                    break;
                case 1:
                    $json_graph .= ',"color": "#3399cc", "r" : 15';
                    break;
                case 2:
                    $json_graph .= ',"color": "#a7a1a1", "r" : 5';
                    break;
            }

            if ($i == count($nodes) - 1) {
                $json_graph .= '}';
            } else {
                $json_graph .= '},';
            }
        }

        $json_graph .= '],"links": [';

        for ($i = 0; $i < count($links); $i++) {
            $json_graph .= '{"source": ' . $normalized_nodes_ids[$links[$i][0]] . ',"target": ' . $normalized_nodes_ids[$links[$i][1]];

            switch ($links[$i][2]) {
                case 0:
                    $json_graph .= ',"value": "50"';
                    break;
                case 1:
                    $json_graph .= ',"value": "20"';
                    break;
                case 2:
                    $json_graph .= ',"value": "5"';
                    break;
            }

            if ($i == count($links) - 1) {
                $json_graph .= '}';
            } else {
                $json_graph .= '},';
            }
        }
        $json_graph .= ']}';

        return array($original_nodes_ids, json_encode($json_graph) );

    }

}