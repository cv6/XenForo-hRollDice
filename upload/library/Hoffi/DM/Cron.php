<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_Cron {

	public static function deleteUnassignedRolls()
	{
		/* @var $trophyModel XenForo_Model_Trophy */
		$rollModel = XenForo_Model::create('Hoffi_DM_Model_Rolls');
		$rollModel->deleteUnassignedRolls();
	}

}
