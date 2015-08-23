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
		$this->_db->query('
					UPDATE xf_hoffi_dm_rolls SET
						post_id = ?,
						thread_id = ?,
						hash = NULL
					WHERE hash = ? and user_id = ?
				', array($postid, $threadid, $hash, $userid));
		return true;
	}
	
	function updateRollStateByPostId($post_id, $state)
	{
		$this->_db->query('
					UPDATE xf_hoffi_dm_rolls SET
						roll_state = ?
					WHERE post_id = ?
				', array($state, $post_id));
		return true;		
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
	
	function assignRollsNewThread($targetThreadId, $mergeFromThreadIds)
	{
		$this->_db->query('
					UPDATE xf_hoffi_dm_rolls SET
						thread_id = ?
					WHERE thread_id IN (' . $this->_db->quote($mergeFromThreadIds) . ')
				', array($targetThreadId));
		return true;				
	}
	
	function assignRollsNewThreadAndPost($targetThreadId, $targetPostId, $mergeFromRollIds)
	{
		$this->_db->query('
					UPDATE xf_hoffi_dm_rolls SET
						thread_id = ?,
						post_id = ?
					WHERE roll_id IN (' . $this->_db->quote($mergeFromRollIds) . ')
				', array($targetThreadId, $targetPostId));
		return true;				
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
	

	public function throwData()
	{
		var_export($this);
	}

}
