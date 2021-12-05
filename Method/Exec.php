<?php
namespace GDO\Websocket\Method;

use GDO\UI\MethodPage;
use GDO\Websocket\Module_Websocket;

/**
 * @TODO: Implement websocket.exec client to run commands against the websocket server.
 * 
 * @author gizmore
 * @version 6.11.1
 * @since 6.11.0
 */
final class Exec extends MethodPage
{
	public function getPermission() { return Module_Websocket::instance()->cfgClientPermission(); }

}
