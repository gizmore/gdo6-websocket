<?php
namespace GDO\Websocket\Server;

use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
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
	
	public function postExecute(GWS_Message $msg, GDT_Form $form, Response $response)
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
	
	public function replySuccess(GWS_Message $msg, GDT_Form $form, Response $response)
	{
		$msg->replyBinary($msg->cmd());
	}
	
	
	/**
	 * @param GDT_Form $form
	 * @return GDT_Submit[]
	 */
	protected function getSubmits(GDT_Form $form)
	{
		$submits = [];
		foreach ($form->getFields() as $field)
		{
			if ($field instanceof GDT_Submit)
			{
				$submits[] = $field;
			}
		}
		return $submits;
	}
	
	protected function removeCaptcha(GDT_Form $form)
	{
	    $form->removeField('captcha');
	}
	
	protected function removeCSRF(GDT_Form $form)
	{
	    $form->removeField('xsrf');
	}
	
	protected function selectSubmit(GDT_Form $form)
	{
		$this->selectSubmitNum($form, 0);
	}
	
	protected function selectSubmitNum(GDT_Form $form, int $num)
	{	
		$submits = $this->getSubmits($form);
		if ($submit = @$submits[$num])
		{
			$name = $submit->name;
			$_REQUEST[$name] = $_POST[$name] = $name;
		}
	}
}
