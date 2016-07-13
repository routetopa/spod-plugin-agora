<?php

    $authorization = OW::getAuthorization();
    $groupName = 'spodpublic';
    $authorization->addAction($groupName, 'create_room');
