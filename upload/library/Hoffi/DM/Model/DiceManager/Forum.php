<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_Model_DiceManager_Forum extends XFCP_Hoffi_DM_Model_DiceManager_Forum {

	/**
	 *
	 * @see XenForo_Model_Poll::__construct()
	 */
	public function __construct()
	{
		parent::__construct();
	}

		/**
	 * Determines if dice can be rolled in this forum. This does not check
	 * general thread posting permissions.
	 *
	 * @param array $forum Info about the forum posting in
	 * @param string $errorPhraseKey Returned phrase key for a specific error
	 * @param array|null $nodePermissions
	 * @param array|null $viewingUser
	 *
	 * @return boolean
	 */
	
	public function canRollDiceInForum(array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
	{
		$this->standardizeViewingUserReferenceForNode($forum['node_id'], $viewingUser, $nodePermissions);

		if (!$viewingUser['user_id'])
		{
			return false;
		}
		return XenForo_Permission::hasContentPermission($nodePermissions, 'can_roll_dice_post') && $forum['h_dm_allowdiceroll'];
	}
	
	public function preparePostJoinOptions(array $fetchOptions)
	{
		$parent = parent::preparePostJoinOptions($fetchOptions);

		$parent['selectFields'] .= ',
            xf_hoffi_dm_rolls.roll_id as roll_id';
		$parent['joinTables'] .= '
            LEFT JOIN xf_hoffi_dm_rolls ON (post.post_id = xf_hoffi_dm_rolls.post_id)';

		return $parent;
	}


}
