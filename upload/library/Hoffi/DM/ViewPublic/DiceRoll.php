<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_ViewPublic_DiceRoll extends XenForo_ViewPublic_Base
{
	public function renderJson()
	{
		$params = array('feedback' => $this->_params);
		return XenForo_ViewRenderer_Json::jsonEncodeForOutput($params);
	}
}