<?php
namespace GDO\Websocket\Server;
use GDO\Core\Debug;
use GDO\Core\Logger;
use GDO\Core\Module_Core;
use GDO\File\Filewalker;
use GDO\Net\GDT_IP;
use GDO\User\GDO_Session;
use GDO\User\GDO_User;
use GDO\Websocket\Module_Websocket;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Exception;
use GDO\Core\ModuleLoader;
use GDO\Core\WithInstance;
use GDO\Core\GDO_Hook;
use GDO\Core\GDO;
use GDO\Language\GDT_Language;
use GDO\Language\Trans;

include 'GWS_Message.php';

final class GWS_Server implements MessageComponentInterface
{
	use WithInstance;
	
	/**
	 * @var GWS_Commands
	 */
	private $handler;
	private $allowGuests;
	
	private $gws;
	private $server;
	private $ipc;
	
	public function __construct()
	{
		self::$INSTANCE = $this;
		if (GWF_IPC === 'db')
		{
			# all fine
		}
		elseif (GWF_IPC)
		{
			for ($i = 1; $i < GWF_IPC; $i++)
			{
				msg_remove_queue(msg_get_queue($i));
			}
			$this->initIPC();
		}
	}
	
	public function initIPC()
	{
		$key = ftok(GWF_PATH.'temp/ipc.socket', 'G');
		$this->ipc = msg_get_queue($key);
	}
	
	public function mainloop($timerInterval=0)
	{
		Logger::logMessage("GWS_Server::mainloop()");
		if ($timerInterval > 0)
		{
			$this->server->loop->addPeriodicTimer($timerInterval/1000.0, [$this->handler, 'timer']);
		}

		# IPC timer
		if (GWF_IPC === 'db')
		{
			# 3 seconds db poll alternative
			GDO_Hook::table()->truncate();
			$this->server->loop->addPeriodicTimer(3.00, [$this, 'ipcdbTimer']);
		}
		elseif (GWF_IPC)
		{
			$this->server->loop->addPeriodicTimer(0.250, [$this, 'ipcTimer']);
		}
		$this->server->run();
	}
	
	/**
	 * Poll a message and delete it afterwards.
	 */
	public function ipcdbTimer()
	{
		if ($message = GDO_Hook::table()->select()->first()->exec()->fetchValue())
		{
			try {
				GWS_Commands::webHookDB($message);
			} catch (\Exception $ex) {
				Logger::logException($ex);
			}
			GDO_Hook::table()->deleteWhere("hook_message=".GDO::quoteS($message))->exec();
			$this->ipcdbTimer();
		}
	}
	
	
	public function ipcTimer()
	{
		$message = null; $messageType = 0; $error = 0;
		if (msg_receive($this->ipc, 0x612, $messageType, 65535, $message, true, MSG_IPC_NOWAIT, $error))
		{
			if ($message)
			{
				try {
					Logger::logWebsocket("calling webHook: ".json_encode($message));
					GWS_Commands::webHook($message);
				} catch (\Exception $ex) {
					Logger::logException($ex);
				}
				$this->ipcTimer();
			}
		}
		if ($error)
		{
			Logger::logError("IPC msg_receive failed with code: $error");
			msg_remove_queue($this->ipc);
			$this->ipc = null;
			$this->initIPC();
		}
	}
	
	###############
	### Ratchet ###
	###############
	public function onOpen(ConnectionInterface $conn)
	{
		Logger::logCron(sprintf("GWS_Server::onOpen()"));
	}

	public function onMessage(ConnectionInterface $from, $data)
	{
		die('NON BINARY MESSAGE NOT SUPPORTED ANYMORE');
		
// 		printf("%s >> %s\n", $from->user() ? $from->user()->displayName() : '???', $data);
// 		$message = new GWS_Message($data, $from);
// 		$message->readTextCmd();
// 		if ($from->user())
// 		{
// 			GDT_IP::$CURRENT = $from->getRemoteAddress();
// 			GDO_User::$CURRENT = $from->user();
// 			GDO_Session::reloadID($from->user()->tempGet('sess_id'));
// 			try
// 			{
// 				$this->handler->executeMessage($message);
// 			}
// 			catch (Exception $e)
// 			{
// 				Logger::logException($e);
// 				$message->replyErrorMessage($message->cmd(), $e->getMessage());
// 			}
// 		}
// 		else
// 		{
// 			$message->replyError(0x0002);
// 		}
	}
	
