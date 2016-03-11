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

