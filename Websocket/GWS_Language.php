<?php
namespace GDO\Websocket\Websocket;

use GDO\Websocket\Server\GWS_Command;
use GDO\Websocket\Server\GWS_Commands;
use GDO\Websocket\Server\GWS_Message;
use GDO\Language\GDO_Language;

/**
 * Change language for current user in temp vars.
 * @author gizmore
 */
final class GWS_Language extends GWS_Command
{
	public function execute(GWS_Message $msg)
	{
		$lang = GDO_Language::findById($msg->readString());
		$msg->user()->tempSet('lang_iso', $lang->getISO());
		return $msg->replyBinary($msg->cmd());
	}
}

GWS_Commands::register(0x0109, new GWS_Language());
