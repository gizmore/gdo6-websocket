<?php
namespace GDO\Websocket\Method;

use GDO\Util\Common;
use GDO\Websocket\Module_Websocket;
use GDO\Core\Module_Core;
use GDO\Core\MethodAjax;
use GDO\Core\GDT_Array;

/**
 * Get cookie and user JSON for external apps.
 * 
 * @author gizmore
 * @version 6.10.6
 * @since 4.0.0
 */
final class GetSecret extends MethodAjax
{
	public function execute()
	{
		$json = [
			'user' => Module_Core::instance()->gdoUserJSON(),
			'secret' => Module_Websocket::instance()->secret(),
			'count' => Common::getRequestInt('count', 0),
		];
		return GDT_Array::makeWith($json);
	}
	
}
