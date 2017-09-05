<?php
namespace GDO\Websocket\Server;

use GDO\Core\Logger;
use GDO\User\GDO_User;

final class GWS_Global
{
// 	public static $LOGGING = false;
	/**
	 * @var User[]
	 */
	public static $USERS = array();
	public static $CONNECTIONS = array();
	
	##################
	### GDO_User cache ###
	##################
	public static function addUser(GDO_User $user, $conn)
	{
		self::$USERS[$user->getID()] = $user;
		self::$CONNECTIONS[$user->getID()] = $conn;
	}
	
	public static function recacheUser(string $userid)
	{
		GDO_User::table()->cache->uncacheID($userid);

		if (isset(self::$USERS[$userid]))
		{
			unset(self::$USERS[$userid]);
			return self::loadUserById($userid);
		}
		else
		{
			return GDO_User::getById($userid);
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
	
// 	public static function getUser($name)
// 	{
// 		return isset(self::$USERS[$name]) ? self::$USERS[$name] : false;
// 	}
	
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
		if ($user = GDO_User::getByID($id))
		{
			self::$USERS[$id] = $user;
		}
		return $user;
	}
	
	
// 	public static function getOrLoadUser($name, $allowGuests)
// 	{
// 		if (false !== ($user = self::getUser($name)))
// 		{
// 			return $user;
// 		}
// 		return self::loadUser($name, $allowGuests);
// 	}
	
	#################
	### Messaging ###
	#################
// 	/**
// 	 * @deprecated
// 	 * @param GDO_User $user
// 	 * @param string $command
// 	 * @param string $payload
// 	 * @return boolean
// 	 */
// 	public static function sendCommand(GDO_User $user, $command, $payload)
// 	{
// 		return self::send($user, "$command:$payload");
// 	}

// 	/**
// 	 * @deprecated
// 	 * @param GDO_User $user
// 	 * @param string $command
// 	 * @param array $payload
// 	 * @return boolean
// 	 */
// 	public static function sendJSONCommand(GDO_User $user, $command, $payload)
// 	{
// 		return self::sendCommand($user, $command, json_encode($payload));
// 	}
	
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
		Logger::logWebsocket(sprintf("!BROADCAST!"));
		GWS_Message::hexdump($payload);
		foreach (self::$USERS as $user)
		{
			self::sendBinary($user, $payload);
		}
		return true;
	}
	
	public static function send(GDO_User $user, $payload)
	{
		if ($conn = self::$CONNECTIONS[$user->getID()])
		{
			Logger::logWebsocket(sprintf("%s << %s", $user->displayName(), $payload));
			$conn->send($payload);
			return true;
		}
		else
		{
			Logger::logError(sprintf('GDO_User %s not connected.', $user->displayName()));
			return false;
		}
	}
	
	public static function sendBinary(GDO_User $user, $payload)
	{
		if ($conn = self::$CONNECTIONS[$user->getID()])
		{
			Logger::logWebsocket(sprintf("%s << BIN", $user->displayName()));
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

	###############
	### Private ###
	###############
// 	private static function loadUser($name, $allowGuests)
// 	{
// 		$letter = $name[0];
// 		if (($letter >= '0') && ($letter <= '9'))
// 		{
// 			if ($allowGuests) {
// 				return self::getUserBySessID($name);
// 			} else {
// 				return false;
// 			}
// 		}
// 		else
// 		{
// 			return GDO_User::getByName($name);
// 		}
// 	}
	
// 	private static function getUserBySessID($number)
// 	{
// 		$number = (string)$number;
// 		if (!isset(self::$USERS[$number]))
// 		{
// 			$user = GWF_Guest::getGuest($number);
// 			$user->setVar('user_password', sha1(GWF_SECRET_SALT.$number.GWF_SECRET_SALT));
// 			self::$USERS[$number] = $user;
// 		}
// 		return self::$USERS[$number];
// 	}
	
	##################
	### Connection ###
	##################
	public static function disconnect(GDO_User $user, $reason="NO_REASON")
	{
		if ($conn = @self::$CONNECTIONS[$user->getID()])
		{
			$conn->send("CLOSE:".$reason);
			unset(self::$CONNECTIONS[$user->getID()]);
		}
	}
	
// 	public static function isConnected($user)
// 	{
// 		return !!isset(self::$CONNECTIONS[$user->getID()]);
// 	}
	

// 	public static function setConnectionInterface($user, $conn)
// 	{
// 		if (self::isConnected($user))
// 		{
// 			self::disconnect($user);
// 		}
// 		self::$CONNECTIONS[$user->getID()] = $conn;
// 	}
	
// 	public static function getConnectionInterface($user)
// 	{
// 		return isset(self::$CONNECTIONS[$user->getID()]) ? self::$CONNECTIONS[$user->getID()] : false;
// 	}

}
