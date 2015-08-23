<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_Model_DiceManager_Thread extends XFCP_Hoffi_DM_Model_DiceManager_Thread {

	/**
	 *
	 * @see XenForo_Model_Poll::__construct()
	 */
	public function __construct()
	{
		parent::__construct();
	}

	public function mergeThreads(array $threads, $targetThreadId, array $options = array())
	{
		$targetThread = parent::mergeThreads($threads, $targetThreadId, $options);
		$mergeFromThreadIds = array_keys($threads);
		
		if ($targetThread !== false)
		{
			// If in Target Forum cann rolls be done, reassign them.
			$targetForum = $this->_getForumModel()->getForumById($targetThread['node_id']);
			if ($this->_getForumModel()->canRollDiceInForum($targetForum, $errorPhraseKey))
			{
				$rollDw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Roll');
				$rollDw->assignRollsNewThread($targetThreadId, $mergeFromThreadIds);
			}
			else
			{
				// Too bad... but I need to delete this rolls, do I?
				
			}
			// merge Works, now merge rolls
		}
		return $targetThread;
	}
	
	public function prepareThread(array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
	{
		$thread = parent::prepareThread($thread, $forum, $nodePermissions, $viewingUser);
		$thread['thread_has_die_roll'] = $this->hasDiceRoll($thread);
		return $thread;
	}

	public function canRollDice(array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
	{

		if (empty($forum['allow_posting']))
		{
			$errorPhraseKey = 'you_may_not_perform_this_action_because_forum_does_not_allow_posting';
			return false;
		}

		$this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);
		return ($viewingUser['user_id'] && XenForo_Permission::hasContentPermission($nodePermissions, 'can_roll_dice_post'));
	}

	/**
	 * @return Hoffi_DM_Model_Roll
	 */
	protected function _getRollModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Rolls');
	}
	
	public function hasDiceRoll($thread)
	{
		if (empty($thread))
			return false;
		
		return false;
		$rolls = $this->_getRollModel()->getLastRollFromThread($thread['thread_id']);
		return (!empty($rolls));
	}
	

}
