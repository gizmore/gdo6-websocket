<?php
namespace GDO\Websocket\Server;

use GDO\DB\GDO;
use GDO\Date\GDT_Timestamp;
use GDO\Date\Time;
use GDO\Form\GDT_Enum;
use GDO\Type\GDT_Decimal;
use GDO\Type\GDT_Int;
use GDO\Type\GDT_String;
use GDO\User\GDO_User;
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
// 			elseif ( ($field instanceof GDT_Password) ||
// 					 ($field instanceof GDT_IP) )
// 			{
// 				# skip
// 			}
			if ($field instanceof GDT_String)
			{
				$payload .= GWS_Message::wrS($gdo->getVar($field->name));
			}
			elseif ($field instanceof GDT_Decimal)
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
			else
			{
			    die("Cannot ws encode {$field->name}");
			}
		}
		return $payload;
	}

}
