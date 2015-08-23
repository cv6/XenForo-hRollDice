<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_ControllerPublic_Thread extends XFCP_Hoffi_DM_ControllerPublic_Thread {
	
	public function actionIndex()
	{
		$response = parent::actionIndex();
		if (!empty($response->params) AND array_key_exists('thread', $response->params) AND array_key_exists('forum', $response->params))
		{
			$response->params['thread']['canRollDice'] = $this->_getThreadModel()->canRollDice($response->params['thread'],$response->params['forum']);
		}
		return $response;
	}
	
	public function actionReply() 
	{
		$response = parent::actionReply();
		if (!empty($response->params) AND array_key_exists('thread', $response->params) AND array_key_exists('forum', $response->params))
		{
			$response->params['thread']['canRollDice'] = $this->_getThreadModel()->canRollDice($response->params['thread'],$response->params['forum']);		
		}
		return $response;		
	}

}

