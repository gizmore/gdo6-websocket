<?php
namespace GDO\Websocket\Server;

use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Core\GDT_Response;
use GDO\DB\GDT_String;
use GDO\DB\GDT_Int;
use GDO\Core\GDT_JSONResponse;
use GDO\Core\GDT;
use GDO\Core\GDOException;
use GDO\Core\Website;
use GDO\Core\GDT_Success;

/**
 * Call MethodForm via websockets.
 * @author gizmore
 * @version 6.10.1
 * @since 5.0
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
	    parent::execute($msg);
	    
	    $method = $this->getMethod();
		
	    $this->fillRequestVars($msg);
		
		try
		{
			$form = GWS_Form::bindMethodForm($method, $msg);
		}
		catch (GDOException $ex)
		{
			$msg->replyErrorMessage($msg->cmd(), t("err_bind_form", [$ex->getMessage()]));
			return;
		}

		$this->selectSubmit($form);
		$this->removeCSRF($form);
// 		$this->removeCaptcha($form);
		$response = $method->executeWithInit();
		$this->postExecute($msg, $form, $response);
	}
	
	public function postExecute(GWS_Message $msg, GDT_Form $form, GDT_Response $response)
	{
		if ($response->isError())
		{
			echo print_r($response->displayJSON(), 1);
			$msg->replyErrorMessage($msg->cmd(), $response->displayJSON());
		}
		else
		{
			$this->replySuccess($msg, $form, $response);
			$this->afterReplySuccess($msg);
		}
	}
	
	public function afterReplySuccess(GWS_Message $msg)
	{
		
	}
	
	public function replySuccess(GWS_Message $msg, GDT_Form $form, GDT_Response $response)
	{
		$msg->replyBinary($msg->cmd(), $this->payloadFromResponse($response));
	}
	
	private function payloadFromResponse(GDT_Response $response)
	{
		$payload = '';
		foreach ($response->getFields() as $gdt)
		{
			$payload .= $this->payloadFromField($gdt);
		}
		
		if (@Website::$TOP_RESPONSE)
		{
		    $payload .= Website::$TOP_RESPONSE->renderCLI() . chr(0);
		}
		
		return $payload;
	}
	
	private function payloadFromField(GDT $gdt)
	{
		$payload = '';
		if ($gdt instanceof GDT_JSONResponse)
		{
			foreach ($gdt->getFields() as $gdt)
			{
				$payload .= $this->payloadFromField($gdt);
			}
		}
		elseif ($gdt instanceof GDT_String)
		{
			$payload .= GWS_Message::wrS($gdt->getVar());
		}
		elseif ($gdt instanceof GDT_Int)
		{
			$payload .= GWS_Message::wrN($gdt->bytes, $gdt->getValue());
		}
		elseif ($gdt instanceof GDT_Success)
		{
		    $payload .= GWS_Message::wrS($gdt->renderText());
		}
		
// 		if ($fields = $gdt->getFields())
// 		{
// 			foreach ($fields as $gdt2)
// 			{
// 				$payload .= $this->payloadFromField($gdt2);
// 			}
// 		}
		
		return $payload;
	}

	/**
	 * @param GDT_Form $form
	 * @return GDT_Submit[]
	 */
	protected function getSubmits(GDT_Form $form)
	{
		$submits = [];
		foreach ($form->getFieldsRec() as $field)
		{
		    if ($field instanceof GDT_Submit)
		    {
		        $submits[] = $field;
		    }
		}
		foreach ($form->actions()->getFieldsRec() as $field)
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
	
	protected function selectSubmitNum(GDT_Form $form, $num)
	{	
		$submits = $this->getSubmits($form);
		if ($submit = @$submits[$num])
		{
			$name = $submit->name;
			$f = $form->formName();
			$_REQUEST[$f][$name] = $_POST[$f][$name] = $name;
		}
	}

}
