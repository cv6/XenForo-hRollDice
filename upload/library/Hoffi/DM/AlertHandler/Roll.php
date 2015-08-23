<?php

/**
 * Handles alerts of dice rolls.
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_AlertHandler_Roll extends XenForo_AlertHandler_DiscussionMessage
{
	/**
	 * @var XenForo_Model_Post
	 */
	protected $_postModel = null;
	protected $_rollModel = null;

	/**
	 * Gets the post content.
	 * @see XenForo_AlertHandler_Abstract::getContentByIds()
	 */
	public function getContentByIds(array $contentIds, $model, $userId, array $viewingUser)
	{
		$postModel = $this->_getPostModel();

		$posts = $postModel->getPostsByIds($contentIds, array(
			'join' => XenForo_Model_Post::FETCH_THREAD | XenForo_Model_Post::FETCH_FORUM,
			'permissionCombinationId' => $viewingUser['permission_combination_id']
		));
		return $postModel->unserializePermissionsInList($posts, 'node_permission_cache');
	}

	/**
	 * Determines if the post is viewable.
	 * @see XenForo_AlertHandler_Abstract::canViewAlert()
	 */
	public function canViewAlert(array $alert, $content, array $viewingUser)
	{
		return $this->_getPostModel()->canViewPostAndContainer(
			$content, $content, $content, NULL, $content['permissions'], $viewingUser
		);
	}

	/**
	 * @return XenForo_Model_Post
	 */
	protected function _getPostModel()
	{
		if (!$this->_postModel)
		{
			$this->_postModel = XenForo_Model::create('XenForo_Model_Post');
		}

		return $this->_postModel;
	}
	
	/**
	 * @return Hoffi_DM_Model_Roll
	 */
	protected function _getRollModel()
	{
		if (!$this->_rollModel)
		{
			$this->_rollModel = XenForo_Model::create('Hoffi_DM_Model_Roll');
		}

		return $this->_rollModel;
	}
}