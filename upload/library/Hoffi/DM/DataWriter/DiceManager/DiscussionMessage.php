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
		if ($this->isInsert())
		{
			$rollDw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Roll', XenForo_DataWriter::ERROR_SILENT);
			$rollDw->updateAllRollsByHashAndUserID(
				XenForo_Helper_Cookie::getCookie('dice_hash'),
				$this->get('user_id'), $this->get('post_id'), $this->get('thread_id')
			);
		}
		else if ($this->isUpdate() && $this->isChanged('thread_id'))
		{
			$rollDw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Roll', XenForo_DataWriter::ERROR_SILENT);
			$rollDw->assignRollsNewThread(
				$this->get('thread_id'),
				$this->getExisting('thread_id')
			);
		}			
		else if ($this->isUpdate() && $this->isChanged('post_id'))
		{
			$rollDw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Roll', XenForo_DataWriter::ERROR_SILENT);
			$rollDw->assignRollsNewPost(
				$this->get('post_id'),
				$this->getExisting('post_id')
			);
		}			
		else if ($this->isUpdate() && $this->isChanged('message_state'))
		{
			// Not a good Idea... I must think about it...
//			$rollDw = XenForo_DataWriter::create('Hoffi_DataWriter_Roll', XenForo_DataWriter::ERROR_SILENT);
//			$rollDw->updateRollStateByPostId($this->get('post_id'), $this->get('message_state'));
		}
	}
	
	protected function _messagePostDelete()
	{
		parent::_messagePostDelete();
		$post_id = $this->_db->quote($this->get('post_id'));
		$this->_db->delete('xf_hoffi_dm_rolls', "post_id = $post_id");
	}

}