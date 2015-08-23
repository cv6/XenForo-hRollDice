<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_ControllerPublic_Help_Dice extends XFCP_Hoffi_DM_ControllerPublic_Help_Dice
{
	public function actionIndex()
	{
		$wrap = parent::actionIndex();
		return $wrap;
	}

	public function actionDice()
	{
		$viewParams = array(
			'wiresets' => $this->getModelFromCache('Hoffi_DM_Model_Wireset')->getAllWiresets(true),
			'dicelist' => $this->getModelFromCache('Hoffi_DM_Model_Dice')->getAllDice(true)
		);

		return $this->_getWrapper('Dice',
			$this->responseView('Hoffi_DM_ViewPublic_Help_Dice', 'hoffi_dice_help', $viewParams)
		);
	}

}