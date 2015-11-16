<?php

class SPODPUBLIC_CTRL_Test extends OW_ActionController
{
    public static $nodes = array();
    public static $links = array();

    public function index()
    {
        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('spodpublic')->getRootDir() . 'master_pages/empty.html');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'commentsList.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'public_room.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'jquery-ui.min.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodpublic')->getStaticJsUrl() . 'perfect-scrollbar.jquery.js');

        //comment and rate
        $commentsParams = new BASE_CommentsParams('spodpublic', SPODPR_BOL_Service::ENTITY_TYPE);
        $commentsParams->setEntityId(10101);
        $commentsParams->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST);
        $commentsParams->setCommentCountOnPage(5);
        $commentsParams->setOwnerId((OW::getUser()->getId()));
        $commentsParams->setAddComment(TRUE);
        $commentsParams->setWrapInBox(false);
        $commentsParams->setShowEmptyList(false);

        $commentsParams->level = 0;
        $commentsParams->nodeId = 0;

        array_push(SPODPUBLIC_CTRL_Test::$nodes, array(10101, 'Sbiricuda Forum', 0));

        $commentCmp = new SPODPUBLIC_CMP_Comments($commentsParams);
        $this->addComponent('comments', $commentCmp);

        /*$topicRates = new BASE_CMP_Rate('photomap', "photomap_rates", 10101, OW::getUser()->getId());
        $this->addComponent('rate', $topicRates);*/

        $normalized_nodes_ids = null;
        $original_nodes_ids   = null;
        for($i=0;$i < count(SPODPUBLIC_CTRL_Test::$nodes); $i++) {
            $normalized_nodes_ids[SPODPUBLIC_CTRL_Test::$nodes[$i][0]] = $i;
            $original_nodes_ids[$i] = SPODPUBLIC_CTRL_Test::$nodes[$i][0];
        }

        $js = UTIL_JsGenerator::composeJsString('
                SPODPUBLICROOM.original_nodes_ids = {$components_url};
            ', array(
            'components_url' => $original_nodes_ids
        ));

        OW::getDocument()->addOnloadScript($js);

        $json_graph = '{"nodes": [{"id": ' . $normalized_nodes_ids[SPODPUBLIC_CTRL_Test::$nodes[0][0]] .',"name": "' . SPODPUBLIC_CTRL_Test::$nodes[0][1] .'","fixed": true,"x": 200,"y": 200,"color": "#519c76", "r" : 30},';

        for($i=1; $i < count(SPODPUBLIC_CTRL_Test::$nodes); $i++){
            $json_graph .= '{"id": ' . $normalized_nodes_ids[SPODPUBLIC_CTRL_Test::$nodes[$i][0]] .',"name": "' . SPODPUBLIC_CTRL_Test::$nodes[$i][1].'"';

            switch(SPODPUBLIC_CTRL_Test::$nodes[$i][2]){
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

            if($i ==  count(SPODPUBLIC_CTRL_Test::$nodes) - 1){
                $json_graph .= '}';
            }else{
                $json_graph .= '},';
            }
        }

        $json_graph .= '],"links": [';

        for($i=0; $i < count(SPODPUBLIC_CTRL_Test::$links); $i++){
            $json_graph .= '{"source": ' . $normalized_nodes_ids[SPODPUBLIC_CTRL_Test::$links[$i][0]] .',"target": ' . $normalized_nodes_ids[SPODPUBLIC_CTRL_Test::$links[$i][1]];

            switch(SPODPUBLIC_CTRL_Test::$links[$i][2]){
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

            if($i ==  count(SPODPUBLIC_CTRL_Test::$links) - 1){
                $json_graph .= '}';
            }else{
                $json_graph .= '},';
            }
        }
        $json_graph .= ']}';

        $this->assign('graphData', json_encode($json_graph));
        $this->assign('components_url', SPODPR_COMPONENTS_URL);

        /*OW::getDocument()->addOnloadScript(
            '$(document).ready(function(){

                  var g = {
        "nodes": [
        {
            "id": 0,
            "name": "Abstract",
        },
        {
            "id": 1,
            "name": "History",
        },
        {
            "id": 2,
            "name": "Info"

        },
        {
            "id": 3,
            "name": "Organisation",
            "color": "#662d91",
            "r": 18
        },
        {
            "id": 4,
            "name": "CFP",
            "color": "#f69a43",
            "r": 19
        },
        {
            "id": 5,
            "name": "Important Dates"
        },
        {
            "id": 6,
            "name": "TOPIC",
            "x": 200,
            "y": 200,
            "fixed": true,
            "color": "#519c76",
            "r": 30
        },
        {
            "id": 7,
            "name": "Programme Committee",
        },
        {
            "id": 8,
            "name": "Submission",
            "color": "#c82528",
            "r": 25
        },
        {
            "id": 9,
            "name": "info2"
        },
        {
            "id": 10,
            "name": "info3"
        },
        {
            "id": 11,
            "name": "content11"
        },
        {
            "id": 12,
            "name": "The Hosting Company"
        },
        {
            "id": 13,
            "name": "Credits"
        }
    ],
    "links": [
        {
            "source": 0,
            "target": 4
        },
        {
            "source": 0,
            "target": 5
        },
        {
            "source": 5,
            "target": 6
        },
        {
            "source": 6,
            "target": 7
        },
        {
            "source": 4,
            "target": 5
        },
        {
            "source": 4,
            "target": 8
        },
        {
            "source": 4,
            "target": 2
        },
        {
            "source": 8,
            "target": 11
        },
        {
            "source": 2,
            "target": 8
        },
        {
            "source": 1,
            "target": 8
        },
        {
            "source": 1,
            "target": 13
        },
        {
            "source": 1,
            "target": 3
        },
        {
            "source": 3,
            "target": 10
        },
        {
            "source": 3,
            "target": 12
        },
        {
            "source": 3,
            "target": 13
        },
        {
            "source": 8,
            "target": 9
        },
        {
            "source": 8,
            "target": 13
        },
        {
            "source": 12,
            "target": 13
        },
        {
            "source": 4,
            "target": 6
        }
    ]
};




                      $("#sbiricuda").height(.2 * $(document).height());
                      buildGraph(g);
            });'


        );*/
    }

}