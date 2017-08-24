<?php
namespace GDO\Websocket\Method;

use GDO\Core\Method;
use GDO\GWF\Module_GWF;
use GDO\Util\Common;
use GDO\Websocket\Module_Websocket;
/**
 * Get cookie and user JSON for external apps.
 * @author gizmore
 * @since 4.0
 * @version 5.0
 */
final class GetSecret extends Method
{
	public function execute()
	{
		$json = array(
			'user' => Module_GWF::instance()->gwfUserJSON(),
			'secret' => Module_Websocket::instance()->secret(),
			'count' => Common::getRequestInt('count', 0),
		);
		die(json_encode($json));
	}
}
