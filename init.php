<?php

OW::getRouter()->addRoute(new OW_Route('spodpublic.test', 'public-room/test', "SPODPUBLIC_CTRL_Test", 'index'));
OW::getRouter()->addRoute(new OW_Route('spodpublic.main', 'public-room', "SPODPUBLIC_CTRL_Main", 'index'));

//OW::getNavigation()->deleteMenuItem('spodpublic', 'main');