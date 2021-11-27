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
use GDO\DB\GDT_Enum;
use GDO\Date\GDT_Timestamp;

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
		foreach ($fields as $gdt)
		{
			self::bind($gdt, $msg);
		}
	}
	
	private static function bind(GDT $gdt, GWS_Message $msg)
	{
		try
		{
			if ($gdt->isSerializable())
			{
				Logger::logWebsocket(sprintf("Reading %s as a %s.", $gdt->name, get_class($gdt)));
				
				if ($gdt instanceof GDT_Checkbox)
				{
					$gdt->value($msg->read8() > 0);
				}
				elseif ($gdt instanceof GDT_String)
				{
					$gdt->var($msg->readString());
				}
				elseif ( ($gdt instanceof GDT_Decimal) ||
						 ($gdt instanceof GDT_Float) )
				{
					$gdt->value($msg->readFloat());
				}
				elseif ($gdt instanceof GDT_Object)
				{
					$gdt->var($msg->read32u());
				}
				elseif ($gdt instanceof GDT_Int)
				{
					$gdt->value($msg->readN($gdt->bytes, !$gdt->unsigned));
				}
				elseif ($gdt instanceof GDT_Enum)
				{
					$gdt->var($gdt->enumForId($msg->read16u()));
				}
				elseif ($gdt instanceof GDT_Timestamp)
				{
					$ts = $msg->read32u();
					if ($ts)
					{
						$gdt->value($ts);
					}
					else
					{
						$gdt->var(null);
					}
				}
				Logger::logWebsocket(sprintf("Reading %s as a %s with var %s.", $gdt->name, get_class($gdt), $gdt->var));
				
			}
		}
		catch (GDOException $ex)
		{
			Logger::logException($ex);
			throw new GDOException("Cannot read {$gdt->name} which is a " . get_class($gdt));
		}
	}
}
