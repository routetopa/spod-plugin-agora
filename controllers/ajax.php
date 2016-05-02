<?php


class SPODPUBLIC_CTRL_Ajax extends OW_ActionController
{
    private function validateTextInputVsSqlInjection($input){
        return !preg_match("/(script)|(&lt;)|(&gt;)|(%3c)|(%3e)".
            "|(SELECT)|(UPDATE)|(INSERT)|(DELETE)|(GRANT)|(REVOKE)|(UNION)".
            "|(select)|(update)|(insert)|(delete)|(grant)|(revoke)|(union)|(database)".
            "|(--)|(;)".
            "|(&amp;lt;)|(&amp;gt;)/", $input);
    }

    public function addPublicRoomSuggestion()
    {
        $clean = array();
        $clean['publicRoomId'] = "";
        $clean['dataset'] = "";
        $clean['comment'] = "";
        if($this->validateTextInputVsSqlInjection($_REQUEST['publicRoomId']) &&
            $this->validateTextInputVsSqlInjection($_REQUEST['dataset']) &&
            $this->validateTextInputVsSqlInjection($_REQUEST['comment']))
        {
            $clean['publicRoomId'] = strval(intval($_REQUEST['publicRoomId']));
            $clean['dataset']      = filter_var($_REQUEST['dataset'], FILTER_SANITIZE_STRING);
            $clean['comment']      = filter_var($_REQUEST['comment'], FILTER_SANITIZE_STRING);
        }else{
            echo json_encode(array("status" => "error", "message" => "Insane inputs provided"));
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
        $clean = array();
        $clean['publicRoomId'] = "";
        if($this->validateTextInputVsSqlInjection($_REQUEST['publicRoomId']))
        {
            $clean['roomId'] = strval(intval($_REQUEST['publicRoomId']));
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
        $clean = array();
        $clean['subject'] = "";
        $clean['body'] = "";
        if($this->validateTextInputVsSqlInjection($_REQUEST['dataset']) &&
           $this->validateTextInputVsSqlInjection($_REQUEST['body']))
        {
            $clean['subject'] = filter_var($_REQUEST['subject'], FILTER_SANITIZE_STRING);
            $clean['body']    = filter_var($_REQUEST['body'], FILTER_SANITIZE_STRING);
        }else{
            echo json_encode(array("status" => "error", "message" => "Insane inputs provided"));
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
        $clean = array();
        $clean['id'] = "";
        $clean['type'] = "";
        if($this->validateTextInputVsSqlInjection($_REQUEST['id']) &&
           $this->validateTextInputVsSqlInjection($_REQUEST['type']))
        {
            $clean['id']   = strval(intval($_REQUEST['id']));
            $clean['type'] = filter_var($_REQUEST['type'], FILTER_SANITIZE_STRING);
        }else{
            echo json_encode(array("status" => "error", "message" => "Insane inputs provided"));
            exit;
        }

        echo json_encode(array("status"        => "ok",
            "id"            => $clean['id'],
            "graph"         => SPODPUBLIC_CLASS_Graph::getInstance()->getGraph($clean['id'], $_REQUEST['type'])));

        exit;

        /*echo json_encode(array("status" => "error",
            "message" => "Problem in graph creation."));
        exit;*/
    }

}

