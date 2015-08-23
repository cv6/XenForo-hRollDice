<?php

/**
 * Inline moderation actions for posts
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_ControllerPublic_InlineMod_Post extends XFCP_Hoffi_DM_ControllerPublic_InlineMod_Post
{

	/**
	 * Post merge handler
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionMerge()
	{
		if (!$this->isConfirmedPost())
		{
			$responseView = parent::actionMerge();
			$allPosts = $responseView->params['postIds'];
			$rollInfos = $this->_getRollModel()->getRollsByPostIds($allPosts);
			$responseView->params['rolls'] = count($rollInfos);
			$responseView->params['rollInfos'] = $rollInfos;
			return $responseView;
		}
		else
			return parent::actionMerge();
	}
	
	protected function _getRollModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Rolls');
	}
	

}