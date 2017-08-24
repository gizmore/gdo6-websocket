<?php 
use GDO\Template\GDO_Bar;
use GDO\Template\GDO_Template;
$navbar instanceof GDO_Bar;
$navbar->addField(GDO_Template::make()->template('Websocket', 'ws-connect-bar.php'));
