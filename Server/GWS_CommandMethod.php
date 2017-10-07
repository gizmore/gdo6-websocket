<?php
namespace GDO\Websocket\Server;
use GDO\Core\Method;
use GDO\Core\GDT_Response;

/**
 * Call Method via websockets.
 * @author gizmore
 * @since 5.0
 * @version 5.0
 */
abstract class GWS_CommandMethod extends GWS_Command
{
	/**
	 * @return Method
	 */
	public abstract function getMethod();
	
	public abstract function fillRequestVars(GWS_Message $msg);
	
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
			$msg->replyErrorMessage($msg->cmd(), json_encode($response->getHTML()));
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
