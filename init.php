<?php

OW::getRouter()->addRoute(new OW_Route('spodpublic.pr', 'public-room/:prId', "SPODPUBLIC_CTRL_PublicRoom", 'index'));
OW::getRouter()->addRoute(new OW_Route('spodpublic.main', 'public-room', "SPODPUBLIC_CTRL_Main", 'index'));

//Comments stuff
OW::getRouter()->addRoute(new OW_Route('spodpublic.getcommentlist', 'public-room/get-comment-list', "SPODPUBLIC_CTRL_Comments", 'getCommentList'));
OW::getRouter()->addRoute(new OW_Route('spodpublic.addcomment', 'public-room/add-comment', "SPODPUBLIC_CTRL_Comments", 'addComment'));
OW::getRouter()->addRoute(new OW_Route('spodpublic.deletecomment', 'public-room/delete-comment', "SPODPUBLIC_CTRL_Comments", 'deleteComment'));
OW::getRouter()->addRoute(new OW_Route('spodpublic.deletecommentattachment', 'public-room/delete-comment-attachment', "SPODPUBLIC_CTRL_Comments", 'deleteCommentAttachment'));
OW::getRouter()->addRoute(new OW_Route('spodpublic.getcommentinfofordelete', 'public-room/get-comment-info-for-delete', "SPODPUBLIC_CTRL_Comments", 'getCommentInfoForDelete'));


OW::getRouter()->addRoute(new OW_Route('public-room-settings', '/public-room/settings', 'SPODPUBLIC_CTRL_Admin', 'settings'));

define("SPODPUBLIC_COMMENTS_THRESHOLD", 1);
define("SPODPUBLIC_COMMENTS_MIN", 1);
define("SPODPUBLIC_COMMENTS_PRCTG", 1);
define("SPODPUBLIC_OPENDATA_THRESHOLD", 5);
define("SPODPUBLIC_OPENDATA_MIN", 5);
define("SPODPUBLIC_OPENDATA_PRCTG", 5);
define("SPODPUBLIC_VIEWS_THRESHOLD", 5);
define("SPODPUBLIC_VIEWS_MIN", 5);
define("SPODPUBLIC_VIEWS_PRCTG", 5);