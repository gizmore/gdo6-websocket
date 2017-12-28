<?php
namespace GDO\Websocket\Websocket;

use GDO\Form\GDT_Form;
use GDO\Core\GDT_Response;
use GDO\Websocket\Server\GWS_CommandForm;
use GDO\Websocket\Server\GWS_Commands;
use GDO\Websocket\Server\GWS_Message;
use GDO\User\GDO_Session;
use GDO\User\GDO_User;
use GDO\Register\Module_Register;
use GDO\Websocket\Server\GWS_Global;
use GDO\Recovery\Method\Form;

final class GWS_Recovery extends GWS_CommandForm
{
	public function getMethod()
	{
		return Form::make();
	}
	
// 	public function replySuccess(GWS_Message $msg, GDT_Form $form, GDT_Response $response)
// 	{
// 		if (!Module_Register::instance()->cfgEmailActivation())
// 		{
// 			$user = GDO_User::current();
// 			$user->tempSet('sess_id', GDO_Session::instance()->getID());
// 			$msg->conn()->setUser($user);
// 			GWS_Global::addUser($user, $msg->conn());
// 		}
// 		$msg->replyBinary($msg->cmd(), $this->userToBinary(GDO_User::current()));
// 	}
}

GWS_Commands::register(0x0106, new GWS_Recovery());
