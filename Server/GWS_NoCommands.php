<?php
namespace GDO\Websocket\Server;

use GDO\User\User;
/**
 * Example of a GWS_Commands implementation.
 * 
 * @author gizmore
 * @since 5.0
 * @see GWS_Command
 */
final class GWS_NoCommands extends GWS_Commands
{
	public function init() {}
	public function timer() {}
	public function connect(User $user) {}
	public function disconnect(User $user) {}
}
