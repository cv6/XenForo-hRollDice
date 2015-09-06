<?php

/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_DataWriter_DiceManager_DiscussionMessage extends XFCP_Hoffi_DM_DataWriter_DiceManager_DiscussionMessage
{

	protected function _messagePostSave()
	{
		parent::_messagePostSave();
		$rollDw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Roll', XenForo_DataWriter::ERROR_SILENT);
		if ($this->isInsert())
		{
			$rolls = $rollDw->updateAllRollsByHashAndUserID(
				XenForo_Helper_Cookie::getCookie('dice_hash'),
				$this->get('user_id'), $this->get('post_id'), $this->get('thread_id')
			);
			$rollDw->updateThreadDiceRolls($this->get('thread_id'), $rolls);
			$rollDw->updateUserDiceRolls($this->get('user_id'), $rolls);			
		}
		if ($this->isUpdate() && $this->isChanged('thread_id'))
		{
			$rolls = $rollDw->assignRollsNewThread(
				$this->get('thread_id'),
				$this->getExisting('thread_id')
			);
			$rollDw->updateThreadDiceRolls($this->get('thread_id'), $rolls);
			$rollDw->updateThreadDiceRolls($this->getExisting('thread_id'), -$rolls);
		}
		if ($this->isUpdate() && $this->isChanged('post_id'))
		{
			$rollDw->assignRollsNewPost(
				$this->get('post_id'),
				$this->getExisting('post_id')
			);
		}			
		if ($this->isUpdate() && $this->isChanged('user_id'))
		{
			$rollDw->assignRollsNewPost(
				$this->get('post_id'),
				$this->getExisting('post_id')
			);
			$rollDw->updateUserDiceRolls($this->get('user_id'), $rolls);			
			$rollDw->updateUserDiceRolls($this->getExisting('user_id'), -$rolls);			
		}			
		if ($this->isUpdate() && $this->isChanged('message_state'))
		{
			$rolls = $rollDw->updateRollStateByPostId(
					$this->get('post_id'),
					$this->get('message_state')
				);
			if ($this->get('message_state') == 'deleted')
			{
				$rolls *= -1;
			}
			$rollDw->updateThreadDiceRolls($this->get('thread_id'), $rolls);
			$rollDw->updateUserDiceRolls($this->get('user_id'), $rolls);
		}
	}
	
	protected function _messagePostDelete()
	{
		parent::_messagePostDelete();
		if ($this->get('message_state') != 'deleted')
		{
			$rollDw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Roll', XenForo_DataWriter::ERROR_SILENT);
			// We must delete in two Steps, to calculate the thread counter...
			$post_id = $this->_db->quote($this->get('post_id'));
			$rolls_visible = $this->_db->delete('xf_hoffi_dm_rolls', "roll_state = 'visible' AND post_id = $post_id");
			$rolls_invisible = $this->_db->delete('xf_hoffi_dm_rolls', "roll_state <> 'visible' AND post_id = $post_id");
			$rollDw->updateThreadDiceRolls($this->get('thread_id'), -$rolls_visible);
			$rollDw->updateUserDiceRolls($this->get('user_id'), -$rolls_visible);
		}
	}
	
	protected function _getRollModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Rolls');
	}
	
}