<?php
namespace GDO\Websocket\Websocket;

use GDO\User\GDO_Session;
use GDO\User\GDO_User;
use GDO\Websocket\Server\GWS_Command;
use GDO\Websocket\Server\GWS_Commands;
use GDO\Websocket\Server\GWS_Global;
use GDO\Websocket\Server\GWS_Message;
use GDO\Core\Module_Core;
use GDO\Login\Method\Logout;
use GDO\Websocket\Server\GWS_Server;

final class GWS_Logout extends GWS_Command
{
	public function execute(GWS_Message $msg)
	{
		$sessid = GDO_Session::instance()->getID();
		
		GWS_Server::instance()->onLogout($msg->user());
		GWS_Global::removeUser($msg->user());
		Logout::make()->execute();
		
		$user = GDO_User::$CURRENT;
		$msg->conn()->setUser($user);

		$user->tempSet('sess_id', $sessid);
// 		$user->recache();
		
		$msg->replyText($msg->cmd(), json_encode(Module_Core::instance()->gdoUserJSON()));
	}
}

GWS_Commands::register(0x0104, new GWS_Logout());
