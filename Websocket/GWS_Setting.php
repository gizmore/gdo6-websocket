<?php
namespace GDO\Websocket\Websocket;
use GDO\Websocket\Server\GWS_Message;
use GDO\Websocket\Server\GWS_Command;
use GDO\User\GDO_UserSetting;
use GDO\Websocket\Server\GWS_Commands;
use GDO\Core\Logger;
use GDO\Avatar\Method\Gallery;
use GDO\Core\GDO;

final class GWS_Setting extends GWS_Command
{
	public function execute(GWS_Message $msg)
	{
		$key = $msg->readString();
		$value = $msg->readString();
		if (!($setting = GDO_UserSetting::get($key)))
		{
			return $msg->replyErrorMessage($msg->cmd(), t('err_unknown_setting'));
		}
		if ($value === $setting->var)
		{
			return $msg->replyErrorMessage($msg->cmd(), t('err_setting_unchanged'));
		}
		
		$value = $setting->toValue($value);
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
		
		GDO_UserSetting::set($key, $value);
		return $msg->replyBinary($msg->cmd());
	}
}

GWS_Commands::register(0x0107, new GWS_Setting());
