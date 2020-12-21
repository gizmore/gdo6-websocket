<?php
namespace GDO\Websocket\Server;

use GDO\Core\GDOError;
use GDO\User\GDO_User;
use GDO\Core\Logger;
use GDO\Session\GDO_Session;

require_once 'GWS_Command.php';
require_once 'GWS_CommandForm.php';
require_once 'GWS_CommandMethod.php';

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
	
	public static function webHookDB($message)
	{
		$message = json_decode($message, true);
		$event = $message['event'];
		$args = $message['args'];
		$param = [$event];
		if ($args)
		{
			$param = array_merge($param, $args);
		}
		return self::webHook($param);
	}
	
	public static function webHook(array $hookData)
	{
		$event = array_shift($hookData);
		$method_name = "hook$event";
		foreach (self::$COMMANDS as $command)
		{
			if (method_exists($command, $method_name))
			{
				call_user_func([$command, $method_name], ...$hookData);
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
			throw new GDOError('err_gws_unknown_cmd', [sprintf('0x%04X', $cmd)]);
		}
		$command = self::$COMMANDS[$cmd];
		if ($session = GDO_Session::instance())
		{
			$session->saveVar('sess_last_url', "ws://" . get_class($command));
		}
		Logger::logWebsocket("Executing " . get_class($command));
		return $command->setMessage($message);
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
