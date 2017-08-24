<?php
namespace GDO\Websocket\Websocket;

use GDO\Form\GDO_Form;
use GDO\Template\Response;
use GDO\User\Session;
use GDO\User\User;
use GDO\Websocket\Server\GWS_CommandForm;
use GDO\Websocket\Server\GWS_Commands;
use GDO\Websocket\Server\GWS_Message;

final class GWS_AsGuest extends GWS_CommandForm
{
	public function getMethod() { return method('Register', 'Guest'); }

	public function replySuccess(GWS_Message $msg, GDO_Form $form, Response $response)
	{
		User::$CURRENT = $user = Session::instance()->getUser();
		Session::reset();
		$msg->replyBinary($msg->cmd(), $this->userToBinary($user));
	}
}

GWS_Commands::register(0x0101, new GWS_AsGuest());
