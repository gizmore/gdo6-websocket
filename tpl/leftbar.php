<?php 
use GDO\UI\GDT_Bar;
use GDO\Core\GDT_Template;
$navbar instanceof GDT_Bar;
$navbar->addField(GDT_Template::make()->template('Websocket', 'ws-connect-bar.php'));
