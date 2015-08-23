<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_AdminSearchHandler_Wiresets extends XenForo_AdminSearchHandler_Abstract
{
	protected function _getTemplateName()
	{
		return 'quicksearch_hoffi_wireset';
	}

	public function getPhraseKey()
	{
		return 'h_dm_wireset';
	}

	public function search($searchText, array $phraseMatches = null)
	{
		$wsModel = $this->getModelFromCache('Hoffi_DM_Model_Wireset');
		return $wsModel->getWiresetForAdminQuickSearch($searchText);
	}

	public function getAdminPermission()
	{
		return 'dicemanager';
	}
}