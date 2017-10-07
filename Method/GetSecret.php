<?php
namespace GDO\Websocket\Method;

use GDO\Core\Method;
use GDO\Util\Common;
use GDO\Websocket\Module_Websocket;
use GDO\Core\Module_Core;
/**
 * Get cookie and user JSON for external apps.
 * @author gizmore
 * @since 4.0
 * @version 6.05
 */
final class GetSecret extends Method
{
	public function execute()
	{
		$json = array(
			'user' => Module_Core::instance()->gdoUserJSON(),
			'secret' => Module_Websocket::instance()->secret(),
			'count' => Common::getRequestInt('count', 0),
		);
		die(json_encode($json));
	}
}
