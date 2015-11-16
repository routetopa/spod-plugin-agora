<?php


class SPODPUBLIC_CTRL_Ajax extends OW_ActionController
{
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
}