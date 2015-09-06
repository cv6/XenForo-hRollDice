<?php

/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_Model_Rolls extends XenForo_Model
{

	/**
	 * Gets the specified Dice Wireset
	 *
	 * @param string $id
	 *
	 * @return array|false
	 */
	public function getRollbyPost($post_id)
	{
		return $this->fetchAllKeyed('
			SELECT *
			FROM xf_hoffi_dm_rolls
			WHERE post_id = ? 
				AND roll_state = "visible"
			', 'roll_id', array($post_id));
	}

	public function getRollbyPostAndThread($post_id,$thread_id)
	{
		return $this->fetchAllKeyed('
			SELECT *
			FROM xf_hoffi_dm_rolls
			WHERE post_id = ? 
				AND thread_id = ?
				AND roll_state = "visible"
			', 'roll_id', array($post_id, $thread_id));
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
			WHERE post_id IN (' . $this->_getDb()->quote($postids) . ')', 'roll_id');
	}

	public function getRollsByThread($thread_id)
	{
		return $this->fetchAllKeyed('
			SELECT *
			FROM xf_hoffi_dm_rolls
			WHERE thread_id = ?
			and roll_state = "visible"
		', 'roll_id', $thread_id);
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

	public function getLatestRolls($c = 1)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_hoffi_dm_rolls
			WHERE  roll_state = "visible"
			ORDER BY roll_time DESC LIMIT ?', (int) $c);
	}

	public function getRollsByPostIdsGrouped(array $postids = array())
	{
		$allRolls = $this->getRollsByPostIds($postids);
		$allRollsGrouped = array();
		if (!empty($allRolls))
		{
			foreach ($allRolls as $roll_id => $roll)
			{
				if (empty($allRollsGrouped[$roll['post_id']]))
				{
					$allRollsGrouped[$roll['post_id']] = array();
				}
				$allRollsGrouped[$roll['post_id']][$roll_id] = $roll;
			}
		}
		unset($allRolls);
		return $allRollsGrouped;
	}

	public function getPostIdsInRollRange($start, $limit)
	{
		$db = $this->_getDb();

		return $db->fetchCol($db->limit('
				SELECT distinct(post_id) as post_id
				FROM xf_hoffi_dm_rolls
				WHERE roll_id > ? AND roll_state = "visible"
				ORDER BY roll_id
			', $limit), $start);
	}
	
	public function getRollsInRange($start, $limit)
	{
		$db = $this->_getDb();

		return $this->fetchAllKeyed($db->limit('
				SELECT roll_id, thread_id, user_id
				FROM xf_hoffi_dm_rolls
				WHERE roll_id > ? AND roll_state = "visible"
				ORDER BY roll_id
			', $limit), 'roll_id', $start);
	}
	
	public function fetchUserRollCount($userId)
	{
		$info = $this->_getDb()->fetchRow('
			SELECT count(*) as C
			FROM xf_hoffi_dm_rolls
			WHERE  roll_state = "visible"
			AND user_id = ?', $userId);
		
		return $info['C'];
	}

}
