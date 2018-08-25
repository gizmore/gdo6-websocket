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
	
	public function hookCacheInvalidate($table, $id)
	{
		$table = GDO::tableFor($table);
		if ($object = $table->reload($id))
		{
			if ($object instanceof GDO_User)
			{
				$sessid = $object->tempGet('sess_id');
				$object->tempReset();
				$object->tempSet('sess_id', $sessid);
			}
			else
			{
				$object->tempReset();
			}
		}
	}
	
	public function hookModuleVarsChanged($moduleId)
	{
		ModuleLoader::instance()->initModuleVars();
	}
}

GWS_Commands::register(0x0105, new GWS_Ping());
