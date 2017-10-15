<?php
namespace GDO\Websocket;

use GDO\Core\GDO_Module;
use GDO\Date\GDT_Duration;
use GDO\File\GDT_Path;
use GDO\Net\GDT_Url;
use GDO\UI\GDT_Bar;
use GDO\DB\GDT_Checkbox;
use GDO\DB\GDT_Int;
use GDO\User\GDO_Session;
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
final class Module_Websocket extends GDO_Module
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
			GDT_Checkbox::make('ws_autoconnect')->initial('0'),
			GDT_Checkbox::make('ws_guests')->initial('1'),
			GDT_Int::make('ws_port')->bytes(2)->unsigned()->initial('61221'),
			GDT_Duration::make('ws_timer')->initial('0'),
			GDT_Path::make('ws_processor')->initial($this->defaultProcessorPath())->existingFile(),
			GDT_Url::make('ws_url')->initial('ws://'.GDT_Url::host().':61221')->pattern('#^wss?://.*#'),
		);
	}
	public function cfgAutoConnect() { return $this->getConfigValue('ws_autoconnect'); }
	public function cfgUrl() { return $this->getConfigValue('ws_url'); }
	public function cfgPort() { return $this->getConfigValue('ws_port'); }
	public function cfgTimer() { return $this->getConfigValue('ws_timer'); }
	public function cfgWebsocketProcessorPath() { return $this->getConfigValue('ws_processor'); }
	public function cfgAllowGuests() { return $this->getConfigValue('ws_guests'); }

	public function defaultProcessorPath() { return sprintf('%sGDO/Websocket/Server/GWS_NoCommands.php', GWF_PATH); }
	public function processorClass()
	{
	    $path = Strings::substrFrom($this->cfgWebsocketProcessorPath(), GWF_PATH);
	    $path = str_replace('/', '\\', $path);
	    return Strings::substrTo($path, '.'); 
	}

	##########
	### JS ###
	##########
	public function onIncludeScripts()
	{
	    if (module_enabled('Angular'))
	    {
    		$this->addJavascript('js/gwf-websocket-srvc.js');
    		$this->addJavascript('js/gwf-ws-navbar-ctrl.js');
	    }
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
		$sess = GDO_Session::instance();
		return $sess ? $sess->cookieContent() : 'resend';
	}
	
	##############
	### Navbar ###
	##############
	public function hookLeftBar(GDT_Bar $navbar)
	{
		$this->templatePHP('leftbar.php', ['navbar' => $navbar]);
	}
}
