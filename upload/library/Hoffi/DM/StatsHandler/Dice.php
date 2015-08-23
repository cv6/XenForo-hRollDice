<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_StatsHandler_Dice extends XenForo_StatsHandler_Abstract
{
	public function getStatsTypes()
	{
		return array(
			'rolls' => new XenForo_Phrase('h_dm_stats_rolls'),
			'post_rolls' =>  new XenForo_Phrase('h_dm_stats_posts_rolls'),
			'rolled_wiresets' =>  new XenForo_Phrase('h_dm_stats_rolled_wiresets'),
			'rolls_wins' =>  new XenForo_Phrase('h_dm_stats_rolls_wins')
		);
	}

	public function getData($startDate, $endDate)
	{
		$db = $this->_getDb();

		$rolls = $db->fetchPairs(
			$this->_getBasicDataQuery('xf_hoffi_dm_rolls', 'roll_time', 'roll_state = ?'),
			array($startDate, $endDate, 'visible')
		);

		$post_rolls = $db->fetchPairs(
			$this->_getBasicDataQuery('xf_hoffi_dm_rolls', 'roll_time', 'roll_state = ?', 'COUNT(DISTINCT(post_id))'),
			array($startDate, $endDate, 'visible')
		);

		$rolled_wiresets = $db->fetchPairs(
			$this->_getBasicDataQuery('xf_hoffi_dm_rolls', 'roll_time', 'roll_state = ?', 'COUNT(DISTINCT(wireset))'),
			array($startDate, $endDate, 'visible')
		);

		$rolls_wins = $db->fetchPairs(
			$this->_getBasicDataQuery('xf_hoffi_dm_rolls', 'roll_time', 'roll_state = ? AND wins > 0'),
			array($startDate, $endDate, 'visible')
		);

		return array(
			'rolls' => $rolls,
			'post_rolls' => $post_rolls,
			'rolled_wiresets' =>  $rolled_wiresets,
			'rolls_wins' => $rolls_wins
		);
	}
}