<?php

OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'spodpublic.main', 'spodpublic', 'main', OW_Navigation::VISIBLE_FOR_MEMBER);

$authorization = OW::getAuthorization();
$groupName = 'spodpublic';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'view', true);
$authorization->addAction($groupName, 'add_comment');
