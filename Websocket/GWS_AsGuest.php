<?php
namespace GDO\Websocket\Websocket;

use GDO\Form\GDT_Form;
use GDO\Core\GDT_Response;
use GDO\User\GDO_Session;
use GDO\User\GDO_User;
use GDO\Websocket\Server\GWS_CommandForm;
use GDO\Websocket\Server\GWS_Commands;
use GDO\Websocket\Server\GWS_Global;
use GDO\Websocket\Server\GWS_Message;
use GDO\Register\Method\Guest;

final class GWS_AsGuest extends GWS_CommandForm
{
	public function getMethod() { return Guest::make(); }

	public function replySuccess(GWS_Message $msg, GDT_Form $form, GDT_Response $response)
	{
		GDO_User::$CURRENT = $user = GDO_Session::instance()->getUser();
		GWS_Global::addUser(GDO_User::current(), $msg->conn());
		$user->tempSet('sess_id', GDO_Session::instance()->getID());
		$msg->conn()->setUser($user);
		GWS_Global::addUser($user, $msg->conn());
		$msg->replyBinary($msg->cmd(), $this->userToBinary($user));
	}
}

GWS_Commands::register(0x0101, new GWS_AsGuest());
