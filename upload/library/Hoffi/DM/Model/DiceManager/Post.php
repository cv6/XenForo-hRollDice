<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_Model_DiceManager_Post extends XFCP_Hoffi_DM_Model_DiceManager_Post {

	protected function _getRollModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Rolls');
	}

	protected function _getDiceModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Dice');
	}

	protected function _getWiresetModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Wireset');
	}

	protected function _getRuleModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Rules');
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

	public function preparePost(array $post, array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
	{
		$post['hasDice'] = (bool)$post['roll_id'];
		$post['canViewDice'] = $nodePermissions['can_see_dice_post'];
		$post['canDeleteDice'] = $nodePermissions['can_delete_diceroll_post'];
		if ($post['hasDice'])
		{
			$rolls = $this->_getRollModel()->fetchRollsForPost($post['post_id']);
			$allDice = XenForo_Application::getSimpleCacheData('hAllDice');
			// $allDice = array();
			if (empty($allDice))
			{
				$allDice = $this->_getDiceModel()->getAllDice();
				XenForo_Application::setSimpleCacheData('hAllDice', $allDice);
			}
			$post['dicelist'] = $allDice;
			foreach ($rolls as $roll_id => $roll)
			{
				if ($roll['wireset'] == NULL)
				{ 
					// This Part only makes a full backward compatibility to all my dice addons from vB in earlier times. It has no effect for actual rolls.
					if (XenForo_Application::get('options')->hEnableBackwardCompatibility)
					{
						$post['diceinfo'] = $this->_parseOldRolls($roll);
					}
					// If backwars is disabled, we save a lot of shit.
				}
				else if ($roll['roll_state']=='visible')
				{
					$options = unserialize($roll['options']);
					$optionsText = array();
					if (count($options)==0)
					{
						$options = false;
					}
					else if (is_array($options))
					{
						foreach($options as $phrase => $value)
						{
							$optionsText[$phrase] = Hoffi_DM_Helpers_Dice::getDieOptionPhrase($phrase);
						}
					}

					$post['diceInfo'][$roll_id] = array(
							'wireset' => $this->_getWiresetByTag($roll['wireset']),
							'comment' => $roll['comment'],
							'wins' => $roll['wins'],
							'dice' => unserialize($roll['data']),
							'options' => $options,
							'optionsText' => $optionsText,
							'sum' => $roll['result_sum']
					);
				} // /if
			}
		}
		return parent::preparePost($post, $thread, $forum, $nodePermissions, $viewingUser);;
	}
	
	private function _parseOldRolls($roll)
	{
		$rollData = @unserialize($roll['data']);
		$diceinfo = array();
		$c=1;
		if (is_array($rollData) && array_key_exists('faces', $rollData) && array_key_exists('data', $rollData))
		{
			$diceinfo[$roll_id."-".$c] = array(
					'wireset' => $this->_getWiresetByTag('dice'),
					'comment' => $roll['comment'],
					'wins' => false,
					'dice' => array('' => array(
							'tag' => '',
							'title' => '',
							'sides' => $rollData['faces'],
							'active' => 1,
							'max' => 99,
							'count' => count($rollData['data']),
							'winners' => array(),
							'explode' => array(),
							'result' => $rollData['data']
						),
					),
					'options' => false,
					'optionsText' => '',
					'sum' => 0
			);
		}
		else
		{
			foreach($rollData as $oldRoll)
			{
				$newRoll = array(
							'tag' => '',
							'title' => '',
							'sides' => $oldRoll['faces'],
							'active' => 1,
							'max' => 99,
							'count' => $oldRoll['dices'],
							'winners' => array(),
							'explode' => array(),
							'result' => $oldRoll['data']
						);
				$diceinfo[$roll_id."-".$c] = array(
						'wireset' => $this->_getWiresetByTag('dice'),
						'comment' => $roll['comment'],
						'wins' => false,
						'dice' => array('' => $newRoll),
						'options' => false,
						'optionsText' => '',
						'sum' => 0
				);
			}
		}
		return $diceinfo;
	}
	
	public function mergePosts(array $posts, array $threads, $targetPostId, $newMessage, $options = array())
	{
		if (!isset($posts[$targetPostId]))
		{
			return false;
		}

		$targetPost = $posts[$targetPostId];
		$rollPosts = $posts;
		unset($rollPosts[$targetPostId]);

		if (!$rollPosts)
		{
			return false;
		}
	
		// Because Mergung delets Pots, and so the rolls we must move the rolls before the Post merging process.

		$allPosts = array_keys($rollPosts);
		$targetForum = $this->_getForumModel()->getForumByThreadId($targetPost['thread_id']);
		$allRolls = $this->_getRollModel()->getRollsByPostIds($allPosts);


		if ($this->_getForumModel()->canRollDiceInForum($targetForum) AND !empty($allRolls))
		{
			// Ok, now we really can move all the rolls
			$rollDw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Roll');
			$rollDw->assignRollsNewThreadAndPost($targetPost['thread_id'],$targetPost['post_id'],array_keys($allRolls));			
			$rollDw->assignRollsNewUser($targetPost['user_id'],array_keys($allRolls));
		}

		$merge = parent::mergePosts($posts, $threads, $targetPostId, $newMessage, $options);
	
		if ($merge === true)
		{
			// Okay, Posts have merged. Commit.
		}
		else
		{
			// Merde, Rollback!
		}
	
		return $merge;
	}

	protected function _getWiresetByTag($tag)
	{
		$wireset = XenForo_Application::getSimpleCacheData('hWireset_'.$tag);
		if (empty($wireset))
		{
			$wireset = $this->_getWiresetModel()->getWiresetByTag($tag);
			XenForo_Application::setSimpleCacheData('hWireset_'.$tag, $wireset);
		}
		return $wireset;
	}

	/**
	 * @return XenForo_Model_Forum
	 */
	protected function _getForumModel()
	{
		return $this->getModelFromCache('XenForo_Model_Forum');
	}

}
