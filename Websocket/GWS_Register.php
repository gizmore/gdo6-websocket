<?php
namespace GDO\Websocket\Websocket;

use GDO\Form\GDO_Form;
use GDO\Template\Response;
use GDO\Websocket\Server\GWS_CommandForm;
use GDO\Websocket\Server\GWS_Commands;
use GDO\Websocket\Server\GWS_Message;

final class GWS_Register extends GWS_CommandForm
{
	public function getMethod()
	{
		return method('Register', 'Form');
	}
	
	public function replySuccess(GWS_Message $msg, GDO_Form $form, Response $response)
	{
		$msg->replyBinary($msg->cmd());
	}
}

GWS_Commands::register(0x0102, new GWS_Register());
