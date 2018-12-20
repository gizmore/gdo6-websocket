<?php
namespace GDO\Websocket\Server;
use GDO\Core\Method;
use GDO\Core\GDT_Response;

/**
 * Call GDO Method via websockets.
 * Either override fillRequestVars or gdoParameters in your derrived @link Method
 * 
 * @author gizmore
 * @since 5.0
 * @version 6.07
 */
abstract class GWS_CommandMethod extends GWS_Command
{
	/**
	 * @return Method
	 */
	public abstract function getMethod();
	
	public function fillRequestVars(GWS_Message $msg)
	{
		GWS_Form::bindMethod($this->getMethod(), $msg);
	}
	
	public function execute(GWS_Message $msg)
	{
		$_GET = []; $_POST = []; $_REQUEST = []; $_FILES = [];
		$_GET['fmt'] = 'json'; $_GET['ajax'] = 1;
		$this->fillRequestVars($msg);
		$method = $this->getMethod();
		$response = $method->exec();
		$this->postExecute($msg, $response);
	}
	
	public function postExecute(GWS_Message $msg, GDT_Response $response)
	{
		if ($response->isError())
		{
			$msg->replyErrorMessage($msg->cmd(), $response->displayJSON());
		}
		else
		{
			$this->replySuccess($msg, $response);
		}
	}
	
	public function replySuccess(GWS_Message $msg, GDT_Response $response)
	{
		$msg->replyBinary($msg->cmd());
	}
	
}
