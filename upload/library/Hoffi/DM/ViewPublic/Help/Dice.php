<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_ViewPublic_Help_Dice extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$this->_params['dice'] = 'dd';
	}

}