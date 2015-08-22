<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_ControllerPublic_Dice extends XenForo_ControllerPublic_Abstract
{
	
	public function actionIndex()
	{
		$params = array();
		return $this->responseView('Hoffi_ViewPublic', 'hoffi_roll_dice', $params);
	}

	public function actionRollThread()
	{
		$forum_id = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
		$forum_name = $this->_input->filterSingle('node_name', XenForo_Input::STRING);

		$ftpHelper = $this->getHelper('ForumThreadPost');
        $forum = $ftpHelper->assertForumValidAndViewable($forum_id > 0 ? $forum_id : $forum_name);
		return $this->_doRoll($forum);
	}
	
	public function actionRollPost()
	{
		$thread_id = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
        $ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($thread_id);
		return $this->_doRoll($forum);
	}
	
	private function _doRoll($forum, $thread_id = 0)
	{
		if (!$this->_getForumModel()->canRollDiceInForum($forum, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		$hash = XenForo_Helper_Cookie::getCookie('dice_hash');
		if (!$hash)
		{
			$hash = md5('my'.time().'dice$now'.XenForo_Visitor::getUserId().'#'.rand(100,2000));
			XenForo_Helper_Cookie::setCookie('dice_hash', $hash, 360);
		}

		$wiresets = $forum['h_dm_wiresets'];

		if (empty($wiresets) OR $wiresets == "__all")
		{
			// Load all
			$wiresetList = $this->_getWiresetModel()->getAllWiresets(true);
		}
		else
		{
			// load specified
			$wiresetList = $this->_getWiresetModel()->getWiresetsByTag($wiresets);
		}

		$dice = $this->_getDiceModel()->getAllDice(true);
		$rules = $this->_getRuleModel()->getAllRules(true);

		$params = array(
			'wiresets' => array(),
			'dice' => $dice,
			'firstWireset' => null,
			'thread_id' => $thread_id
		);
		foreach($wiresetList as $tag => $ws)
		{
			if ($ws['active'] == 1)
			{
				$params['wiresets'][$tag] = $ws;
				$params['wiresets'][$tag]['dietypes'] = array();
				if (empty($params['firstWireset']))
				{
					$params['firstWireset'] = $tag;
				}
				if ($ws['dietypes'] == '__all')
				{
					$d = array_keys($this->_getDiceModel()->getAllDice(true));
				}
				else
				{
					$d = explode(",",$ws['dietypes']);
				}
				foreach($d as $die)
				{
					if (!empty($die) and !empty($tag))
					{
						$params['wiresets'][$tag]['dietypes'][$die] = $dice[$die];
					}
				}
				if (!empty($params['wiresets'][$tag]['rule']) OR $params['wiresets'][$tag]['rule'] == "noRule")
				{
					$params['wiresets'][$tag]['rules'] = $rules[$params['wiresets'][$tag]['rule']];
					if ($params['wiresets'][$tag]['rules']['optionlist'] != "")
					{
						$params['wiresets'][$tag]['rules']['options'] = array_flip(explode("\n",$params['wiresets'][$tag]['rules']['optionlist']));
						foreach($params['wiresets'][$tag]['rules']['options'] as $phrase => $text)
						{
							$params['wiresets'][$tag]['rules']['options'][$phrase] = Hoffi_DM_Helpers_Dice::getDieOptionPhrase($phrase);
						}
					}
				}
			}
		}

		return $this->responseView('Hoffi_ViewPublic_Roll', 'hoffi_roll_dice', $params);
	}

	public function actionRollNow()
	{
		$this->_assertPostOnly();

        $hash = XenForo_Helper_Cookie::getCookie('dice_hash');
		$wireset_tag = $this->_input->filterSingle('dicetag', XenForo_Input::STRING);
		$thread_id = $this->_input->filterSingle('thread_id', XenForo_Input::INT);
		$comment = $this->_input->filterSingle('roll_comment', XenForo_Input::ARRAY_SIMPLE);

        if ($thread_id)
        {
            $ftpHelper = $this->getHelper('ForumThreadPost');
            list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($thread_id);
            if (!$this->_getForumModel()->canRollDiceInForum($forum, $errorPhraseKey))
            {
                throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
            }
        }
        
		//var_export($params);
		$roller = new Hoffi_DM_Dice_Roller(
						$this->getModelFromCache('Hoffi_DM_Model_Wireset'),
						$this->getModelFromCache('Hoffi_DM_Model_Dice'),
						$this->getModelFromCache('Hoffi_DM_Model_Rules'),
						$hash,
						$thread_id
					);

		if ($roller && $hash)
		{
			$roller->setWiresetByTag($wireset_tag);
			$dice = $this->_input->filterSingle('dice', XenForo_Input::ARRAY_SIMPLE);

			if (array_key_exists($wireset_tag, $dice) && is_array($dice[$wireset_tag]))
			{
				foreach ($dice[$wireset_tag] as $tag => $count)
				{
					$roller->setDieCount($tag, $count);
				}
			}
			$option = $this->_input->filterSingle('option', XenForo_Input::ARRAY_SIMPLE);
			if (array_key_exists($wireset_tag, $option) && is_array($option[$wireset_tag]))
			{
				foreach($option[$wireset_tag] as $title => $value)
				{
					$roller->setOption($title,$value);
				}
			}

			$rollCount = $roller->rollAll();
			if ($rollCount > 0)
			{
				if (array_key_exists($wireset_tag, $comment))
				{
					$roller->setComment($comment[$wireset_tag]);
				}
				$roller->checkRule();
				$roller->sortRoll();
				$roller->buildSum();
				$roller->prepareSave();
				$feedback = $roller->getFeedback();

				return $this->responseRedirect(
					XenForo_ControllerResponse_Redirect::SUCCESS,
					XenForo_Link::buildPublicLink('dice/roll', false, array()),
					null,
					array('feedback' => $feedback)
				);
			}
			else
			{
				return $this->responseError(new XenForo_Phrase('h_dm_no_roll'));
			}
		}
		else
		{
			return $this->responseError(new XenForo_Phrase('h_dm_you_cant_roll'));

		}
	}

	public function actionDelete()
	{
        $roll_id = $this->_input->filterSingle('roll_id', XenForo_Input::INT);
        $ftpHelper = $this->getHelper('ForumThreadPost');
        $postModel = $this->_getPostModel();
		$rollModel = $this->_getRollModel();
		$roll = $rollModel->getRollByRoll($roll_id);
		if (empty($roll))
		{
			throw $this->getErrorOrNoPermissionResponseException('');
		}  
        
        list($post, $thread, $forum) = $ftpHelper->assertThreadValidAndViewable($roll['post_id']);
		if (!$postModel->canDeleteDiceRoll($post, $thread, $forum, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}  

		$params = array('roll_id' => $roll_id);
		return $this->responseView('Hoffi_DM_ViewPublic_Roll', 'hoffi_delete_roll', $params);
	}

	public function actionDeleteNow()
	{
        $this->_assertPostOnly();
        $roll_id = $this->_input->filterSingle('roll_id', XenForo_Input::INT);
        $ftpHelper = $this->getHelper('ForumThreadPost');
        $postModel = $this->_getPostModel();
		$rollModel = $this->_getRollModel();
		$roll = $rollModel->getRollByRoll($roll_id);
		if (empty($roll))
		{
			throw $this->getErrorOrNoPermissionResponseException('');
		}  
        
        list($post, $thread, $forum) = $ftpHelper->assertThreadValidAndViewable($roll['post_id']);
		if (!$postModel->canDeleteDiceRoll($post, $thread, $forum, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}        
		
		$dataDw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Roll');
        $options = array(
            'roll_id' => $roll_id
        );
        $options['comment'] = (array_key_exists('comment', $roll)?$roll['comment']:'');
        $dataDw->softDeleteRoll($roll_id);
        $roll['username'] = $post['username'];
        XenForo_Model_Log::logModeratorAction(
            'dice_roll', $roll, 'delete_roll', $options
        );
		
		$authorAlert = $this->_input->filterSingle('send_author_alert', XenForo_Input::BOOLEAN);
		$authorAlertReason = $this->_input->filterSingle('author_alert_reason', XenForo_Input::STRING);

		if ($authorAlert) 
		{
			$thread = $this->_getThreadModel()->getThreadById($roll['thread_id']);
			$extra = array(
				'title' => $thread['title'],
				'link' => XenForo_Link::buildPublicLink('posts', $roll),
				'threadLink' => XenForo_Link::buildPublicLink('threads', $thread),
				'reason' => $authorAlertReason,
				'roll' => $roll
			);
			
			XenForo_Model_Alert::alert(
				$roll['user_id'],
				0, '',
				'user', $roll['user_id'],
				'roll_delete',
				$extra
			);
		}
		
		return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('thread', $roll['thread_id'], array()),
				null,
				array('feedback' => 'deleted','roll_id' => $roll_id)
		);
	}
	
	private function _getForumModel()
	{
		return $this->getModelFromCache('XenForo_Model_Forum');
	}
	
	private function _getWiresetModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Wireset');
	}
	
	private function _getRuleModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Rules');
	}
	
	private function _getDiceModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Dice');
	}

	private function _getRollModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Rolls');
	}

	private function _getThreadModel()
	{
		return $this->getModelFromCache('XenForo_Model_Thread');
	}
    
	private function _getPostModel()
	{
		return $this->getModelFromCache('XenForo_Model_Post');
	}
}