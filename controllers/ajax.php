<?php


class SPODPUBLIC_CTRL_Ajax extends OW_ActionController
{
    public function addPublicRoomSuggestion()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            /*echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));*/
            OW::getFeedback()->info(OW::getLanguage()->text('cocreationep', 'insane_user_email_value'));
            exit;
        }

        $id = SPODPUBLIC_BOL_Service::getInstance()->addPublicRoomSuggestion(
            OW::getUser()->getId(),
            $clean['publicRoomId'],
            $clean['dataset'],
            $clean['comment']
        );

        echo json_encode(array("status" => "ok",
            "dataset" => $clean['dataset'],
            "comment" => $clean['comment'],
            "id" => $id));
        exit;
    }

    public function removePublicRoomSuggestion()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            /*echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));*/
            OW::getFeedback()->info(OW::getLanguage()->text('cocreationep', 'insane_user_email_value'));
            exit;
        }

        SPODPUBLIC_BOL_Service::getInstance()->removePublicRoomSuggestion(
            OW::getUser()->getId(),
            $clean['publicRoomId']
        );

        echo json_encode(array("status" => "ok"));
        exit;
    }

    public function addPublicRoom()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            /*echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));*/
            OW::getFeedback()->info(OW::getLanguage()->text('cocreationep', 'insane_user_email_value'));
            exit;
        }

        $id = SPODPUBLIC_BOL_Service::getInstance()->addPublicRoom(OW::getUser()->getId(),
            $clean['subject'],
            $clean['body']);

        echo json_encode(array("status"  => "ok",
                               "id"      => $id,
                               "subject" => $clean['subject'],
                               "body"    => $clean['body']));
        exit;
    }

    public function getGraph()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            /*echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));*/
            OW::getFeedback()->info(OW::getLanguage()->text('cocreationep', 'insane_user_email_value'));
            exit;
        }

        header("Access-Control-Allow-Origin: *");

        echo json_encode(array("status"        => "ok",
            "id"            => $clean['id'],
            "graph"         => SPODPUBLIC_CLASS_Graph::getInstance()->getGraph($clean['id'], $clean['type'])));

        exit;

        /*echo json_encode(array("status" => "error",
            "message" => "Problem in graph creation."));
        exit;*/
    }

}

