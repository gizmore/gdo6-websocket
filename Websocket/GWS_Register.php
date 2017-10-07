<?php
namespace GDO\Websocket\Websocket;

use GDO\Form\GDT_Form;
use GDO\Core\GDT_Response;
use GDO\Websocket\Server\GWS_CommandForm;
use GDO\Websocket\Server\GWS_Commands;
use GDO\Websocket\Server\GWS_Message;

final class GWS_Register extends GWS_CommandForm
{
	public function getMethod()
	{
		return method('Register', 'Form');
	}
	
	public function replySuccess(GWS_Message $msg, GDT_Form $form, GDT_Response $response)
	{
		$msg->replyBinary($msg->cmd());
	}
}

GWS_Commands::register(0x0102, new GWS_Register());