	public function onBinaryMessage(ConnectionInterface $from, $data)
	{
		printf("%s >> BIN\n", $from->user() ? $from->user()->displayNameLabel() : '???');
		GDT_IP::$CURRENT = $from->getRemoteAddress();
		echo GWS_Message::hexdump($data);
		$message = new GWS_Message($data, $from);
		$message->readCmd();
		if (!$from->user())
		{
			$this->onAuthBinary($message);
		}
		else
		{
			try {
				$_REQUEST = array('fmt'=>'ws'); # start with a blank request emulation
				/**
				 * @var GDO_User $user
				 */
				$user = GDO_User::$CURRENT = $from->user();
				$sessid = $user->tempGet('sess_id');
				GDO_Session::reloadID($sessid);
				$langISO = $user->tempGet('lang_iso');
				$langISO = $langISO ? $langISO : $user->getLangISO();
				Trans::setISO($langISO);
				$this->handler->executeMessage($message);
			}
			catch (Exception $e) {
				Logger::logWebsocket(Debug::backtraceException($e, false));
				$message->replyErrorMessage($message->cmd(), $e->getMessage());
			}
		}
	}
	
	public function onAuthBinary(GWS_Message $message)
	{
		if ($message->cmd() !== 0x0001)
		{
			$message->replyErrorMessage(0x0001, "Wrong authentication command");
		}
		elseif (!($cookie = $message->readString()))
		{
			$message->replyErrorMessage(0x0002, "No cookie was sent");
		}
		elseif (!GDO_Session::reloadCookie($cookie))
		{
			$message->replyErrorMessage(0x0003, "Could not load session");
		}
		elseif (!($user = GDO_User::current()))
		{
			$message->replyError(0x0004, "Cannot load user for session");
		}
		else
		{
			# Connect user
			$conn = $message->conn();
			$conn->setUser($user);
			$user->tempSet('sess_id', GDO_Session::instance()->getID());
			$message->replyText('AUTH', json_encode(Module_Core::instance()->gdoUserJSON()));
			# Add with event
			GWS_Global::addUser($user, $conn);
		}
	}
	
	public function onClose(ConnectionInterface $conn)
	{
		Logger::logCron(sprintf("GWS_Server::onClose()"));
		if ($user = $conn->user())
		{
			$this->handler->disconnect($user);
			$conn->setUser(false);
			GWS_Global::removeUser($user);
		}
	}
	
	public function onError(ConnectionInterface $conn, \Exception $e)
	{
		Logger::logCron(sprintf("GWS_Server::onError()"));
	}
	
	public function onLogout(GDO_User $user)
	{
		$this->handler->logout($user);
	}
	
	############
	### Init ###
	############
	public function initGWSServer($handler, Module_Websocket $gws)
	{
		$this->handler = $handler;
		$this->gws = $gws;
		$port = $gws->cfgPort();
		Logger::logCron("GWS_Server::initGWSServer() Port $port");
		$this->allowGuests = $gws->cfgAllowGuests();
// 		$this->consoleLog = GWS_Global::$LOGGING = $gws->cfgConsoleLogging();
		$this->server = IoServer::factory(new HttpServer(new WsServer($this)), $port, $this->socketOptions());
		$this->handler->init();
		$_REQUEST['fmt'] = 'ws';
		$this->registerCommands();
		return true;
	}
	
	private function registerCommands()
	{
		foreach (ModuleLoader::instance()->getModules() as $module)
		{
			Filewalker::traverse($module->filePath('Websocket'), '*', [$this, 'registerModuleCommands']);
		}
	}
	
	public function registerModuleCommands($entry, $path)
	{
		include $path;
	}
	
	private function socketOptions()
	{
// 		$pemCert = trim($this->gws->cfgWebsocketCert());
// 		if (empty($pemCert))
		{
			return array();
		}
// 		else
// 		{
// 			return array(
// 				'ssl' => array(
// 					'local_cert' => $pemCert,
// 				),
// 			);
// 		}
	}
	
	/**
	 * @return \GDO\Websocket\Server\GWS_Commands
	 */
	public function getHandler()
	{
		return $this->handler;
	}
}
