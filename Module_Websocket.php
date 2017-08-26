<?php
namespace GDO\Websocket;

use GDO\Core\Module;
use GDO\Date\GDO_Duration;
use GDO\File\GDO_Path;
use GDO\Net\GDO_Url;
use GDO\Template\GDO_Bar;
use GDO\Type\GDO_Checkbox;
use GDO\Type\GDO_Int;
use GDO\User\Session;
use GDO\Util\Javascript;
use GDO\Util\Strings;
/**
 * Websocket server module.
 * 
 * @author gizmore
 * 
 * @since 4.1
 * @version 5.0
 */
final class Module_Websocket extends Module
{
	##############
	### Module ###
	##############
	public $module_priority = 45;
	public function onLoadLanguage() { return $this->loadLanguage('lang/websocket'); }

	##############
	### Config ###
	##############
	public function getConfig()
	{
		return array(
			GDO_Checkbox::make('ws_autoconnect')->initial('0'),
			GDO_Checkbox::make('ws_guests')->initial('1'),
			GDO_Int::make('ws_port')->bytes(2)->unsigned()->initial('61221'),
			GDO_Duration::make('ws_timer')->initial('0'),
			GDO_Path::make('ws_processor')->initial($this->defaultProcessorPath())->existingFile(),
			GDO_Url::make('ws_url')->initial('ws://'.GDO_Url::host().':61221')->pattern('#^wss?://.*#'),
		);
	}
	public function cfgAutoConnect() { return $this->getConfigValue('ws_autoconnect'); }
	public function cfgUrl() { return $this->getConfigValue('ws_url'); }
	public function cfgPort() { return $this->getConfigValue('ws_port'); }
	public function cfgTimer() { return $this->getConfigValue('ws_timer'); }
	public function cfgWebsocketProcessorPath() { return $this->getConfigValue('ws_processor'); }
	public function cfgAllowGuests() { return $this->getConfigValue('ws_guests'); }

	public function defaultProcessorPath() { return sprintf('%sGDO/Websocket/server/GWS_NoCommands.php', GWF_PATH); }
	public function processorClass() { return Strings::substrTo(basename($this->cfgWebsocketProcessorPath()), '.'); }

	##########
	### JS ###
	##########
	public function onIncludeScripts()
	{
		$this->addJavascript('js/gwf-websocket-srvc.js');
		$this->addJavascript('js/gwf-ws-navbar-ctrl.js');
		$this->addJavascript('js/gws-message.js');
		Javascript::addJavascriptInline($this->configJS());
	}
	
	private function configJS()
	{
		return sprintf('window.GDO_CONFIG.ws_url = "%s";
window.GDO_CONFIG.ws_secret = "%s";
window.GDO_CONFIG.ws_autoconnect = %s;',
				$this->cfgUrl(), $this->secret(), $this->cfgAutoConnect()?'1':'0');
	}
	
	public function secret()
	{
		$sess = Session::instance();
		return $sess ? $sess->cookieContent() : 'resend';
	}
	
	##############
	### Navbar ###
	##############
	public function hookLeftBar(GDO_Bar $navbar)
	{
		$this->templatePHP('leftbar.php', ['navbar' => $navbar]);
	}
}
