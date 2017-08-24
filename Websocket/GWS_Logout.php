<?php
namespace GDO\Websocket\Websocket;

use GDO\User\Session;
use GDO\User\User;
use GDO\Websocket\Server\GWS_Command;
use GDO\Websocket\Server\GWS_Commands;
use GDO\Websocket\Server\GWS_Message;

final class GWS_Logout extends GWS_Command
{
    public function execute(GWS_Message $msg)
    {
        method('Login', 'Logout')->execute();
        Session::reset();
        User::$CURRENT = $user = User::ghost();
        $msg->replyBinary($msg->cmd(), $this->userToBinary($user));
    }
}

GWS_Commands::register(0x0104, new GWS_Logout());
