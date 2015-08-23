<?php

/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_Dice_Template {
	
	var $_modelCache = array();
	
	public static function changeForum(array $templateParts) {
		$newTemplate = ''; // hoffi_dm_forumoptions
		
		$ws =  XenForo_Model::create('Hoffi_DM_Model_Wireset');
		$all = $ws->getAllWiresets(true);
		return $newTemplate.$templateParts[0];
	}
	
	public function createTemplateObject($templateName, array $params = array())
	{
		return $this->_renderer->createTemplateObject($templateName, $params);
	}
	
}
