<?php

/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_Model_Rolls extends XenForo_Model {

	/**
	 * Gets the specified Dice Wireset
	 *
	 * @param string $id
	 *
	 * @return array|false
	 */
	public function getRollbyPost($post_id)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_hoffi_dm_rolls
			WHERE post_id = ?
			and roll_state = "visible"
		', $post_id);
	}

	public function getRollbyRoll($roll_id)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_hoffi_dm_rolls
			WHERE roll_id = ?
			and roll_state = "visible"
		', $roll_id);
	}

	public function getRollsByHashAndUserId($hash, $userid)
	{
		return $this->fetchAllKeyed('
			SELECT *
			FROM xf_hoffi_dm_rolls
			WHERE user_id = ? AND hash = ?', 'roll_id', array($userid, $hash));
	}

	public function getRollsByPostIds(array $postids = array())
	{
		return $this->fetchAllKeyed('
			SELECT *
			FROM xf_hoffi_dm_rolls
			WHERE post_id IN (' . $this->_getDb()->quote($postids) . ')','roll_id');
	}
	
	public function getRollsByThread($thread_id)
	{
		return $this->_getDb()->fetchAllKeyed('
			SELECT *
			FROM xf_hoffi_dm_rolls
			WHERE thread_id = ?
			and roll_state = "visible"
		', $thread_id);
	}

	public function getLastRollFromThread($thread_id)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_hoffi_dm_rolls
			WHERE thread_id = ?
			AND roll_state = "visible" 
			ORDER BY roll_time DESC 
			LIMIT 1
		', $thread_id);
	}

	// for Cron
	public function deleteUnassignedRolls()
	{
		$this->_getDb()->delete('xf_hoffi_dm_rolls', 'hash IS NOT NULL AND roll_time < DATE_SUB(NOW(), INTERVAL 1 DAY)');
	}

	public function fetchRollsForPost($post_id)
	{
		return $this->fetchAllKeyed('
			SELECT *
			FROM xf_hoffi_dm_rolls
			WHERE post_id = ?', 'roll_id', array($post_id));
	}
	
	public function getLatestRolls($c=1)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_hoffi_dm_rolls
			WHERE  roll_state = "visible"
			ORDER BY roll_time DESC LIMIT ?' , (int)$c);		
	}


}
