<?php
namespace GDO\Websocket\Server;

use GDO\Core\GDO;
use GDO\Date\GDT_Timestamp;
use GDO\Date\Time;
use GDO\DB\GDT_Enum;
use GDO\Net\GDT_IP;
use GDO\DB\GDT_Decimal;
use GDO\DB\GDT_Int;
use GDO\User\GDT_Password;
use GDO\DB\GDT_String;
use GDO\User\GDO_User;
use GDO\Maps\GDT_Position;
use GDO\Core\GDT_Secret;
use GDO\Table\GDT_PageMenu;
use GDO\DB\GDT_Float;
/**
 * GWS_Commands have to register via GWS_Commands::register($code, GWS_Command, $binary=true)
 * @author gizmore
 */
abstract class GWS_Command
{
	protected $message;
	
	public function setMessage(GWS_Message $message) { $this->message = $message; return $this; }
	
	/**
	 * @return GDO_User
	 */
	public function user() { return $this->message->user(); }
	public function message() { return $this->message; }
	
	################
	### Abstract ###
	################
	public abstract function execute(GWS_Message $msg);

	############
	### Util ###
	############
	public function userToBinary(GDO_User $user)
	{
		$fields = $user->gdoColumnsExcept('user_password', 'user_register_ip');
		return $this->gdoToBinary($user, array_keys($fields));
	}
	
	public function gdoToBinary(GDO $gdo, array $fields=null)
	{
		$fields = $fields ? $gdo->getGDOColumns($fields) : $gdo->gdoColumnsCache();
		$payload = '';
		foreach ($fields as $field)
		{
			$field->gdo($gdo);
			
			if ( ($field instanceof GDT_Password) ||
				 ($field instanceof GDT_IP) ||
				 ($field instanceof GDT_Secret) )
			{
				# skip
			}
			elseif ($field instanceof GDT_String)
			{
				$payload .= GWS_Message::wrS($gdo->getVar($field->name));
			}
			elseif ( ($field instanceof GDT_Decimal) ||
					 ($field instanceof GDT_Float) )
			{
				$payload .= GWS_Message::wrF($gdo->getVar($field->name));
			}
			elseif ($field instanceof GDT_Int)
			{
				$payload .= GWS_Message::wrN($field->bytes, $gdo->getVar($field->name));
			}
			elseif ($field instanceof GDT_Enum)
			{
				$value = array_search($gdo->getVar($field->name), $field->enumValues);
				$payload .= GWS_Message::wr8($value === false ? 0 : $value + 1);
			}
			elseif ($field instanceof GDT_Timestamp)
			{
				$time = 0;
				if ($date = $gdo->getVar($field->name))
				{
					$time = Time::getTimestamp($date);
				}
				$payload .= GWS_Message::wr32($time);
			}
			elseif ($field instanceof GDT_Position)
			{
				$payload .= GWS_Message::wrF($field->getLat());
				$payload .= GWS_Message::wrF($field->getLng());
			}
			else
			{
				die("Cannot ws encode {$field->name}");
			}
		}
		return $payload;
	}
	
	public function pagemenuToBinary(GDT_PageMenu $gdt)
	{
		return GWS_Message::wr16($gdt->getPage()) . 
		   GWS_Message::wr16($gdt->getPages()) .
		   GWS_Message::wr32($gdt->numItems) .
		   GWS_Message::wr16($gdt->ipp);
	}

}
