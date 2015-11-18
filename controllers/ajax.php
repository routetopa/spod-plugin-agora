<?php


class SPODPUBLIC_CTRL_Ajax extends OW_ActionController
{
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
            echo json_encode(array("status"        => "ok",
                "id"            => $id,
                "graph"         => json_encode(SPODPUBLIC_CLASS_Graph::getInstance()->getGraph($id, $_REQUEST['type']))));

            exit;

        }

        echo json_encode(array("status" => "error",
            "message" => "Problem in graph creation."));

        exit;

    }
}

