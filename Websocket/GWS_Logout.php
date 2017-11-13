<?php
namespace GDO\Websocket\Websocket;

use GDO\User\GDO_Session;
use GDO\User\GDO_User;
use GDO\Websocket\Server\GWS_Command;
use GDO\Websocket\Server\GWS_Commands;
use GDO\Websocket\Server\GWS_Message;
use GDO\Login\Method\Logout;

final class GWS_Logout extends GWS_Command
{
    public function execute(GWS_Message $msg)
    {
    	Logout::make()->execute();
//         GDO_Session::reset();
    	$user = GDO_User::$CURRENT;
        $user->tempSet('sess_id', GDO_Session::instance()->getID());
        $msg->conn()->setUser($user);
        $msg->replyBinary($msg->cmd(), $this->userToBinary($user));
    }
}

GWS_Commands::register(0x0104, new GWS_Logout());
