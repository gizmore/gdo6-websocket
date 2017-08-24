<?php
namespace GDO\Websocket\Server;

use GDO\DB\GDO_Object;
use GDO\Form\GDO_Form;
use GDO\Form\MethodForm;
use GDO\Type\GDO_Decimal;
use GDO\Type\GDO_Int;
use GDO\Type\GDO_String;
use GDO\Type\GDO_Checkbox;
/**
 * Fill a GDO_Form with a GWS_Message.
 * 
 * @author gizmore
 * @since 5.0
 * 
 * @see GDO_Base;
 * @see GDO_Form
 * @see GWS_Message
 */
final class GWS_Form
{
	public static function bindMethod(MethodForm $method, GWS_Message $msg)
	{
		return self::bind($method->getForm(), $msg);
	}
	
	public static function bind(GDO_Form $form, GWS_Message $msg)
	{
		foreach ($form->getFields() as $gdoType)
		{
			if ($gdoType instanceof GDO_String)
			{
				$gdoType->setGDOValue($msg->readString());
			}
			elseif ($gdoType instanceof GDO_Decimal)
			{
				$gdoType->setGDOValue($msg->readFloat());
			}
			elseif ($gdoType instanceof GDO_Checkbox)
			{
			    $gdoType->setGDOValue($msg->read8() > 0);
			}
		    elseif ($gdoType instanceof GDO_Int)
			{
				$gdoType->setGDOValue($msg->readN($gdoType->bytes, $gdoType->signed()));
			}
			elseif ($gdoType instanceof GDO_Object)
			{
			    $gdoType->value($msg->read32u());
			}
		}
		return $form;
	}
}
