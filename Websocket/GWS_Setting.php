<?php
namespace GDO\Websocket\Websocket;
use GDO\Websocket\Server\GWS_Message;
use GDO\Websocket\Server\GWS_Command;
use GDO\User\GDO_UserSetting;
use GDO\Websocket\Server\GWS_Commands;

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
        if ($value === $setting->initial)
        {
            return $msg->replyErrorMessage($msg->cmd(), t('err_setting_unchanged'));
        }
        if (!$setting->validate($setting->toValue($value)))
        {
            return $msg->replyErrorMessage($msg->cmd(), t('err_setting_validate', [$setting->error]));
        }
        
        GDO_UserSetting::set($key, $value);
        return $msg->replyBinary($msg->cmd());
    }
}

GWS_Commands::register(0x0107, new GWS_Setting());
