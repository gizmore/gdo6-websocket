<?php
use GDO\Core\Logger;
use GDO\Core\Debug;
use GDO\DB\Database;
use GDO\Websocket\Module_Websocket;
use GDO\Websocket\Server\GWS_Server;

include 'GDO/Websocket/gwf4-ratchet/autoload.php';

# Load config
include 'protected/config.php'; # <-- You might need to adjust this path.

# Init GDO and GWF core
include 'GDO6.php';

Logger::init(null, 0x20ff);
Debug::init();
#Debug::enableErrorHandler();
#Debug::enableExceptionHandler();
#Debug::setDieOnError(GWF_ERROR_DIE);
Debug::setMailOnError(GWF_ERROR_MAIL);
Database::init();

# Init some config like
$_SERVER['REQUEST_URI'] = 'ws.php';
$_GET['ajax'] = '1';
$_GET['fmt'] = 'json';
$_GET['mo'] = 'Websocket';
$_GET['me'] = 'Run';

# Load
$gwf5->loadModulesCache();

# Create WS
$gws = Module_Websocket::instance();

include $gws->cfgWebsocketProcessorPath();

$processor = $gws->processorClass();

$server = new GWS_Server();
$server->initGWSServer(new $processor(), $gws);
$server->mainloop($gws->cfgTimer());
