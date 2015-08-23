<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_ControllerAdmin_DiceManager_Stats extends XFCP_Hoffi_DM_ControllerAdmin_DiceManager_Stats
{

	public function getStatsData($grouping, $defaultStart)
	{

		$viewParams = parent::getStatsData($grouping, $defaultStart);
//		$statsModel = $this->_getStatsModel();
//		$statsTypeOptions = $statsModel->getStatsTypeOptionsDice($statsTypes);
//		$statsTypePhrases = $statsModel->getStatsTypePhrasesDice($statsTypes);
//		$viewParams['statsTypeOptions'] += $statsTypeOptions;
//		$viewParams['$statsTypePhrases'] += $statsTypePhrases;

		return $viewParams;
	}

	/**
	 * @return XenForo_Model_Stats
	 */
	protected function _getStatsModel()
	{
		return $this->getModelFromCache('XenForo_Model_Stats');
	}
}