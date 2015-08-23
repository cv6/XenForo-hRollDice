<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_ControllerPublic_Forum extends XFCP_Hoffi_DM_ControllerPublic_Forum 
{
	
	public function actionCreateThread()
	{
		$response = parent::actionCreateThread();
		$response->params['forum']['allow_diceroll'] = $this->_getForumModel()->canRollDiceInForum($response->params['forum']);
		return $response;
	}	
	
}