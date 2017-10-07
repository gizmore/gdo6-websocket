<?php
namespace GDO\Websocket\Server;

use GDO\DB\GDT_Object;
use GDO\Form\GDT_Form;
use GDO\Form\MethodForm;
use GDO\DB\GDT_Decimal;
use GDO\DB\GDT_Int;
use GDO\DB\GDT_String;
use GDO\DB\GDT_Checkbox;
/**
 * Fill a GDT_Form with a GWS_Message.
 * 
 * @author gizmore
 * @since 5.0
 * 
 * @see GDT;
 * @see GDT_Form
 * @see GWS_Message
 */
final class GWS_Form
{
	public static function bindMethod(MethodForm $method, GWS_Message $msg)
	{
		return self::bind($method->getForm(), $msg);
	}
	
	public static function bind(GDT_Form $form, GWS_Message $msg)
	{
		foreach ($form->getFields() as $gdoType)
		{
			if ($gdoType instanceof GDT_String)
			{
				$gdoType->setGDOValue($msg->readString());
			}
			elseif ($gdoType instanceof GDT_Decimal)
			{
				$gdoType->setGDOValue($msg->readFloat());
			}
			elseif ($gdoType instanceof GDT_Checkbox)
			{
			    $gdoType->setGDOValue($msg->read8() > 0);
			}
		    elseif ($gdoType instanceof GDT_Int)
			{
				$gdoType->setGDOValue($msg->readN($gdoType->bytes, $gdoType->signed()));
			}
			elseif ($gdoType instanceof GDT_Object)
			{
			    $gdoType->value($msg->read32u());
			}
		}
		return $form;
	}
}
