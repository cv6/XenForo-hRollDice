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

	protected function _discussionPostSave()
	{
		parent::_discussionPostSave();
		if ($this->isUpdate())
		{

		}
	}
	
	protected function _discussionPostDelete()
	{
		parent::_discussionPostDelete();
		$thread_id = $this->_db->quote($this->get('thread_id'));
		$this->_db->delete('xf_hoffi_dm_rolls', "thread_id = $thread_id");
	}
	
	public function updateDiceRolls($thread_id, $add_roll_counter)
	{
		
	}
	
	public function updateCountersAfterMessageSave(XenForo_DataWriter_DiscussionMessage $messageDw)
	{
		parent::updateCountersAfterMessageSave($messageDw);
	}
}