<?php
namespace GDO\Websocket\Server;

use GDO\Core\GDOError;
use GDO\User\GDO_User;

include 'GWS_Command.php';
include 'GWS_CommandForm.php';
include 'GWS_CommandMethod.php';

/**
 * Command handler base class.
 * Override this and set in websocket module config
 * @author gizmore
 */
class GWS_Commands
{
	const MID_LENGTH = 7; # Sync Message ID
	const DEFAULT_MID = '0000000'; # Sync Message ID
	
	################
	### Commands ###
	################
	/**
	 * 
	 * @var GWS_Command[]
	 */
	public static $COMMANDS = array();
	public static function register($code, GWS_Command $command, $binary=true)
	{
		if (isset(self::$COMMANDS[$code]))
		{
			throw new GDOError('err_gws_dup_code', [$code, get_class($command)]);
		}
		self::$COMMANDS[$code] = $command;
	}
	
	public static function webHook(array $hookData)
	{
		list($event, $args) = $hookData;
		$method_name = "hook$event";
		foreach (self::$COMMANDS as $command)
		{
			if (method_exists($command, $method_name))
			{
				call_user_func([$command, $method_name], ...$args);
			}
		}
	}

	############
	### Exec ###
	############
	public function executeMessage(GWS_Message $message)
	{
		return $this->command($message)->execute($message);
	}
	
	/**
	 * Get command for a message
	 * @param GWS_Message $message
	 * @return GWS_Command
	 */
	public function command(GWS_Message $message)
	{
		$cmd = $message->cmd();
		if (!isset(self::$COMMANDS[$cmd]))
		{
			throw new GDOError('err_gws_unknown_cmd', [$cmd]);
		}
		return self::$COMMANDS[$cmd]->setMessage($message);
	}

	################
	### Override ###
	################
	public function init() {}
	public function timer() {}
	public function connect(GDO_User $user) {}
	public function disconnect(GDO_User $user) {}
	public function logout(GDO_User $user) {}
}
