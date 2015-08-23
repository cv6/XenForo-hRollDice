<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_Route_Prefix_DiceThread implements XenForo_Route_Interface
{
	/**
	 * Match a specific route for an already matched prefix.
	 *
	 * @see XenForo_Route_Interface::match()
	 */
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		$action = $router->resolveActionWithIntegerOrStringParam($routePath, $request, 'node_id', 'node_name');
		return $router->getRouteMatch('Hoffi_DM_ControllerPublic_Dice', $action, 'dice');
	}

	/**
	 * Method to build a link to the specified page/action with the provided
	 * data and params.
	 *
	 * @see XenForo_Route_BuilderInterface
	 */
	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		if (is_array($data) && !empty($data['node_name']))
		{
			return XenForo_Link::buildBasicLinkWithStringParam($outputPrefix, $action, $extension, $data, 'node_name');
		}
		else
		{
			// for situations such as an array with thread and node info
			if (isset($data['node_title']))
			{
				$data['title'] = $data['node_title'];
			}

			return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'node_id', 'title');
		}
	}
}
