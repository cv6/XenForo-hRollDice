<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_ModeratorLogHandler_Roll extends XenForo_ModeratorLogHandler_Abstract
{
	protected function _log(array $logUser, array $content, $action, array $actionParams = array(), $parentContent = null)
	{
		if (isset($content['title']))
		{
			$title = $content['title'];
		}
		else
		{
			$thread = XenForo_Model::create('XenForo_Model_Thread')->getThreadById($content['thread_id']);
			$title = ($thread ? $thread['title'] : '');
		}
		
		$dw = XenForo_DataWriter::create('XenForo_DataWriter_ModeratorLog');
		$set = array(
			'user_id' => $logUser['user_id'],
			'content_type' => 'dice_roll',
			'content_id' => $content['post_id'],
			'content_user_id' => $content['user_id'],
			'content_username' => $content['username'],
			'content_title' => $title,
			'content_url' => XenForo_Link::buildPublicLink('posts', $content),
			'discussion_content_type' => 'thread',
			'discussion_content_id' => $content['thread_id'],
			'action' => $action,
			'action_params' => $actionParams
		);
		$dw->bulkSet($set);
		$dw->save();
		
		return $dw->get('moderator_log_id');
	}

	protected function _prepareEntry(array $entry)
	{
		$elements = json_decode($entry['action_params'], true);
		if ($entry['action'] == 'delete_roll')
		{
			$entry['actionText'] = new XenForo_Phrase('h_dm_moderator_log_delete_roll',
				array(
					'roll_id' => (array_key_exists('roll_id', $elements) ? $elements['roll_id'] : new XenForo_Phrase('h_dm_unknown_roll_id') )
				)
			);
			$entry['content_title'] = new XenForo_Phrase('h_dm_roll_in_thread_x', 
				array(
					'title' => $entry['content_title'],
					'comment' => (isset($elements['comment'])?$elements['comment']:'')
				)
			);
		}
		return $entry;
	}
}