<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_AdminSearchHandler_Rules extends XenForo_AdminSearchHandler_Abstract
{
	protected function _getTemplateName()
	{
		return 'quicksearch_hoffi_rules';
	}

	public function getPhraseKey()
	{
		return 'h_dm_rules';
	}

	public function search($searchText, array $phraseMatches = null)
	{
		$ruleModel = $this->getModelFromCache('Hoffi_DM_Model_Rules');
		return $ruleModel->getRulesForAdminQuickSearch($searchText);
	}

	public function getAdminPermission()
	{
		return 'dicemanager';
	}
}