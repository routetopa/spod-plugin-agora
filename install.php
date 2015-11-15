<?php

OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'spodpublic.main', 'spodpublic', 'main', OW_Navigation::VISIBLE_FOR_MEMBER);

$sql = 'DROP TABLE IF EXISTS `' . OW_DB_PREFIX . 'spod_public_room`;
CREATE TABLE `' . OW_DB_PREFIX . 'spod_public_room` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ownerId` int(11) NOT NULL,
  `subject` text,
  `body` text,
  `views` int(11) DEFAULT 0,
  `comments` int(11) DEFAULT 0,
  `opendata` int(11) DEFAULT 0,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` enum("approval","approved","blocked") NOT NULL DEFAULT "approved",
  `privacy` varchar(50) NOT NULL DEFAULT "everybody",
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;';

OW::getDbo()->query($sql);

$authorization = OW::getAuthorization();
$groupName = 'spodpublic';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'view', true);
$authorization->addAction($groupName, 'add_comment');
