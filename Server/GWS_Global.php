<?php
namespace GDO\Websocket\Server;

use GDO\Core\Logger;
use GDO\User\GDO_User;

final class GWS_Global
{
	/**
	 * @var GDO_User[]
	 */
	public static $USERS = array();
	public static $CONNECTIONS = array();
	
	##################
	### GDO_User cache ###
	##################
	public static function addUser(GDO_User $user, $conn)
	{
// 		Logger::logWebsocket("GWS_Global.addUser({$user->getID()})");
		if ($user->isPersisted())
		{
			if (isset(self::$CONNECTIONS[$user->getID()]))
			{
				GWS_Global::disconnect($user, t('err_was_already_connected'));
			}
			self::$USERS[$user->getID()] = $user;
			self::$CONNECTIONS[$user->getID()] = $conn;
			GWS_Server::instance()->getHandler()->connect($user);
		}
	}
	
	public static function removeUser(GDO_User $user, $reason='NO_REASON')
	{
		$key = $user->getID();
		if (isset(self::$USERS[$key]))
		{
			unset(self::$USERS[$key]);
			GWS_Global::disconnect($user, $reason);
		}
	}
	
	/**
	 * @param int $id
	 * @return GDO_User
	 */
	public static function getUserByID($id)
	{
		return @self::$USERS[$id];
	}
	
	public static function getOrLoadUserById($id)
	{
		if ($user = self::getUserByID($id))
		{
			return $user;
		}
		return self::loadUserById($id);
	}
	
	public static function loadUserById($id)
	{
		if ($user = GDO_User::getById($id))
		{
			self::$USERS[$id] = $user;
		}
		return $user;
	}
	
	public static function broadcast($payload)
	{
		Logger::logWebsocket(sprintf("!BROADCAST! << %s", $payload));
		foreach (self::$USERS as $user)
		{
			self::send($user, $payload);
		}
		return true;
	}

	public static function broadcastBinary($payload)
	{
		Logger::logWebsocket(sprintf("Broadcast to %d users.", count(self::$USERS)));
		GWS_Message::hexdump($payload);
		foreach (self::$USERS as $user)
		{
			self::sendBinary($user, $payload);
		}
		return true;
	}
	
	public static function send(GDO_User $user, $payload)
	{
		if ($conn = @self::$CONNECTIONS[$user->getID()])
		{
			Logger::logWebsocket(sprintf("%s << %s", $user->displayNameLabel(), $payload));
			$conn->send($payload);
			return true;
		}
		else
		{
			Logger::logError(sprintf('GDO_User %s not connected.', $user->displayNameLabel()));
			return false;
		}
	}
	
	public static function sendBinary(GDO_User $user, $payload)
	{
		if ($conn = @self::$CONNECTIONS[$user->getID()])
		{
			Logger::logWebsocket(sprintf("%s << BIN", $user->displayNameLabel()));
			GWS_Message::hexdump($payload);
			$conn->sendBinary($payload);
			return true;
		}
		else
		{
			Logger::logWebsocket(sprintf('GDO_User %s not connected.', $user->displayName()));
			return false;
		}
	}
	##################
	### Connection ###
	##################
	public static function disconnect(GDO_User $user, $reason="no_reason")
	{
		if ($conn = @self::$CONNECTIONS[$user->getID()])
		{
			GWS_Server::instance()->getHandler()->disconnect($user);
			$conn->send("CLOSE:".$reason);
			unset(self::$USERS[$user->getID()]);
			unset(self::$CONNECTIONS[$user->getID()]);
			$conn->close();
		}
	}

}
