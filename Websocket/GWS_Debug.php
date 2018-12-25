<?php
use GDO\Websocket\Server\GWS_Command;
use GDO\Websocket\Server\GWS_Commands;
use GDO\Websocket\Server\GWS_Message;
use GDO\Websocket\Server\GWS_Global;

final class GWS_Debug extends GWS_Command
{
	public function execute(GWS_Message $msg)
	{
		print_r(GWS_Global::$USERS);
	}
}

GWS_Commands::register(0x0108, new GWS_Debug());
