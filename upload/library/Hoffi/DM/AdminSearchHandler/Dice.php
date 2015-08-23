<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_AdminSearchHandler_Dice extends XenForo_AdminSearchHandler_Abstract
{
	/**
	 *
	 * @return string Template Name
	 */
	protected function _getTemplateName()
	{
		return 'quicksearch_hoffi_dice';
	}

	/**
	 *
	 * @return string
	 */
	public function getPhraseKey()
	{
		return 'h_dm_dice';
	}

	/**
	 *
	 * @param string $searchText
	 * @param array $phraseMatches
	 * @return XenForo_Model
	 */
	public function search($searchText, array $phraseMatches = null)
	{
		$diceModel = $this->getModelFromCache('Hoffi_DM_Model_Dice');
		return $diceModel->getDiceForAdminQuickSearch($searchText);
	}

	/**
	 *
	 * @return string
	 */
	public function getAdminPermission()
	{
		return 'dicemanager';
	}
}