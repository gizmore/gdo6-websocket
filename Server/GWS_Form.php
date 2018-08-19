<?php
namespace GDO\Websocket\Server;

use GDO\Core\Method;
use GDO\DB\GDT_Object;
use GDO\Form\GDT_Form;
use GDO\Form\MethodForm;
use GDO\DB\GDT_Decimal;
use GDO\DB\GDT_Int;
use GDO\DB\GDT_String;
use GDO\DB\GDT_Checkbox;
use GDO\Core\GDT;
use GDO\Core\Logger;
use GDO\Core\GDOException;
use GDO\DB\GDT_Float;
/**
 * Fill a GDT_Form with a GWS_Message.
 * Fill a Method with a GWS_Message.
 * 
 * @author gizmore
 * @since 5.0
 * @version 6.07
 * 
 * @see GDT;
 * @see GDT_Form
 * @see GWS_Message
 */
final class GWS_Form
{
	public static function bindMethod(Method $method, GWS_Message $msg)
	{
		return self::bindFields($method->gdoParameters(), $msg);
	}

	public static function bindMethodForm(MethodForm $method, GWS_Message $msg)
	{
		return self::bindForm($method->getForm(), $msg);
	}
	
	public static function bindForm(GDT_Form $form, GWS_Message $msg)
	{
		self::bindFields($form->getFields(), $msg);
		return $form;
	}
	
	/**
	 * @param array $fields
	 * @param GWS_Message $msg
	 */
	public static function bindFields(array $fields, GWS_Message $msg)
	{
		foreach ($fields as $gdoType)
		{
			self::bind($gdoType, $msg);
		}
	}
	
	private static function bind(GDT $gdoType, GWS_Message $msg)
	{
		try
		{
			if ($gdoType->isSerializable())
			{
// 				Logger::logWebsocket(sprintf("Reading %s as a %s.", $gdoType->name, get_class($gdoType)));
				
				if ($gdoType instanceof GDT_Checkbox)
				{
					$gdoType->value($msg->read8() > 0);
				}
				elseif ($gdoType instanceof GDT_String)
				{
					$gdoType->val($msg->readString());
				}
				elseif ( ($gdoType instanceof GDT_Decimal) ||
						 ($gdoType instanceof GDT_Float) )
				{
					$gdoType->value($msg->readFloat());
				}
				elseif ($gdoType instanceof GDT_Object)
				{
					$gdoType->val($msg->read32u());
				}
				elseif ($gdoType instanceof GDT_Int)
				{
					$gdoType->value($msg->readN($gdoType->bytes, !$gdoType->unsigned));
				}
			}
		}
		catch (GDOException $ex)
		{
			Logger::logException($ex);
			throw new GDOException("Cannot read {$gdoType->name} which is a " . get_class($gdoType));
		}
	}
}
