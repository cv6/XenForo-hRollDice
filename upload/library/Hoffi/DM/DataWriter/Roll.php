<?php

/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_DataWriter_Roll extends XenForo_DataWriter {

	protected $_existingDataErrorPhrase = 'Hoffi_RollNotFound';

	protected function _getFields()
	{
		return array(
				'xf_hoffi_dm_rolls' => array(
						'roll_id' => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
						'hash' => array('type' => self::TYPE_STRING),
						'post_id' => array('type' => self::TYPE_UINT, 'default' => 0),
						'user_id' => array('type' => self::TYPE_UINT, 'required' => true,
								'requiredError' => 'please_enter_valid_userid'),
						'thread_id' => array('type' => self::TYPE_UINT, 'required' => true,
								'requiredError' => 'please_enter_valid_threadid'),
						'comment' => array('type' => self::TYPE_STRING, 'default' => ''),
						'data' => array('type' => self::TYPE_STRING, 'required' => true,
								'requiredError' => 'data_missing'),
						'wireset' => array('type' => self::TYPE_STRING, 'required' => false, 'default' => NULL),
						'wins' => array('type' => self::TYPE_UINT),
						'result_sum' => array('type' => self::TYPE_UINT, 'default' => 0),
						'options' => array('type' => self::TYPE_STRING, 'required' => false),
						'roll_time' => array('type' => self::TYPE_UINT, 'default' => XenForo_Application::$time),
						'roll_state' => array('type' => self::TYPE_STRING, 'required' => false, 'default' => 'visible'),
				)
		);
	}
	protected function _getExistingData($data)
	{
		if (!$roll_id = $this->_getExistingPrimaryKey($data, 'roll_id'))
		{
			return false;
		}

		return array('xf_hoffi_dm_rolls' => $this->getModelFromCache('Hoffi_DM_Model_Rolls')->getRollByRoll($roll_id));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'roll_id = ' . $this->_db->quote($this->getExisting('roll_id'));
	}

	public function softDeleteRoll($roll_id)
	{
		$this->_db->query('
					UPDATE xf_hoffi_dm_rolls SET
						roll_state = "deleted"
					WHERE roll_id = ?
				', array($roll_id));
		
		return true;
	}

	function updateAllRollsByHashAndUserID($hash, $userid, $postid, $threadid)
	{
		return $this->_db->update('xf_hoffi_dm_rolls', 
						array(
								'post_id' => $postid,
								'thread_id' => $threadid,
								'hash' => NULL
						), 
						'hash = '. $this->_db->quote($hash).' and user_id = '. $this->_db->quote($userid)
		);
	}
	
	function updateRollStateByPostId($postId, $state)
	{
		return $this->_db->update('xf_hoffi_dm_rolls', 
						array(
								'roll_state' => $state
						), 
						'post_id = ' . $this->_db->quote($postId)
		);
	}

	function updateRollStateByThreadId($thread_id, $state)
	{
		$this->_db->query('
					UPDATE xf_hoffi_dm_rolls SET
						roll_state = ?
					WHERE thread_id = ?
				', array($state, $thread_id));
		return true;		
	}
	
	function assignRollsNewThreadFromThread($targetThreadId, $mergeFromThreadIds)
	{
		return $this->_db->update('xf_hoffi_dm_rolls', 
						array(
								'thread_id' => $targetThreadId
						), 
						'thread_id IN (' . $this->_db->quote($mergeFromThreadIds) . ')'
		);
	}
	
	function assignRollsNewThreadByRoll($targetThreadId, $mergeFromRollIds)
	{
		return $this->_db->update('xf_hoffi_dm_rolls', 
						array(
								'thread_id' => $targetThreadId
						), 
						'roll_id IN (' . $this->_db->quote($mergeFromRollIds) . ')'
		);
	}
	
	function assignRollsNewThreadAndPost($targetThreadId, $targetPostId, $mergeFromRollIds)
	{
		return $this->_db->update('xf_hoffi_dm_rolls', 
						array(
								'thread_id' => $targetThreadId,
								'post_id' => $targetPostId
						), 
						'roll_id IN (' . $this->_db->quote($mergeFromRollIds) . ')'
		);
	}
	
	function assignRollsNewUser($targetUserId, $mergeFromRollIds)
	{
		$this->_db->query('
					UPDATE xf_hoffi_dm_rolls SET
						user_id = ?
					WHERE roll_id IN (' . $this->_db->quote($mergeFromRollIds) . ')
				', array($targetUserId));
		return true;				
	}
	
	function assignRollsNewPost($new_post_id, $old_post_id)
	{
		$this->_db->query('
					UPDATE xf_hoffi_dm_rolls SET
						post_id = ?
					WHERE post_id = ?
				', array($new_post_id, $old_post_id));
		return true;				
	}

	public function updateThreadDiceRolls($threadId, $addRollCounter)
	{
		return $this->_db->query('
						UPDATE xf_thread
						SET h_dice_rolls = h_dice_rolls + ?
						WHERE thread_id = ?
					', array($addRollCounter, $threadId));
	}

	public function updateUserDiceRolls($userId, $addRollCounter)
	{
		return $this->_db->query('
						UPDATE xf_user
						SET diceroll_count = diceroll_count + ?
						WHERE user_id = ?
					', array($addRollCounter, $userId));
	}
	
	public function resetUserCounter()
	{
		$this->_db->update('xf_user',array('diceroll_count' => 0));
	}

	public function resetThreadCounter()
	{
		$this->_db->update('xf_thread',array('h_dice_rolls' => 0));
	}

}
