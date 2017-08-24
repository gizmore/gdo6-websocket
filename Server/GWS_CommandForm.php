<?php
namespace GDO\Websocket\Server;

use GDO\Form\GDO_Form;
use GDO\Form\GDO_Submit;
use GDO\Form\MethodForm;
use GDO\Template\Response;
/**
 * Call MethodForm via websockets.
 * @author gizmore
 * @since 5.0
 * @version 5.0
 */
abstract class GWS_CommandForm extends GWS_Command
{
	/**
	 * @return MethodForm
	 */
	public abstract function getMethod();
	
	public function fillRequestVars(GWS_Message $msg) {}
	
	public function execute(GWS_Message $msg)
	{
	    $_POST = []; $_REQUEST = []; $_FILES = [];
	    $method = $this->getMethod();
	    $this->fillRequestVars($msg);
	    $form = GWS_Form::bindMethod($method, $msg);
	    $this->selectSubmit($form);
	    $this->removeCSRF($form);
	    $this->removeCaptcha($form);
	    $response = $method->exec();
	    $this->postExecute($msg, $form, $response);
	}
	
	public function postExecute(GWS_Message $msg, GDO_Form $form, Response $response)
	{
		if ($response->isError())
		{
			$msg->replyErrorMessage($msg->cmd(), json_encode($response->getHTML()));
		}
		else
		{
			$this->replySuccess($msg, $form, $response);
		}
	}
	
	public function replySuccess(GWS_Message $msg, GDO_Form $form, Response $response)
	{
		$msg->replyBinary($msg->cmd());
	}
	
	
	/**
	 * @param GDO_Form $form
	 * @return GDO_Submit[]
	 */
	protected function getSubmits(GDO_Form $form)
	{
		$submits = [];
		foreach ($form->getFields() as $field)
		{
			if ($field instanceof GDO_Submit)
			{
				$submits[] = $field;
			}
		}
		return $submits;
	}
	
	protected function removeCaptcha(GDO_Form $form)
	{
	    $form->removeField('captcha');
	}
	
	protected function removeCSRF(GDO_Form $form)
	{
	    $form->removeField('xsrf');
	}
	
	protected function selectSubmit(GDO_Form $form)
	{
		$this->selectSubmitNum($form, 0);
	}
	
	protected function selectSubmitNum(GDO_Form $form, int $num)
	{	
		$submits = $this->getSubmits($form);
		if ($submit = @$submits[$num])
		{
			$name = $submit->name;
			$_REQUEST[$name] = $_POST[$name] = $name;
		}
	}
}
