<?php 
use GDO\UI\GDT_Bar;
use GDO\Core\GDT_Template;
$navbar instanceof GDT_Bar;
if (module_enabled('Angular'))
{
	$navbar->addField(GDT_Template::make()->template('Websocket', 'ws-connect-bar.php'));
}
