<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_Route_PrefixAdmin_Rules implements XenForo_Route_Interface
{
	/**
	 * Match a specific route for an already matched rule.
	 *
	 * @see XenForo_Route_Interface::match()
	 */

	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		$action = $router->resolveActionWithStringParam($routePath, $request, 'rule');
		return $router->getRouteMatch('Hoffi_DM_ControllerAdmin_Rules', $action, 'dm_category');
	}


	/**
	 * Method to build a link to the specified page/action with the provided
	 * data and params.
	 *
	 * @see XenForo_Route_BuilderInterface
	 */
	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		return XenForo_Link::buildBasicLinkWithStringParam($outputPrefix, $action, $extension, $data, 'rule');
	}

}