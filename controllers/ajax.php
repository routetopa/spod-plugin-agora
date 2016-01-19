<?php


class SPODPUBLIC_CTRL_Ajax extends OW_ActionController
{
    public function addPublicRoomSuggestion()
    {
        $id = SPODPUBLIC_BOL_Service::getInstance()->addPublicRoomSuggestion(
            OW::getUser()->getId(),
            $_REQUEST['publicRoomId'],
            $_REQUEST['dataset'],
            $_REQUEST['comment']
        );

        echo json_encode(array("status" => "ok",
            "dataset" => $_REQUEST['dataset'],
            "comment" => $_REQUEST['comment'],
            "id" => $id));
        exit;
    }

    public function removePublicRoomSuggestion()
    {
        SPODPUBLIC_BOL_Service::getInstance()->removePublicRoomSuggestion(
            OW::getUser()->getId(),
            $_REQUEST['publicRoomId']
        );

        echo json_encode(array("status" => "ok"));
        exit;
    }

    public function addPublicRoom()
    {
        $id = SPODPUBLIC_BOL_Service::getInstance()->addPublicRoom(OW::getUser()->getId(),
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

