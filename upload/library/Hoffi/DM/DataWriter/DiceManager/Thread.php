<?php

/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_DataWriter_DiceManager_Thread extends XFCP_Hoffi_DM_DataWriter_DiceManager_Thread
{

	protected function _getFields()
	{
		$fields = parent::_getFields();
		$fields['xf_thread']['h_dice_rolls'] = array('type' => self::TYPE_UINT_FORCED, 'default' => 0);
		return $fields;
	}

	protected function _getDefaultOptions()
	{
		$defaultOptions = parent::_getDefaultOptions();
		$defaultOptions['h_dice_rolls'] = '0';
		return $defaultOptions;
	}
	
	protected function _discussionPostSave()
	{
		parent::_discussionPostSave();
		if ($this->isInsert())
		{
			// Just Update Dice-Roll Count
		}
	}
	
	protected function _discussionPostDelete()
	{
		parent::_discussionPostDelete();
		$threadId = $this->_db->quote($this->get('thread_id'));
		$this->_db->delete('xf_hoffi_dm_rolls', "thread_id = $threadId");
	}
			
	public function rebuildDiscussionCounters($replyCount = false, $firstPostId = false, $lastPostId = false)
	{
		$threadId = $this->get('thread_id');
		parent::rebuildDiscussionCounters($replyCount, $firstPostId, $lastPostId);
		$rolls = $this->_getRollModel()->getRollsByThread($threadId);
		$this->set('h_dice_rolls', count($rolls));
	}

	protected function _getRollModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Rolls');
	}
}