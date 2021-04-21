<?php
namespace GDO\Websocket\Websocket;

use GDO\Websocket\Server\GWS_Message;
use GDO\Websocket\Server\GWS_Command;
use GDO\Websocket\Server\GWS_Commands;
use GDO\Core\Logger;
use GDO\Core\GDO;
use GDO\Core\ModuleLoader;

/**
 * Change a user setting.
 * @author gizmore
 *
 */
final class GWS_Setting extends GWS_Command
{
	public function execute(GWS_Message $msg)
	{
	    $moduleName = $msg->readString();
	    $module = ModuleLoader::instance()->getModule($moduleName);
		$key = $msg->readString();
		$var = $msg->readString();
		
		if (!($setting = $setting = $module->setting($key)))
		{
			return $msg->replyErrorMessage($msg->cmd(), t('err_unknown_setting', [html($key)]));
		}
		if ($var === $setting->var)
		{
			return $msg->replyErrorMessage($msg->cmd(), t('err_setting_unchanged'));
		}
		
		$value = $setting->toValue($var);
		if (!$setting->validate($value))
		{
			return $msg->replyErrorMessage($msg->cmd(), t('err_setting_validate', [$setting->error]));
		}
		
		Logger::logWebsocket("Writing Setting $key to $value");
		
		# XXX: Ugly fix.
		if ($value instanceof GDO)
		{
			$value = $value->getID();
		}
		
		$module->saveSetting($key, $value);
		return $msg->replyBinary($msg->cmd());
	}
}

GWS_Commands::register(0x0107, new GWS_Setting());
