<?php
namespace GDO\Websocket\Websocket;

use GDO\Form\GDT_Form;
use GDO\Template\Response;
use GDO\User\GDO_Session;
use GDO\User\GDO_User;
use GDO\Websocket\Server\GWS_CommandForm;
use GDO\Websocket\Server\GWS_Commands;
use GDO\Websocket\Server\GWS_Message;

final class GWS_Login extends GWS_CommandForm
{
	public function getMethod() { return method('Login', 'Form'); }
	
	public function replySuccess(GWS_Message $msg, GDT_Form $form, Response $response)
	{
		GDO_User::$CURRENT = $user = GDO_Session::instance()->getUser();
		GDO_Session::reset();
		$msg->replyBinary($msg->cmd(), $this->userToBinary($user));
	}
}

GWS_Commands::register(0x0103, new GWS_Login());
