<?php
use GDO\Core\Application;
use GDO\Core\Logger;
use GDO\Core\Debug;
use GDO\Core\ModuleLoader;
use GDO\DB\Database;
use GDO\Language\Trans;
use GDO\User\GDO_Session;
use GDO\Websocket\Module_Websocket;
use GDO\Websocket\Server\GWS_Server;

# Load config
include 'protected/config.php'; # <-- You might need to adjust this path.
include 'GDO6.php';

include 'GDO/Websocket/gwf4-ratchet/autoload.php';

# Init some config like
$_SERVER['REQUEST_URI'] = 'ws.php';
$_GET['ajax'] = '1';
$_GET['fmt'] = 'json';
$_GET['mo'] = 'Websocket';
$_GET['me'] = 'Run';

# Bootstrap
class WebsocketApplication extends Application
{
    public function isCLI() { return true; }
}
$app = new WebsocketApplication();
Trans::$ISO = GWF_LANGUAGE;
Logger::init(null, GWF_ERROR_LEVEL); # 1st init as guest
Debug::init();
Debug::enableErrorHandler();
Debug::setDieOnError(false);
Debug::setMailOnError(GWF_ERROR_MAIL);
Database::init();
GDO_Session::init(GWF_SESS_NAME, GWF_SESS_DOMAIN, GWF_SESS_TIME, !GWF_SESS_JS, GWF_SESS_HTTPS);
ModuleLoader::instance()->loadModulesCache();
// GDO_Session::instance();

# Create WS
$gws = Module_Websocket::instance();

include $gws->cfgWebsocketProcessorPath();

$processor = $gws->processorClass();

$server = new GWS_Server();
$server->ipcTimer();
$server->initGWSServer(new $processor(), $gws);
$server->mainloop($gws->cfgTimer());
