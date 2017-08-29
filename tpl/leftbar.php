<?php 
use GDO\Template\GDT_Bar;
use GDO\Template\GDT_Template;
$navbar instanceof GDT_Bar;
$navbar->addField(GDT_Template::make()->template('Websocket', 'ws-connect-bar.php'));
