<?php
namespace GDO\Websocket\Websocket;

use GDO\Core\ModuleLoader;
use GDO\Core\GDO;
use GDO\Websocket\Server\GWS_Command;
use GDO\Websocket\Server\GWS_Commands;
use GDO\Websocket\Server\GWS_Message;
use GDO\User\GDO_User;
/**
 * Ping and ws system hooks.
 * 
 * 1. hook cache invalidation
 * 2. hook module vars changed
 * 3. hook user settings changed
 * 
 * @author gizmore
 *
 */
final class GWS_Ping extends GWS_Command
{
	public function execute(GWS_Message $msg)
	{
		$msg->replyBinary(0x0105); # Reply pong
	}
	
	public function hookModuleVarsChanged($moduleId)
	{
		ModuleLoader::instance()->initModuleVars();
	}
	
	public function hookUserSettingChange($userId, $key, $value)
	{
		if (GDO_User::table()->cache->hasID($userId))
		{
			$this->tempReset(GDO_User::findById($userId));
		}
	}
	
	public function hookCacheInvalidate($table, $id)
	{
		$table = GDO::tableFor($table);
		if ($object = $table->reload($id))
		{
			$this->tempReset($object);
		}
	}
	
	private function tempReset(GDO $gdo)
	{
		if ($gdo instanceof GDO_User)
		{
			$sessid = $gdo->tempGet('sess_id');
			$gdo->tempReset();
			$gdo->tempSet('sess_id', $sessid);
		}
		else
		{
			$gdo->tempReset();
		}
	}
	
}

GWS_Commands::register(0x0105, new GWS_Ping());
