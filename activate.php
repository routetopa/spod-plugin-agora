<?php

    $authorization = OW::getAuthorization();
    $groupName = 'spodpublic';
    $authorization->addAction($groupName, 'create_room');

    BOL_LanguageService::getInstance()->importPrefixFromZip(OW::getPluginManager()->getPlugin('spodpublic')->getRootDir() . 'langs.zip', 'spodpublic');

