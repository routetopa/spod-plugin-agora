<?php

OW::getRouter()->addRoute(new OW_Route('spodpublic.pr', 'public-room/:prId', "SPODPUBLIC_CTRL_PublicRoom", 'index'));
OW::getRouter()->addRoute(new OW_Route('spodpublic.main', 'public-room', "SPODPUBLIC_CTRL_Main", 'index'));

//OW::getNavigation()->deleteMenuItem('spodpublic', 'main');