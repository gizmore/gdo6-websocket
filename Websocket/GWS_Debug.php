<?php
use GDO\Websocket\Server\GWS_Command;
use GDO\Websocket\Server\GWS_Commands;
use GDO\Websocket\Server\GWS_Message;
use GDO\Websocket\Server\GWS_Global;
use GDO\Perf\GDT_PerfBar;

final class GWS_Debug extends GWS_Command
{
	public function execute(GWS_Message $msg)
	{
		$data = GDT_PerfBar::data();
		$data['gws_users'] = count(GWS_Global::$USERS);
		$msg->replyText($msg->cmd(), json_encode($data));
	}
}

GWS_Commands::register(0x0108, new GWS_Debug());
