<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_ControllerAdmin_DiceManager extends XenForo_ControllerAdmin_Abstract
{

	// You can add here several tags, that can't be deleted or disabled.
	protected $_protectedTags = array('dice');

	protected function _preDispatch($action)
	{
		$this->assertAdminPermission('dicemanager');
	}

	public function actionIndex()
	{
		$addonSocial = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('Waindigo_SocialGroups');
		$addonDice = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('hRollDiceSocial');
		$showHint = false;
		if (!empty($addonSocial) and empty($addonDice))
		{
			// You have Social Groups, but not the social dice, tell the user abbout this.
			$showHint = true;
		}
			
		$wiresetList = $this->_getWiresetModel()->getAllWiresets();
		$diceList = $this->_getAllDice();
		$rulesList = $this->_getAllRules();

		$wireset = array(
			'installed' => count($wiresetList),
			'protected' => 0,
			'enabled' => 0,
			'disabled' => 0
		);
		$dice = array(
			'installed' => count($diceList),
			'enabled' => 0,
			'disabled' => 0
		);
		$rules = array(
			'installed' => count($rulesList),
			'protected' => 0,
			'enabled' => 0,
			'disabled' => 0
		);
		foreach($wiresetList as $tag => $ws)
		{
			if ($this->_checkProtectedTag($tag,false))
			{
				$wireset['protected']++;
			}
			switch ($ws['active'])
			{
				case '1': $wireset['enabled']++; break;
				case '0': $wireset['disabled']++; break;
			}
		}
		foreach($diceList as $tag => $die)
		{
			switch ($die['active'])
			{
				case '1': $dice['enabled']++; break;
				case '0': $dice['disabled']++; break;
			}
		}
		foreach($rulesList as $tag => $r)
		{
			switch ($r['active'])
			{
				case '1': $rules['enabled']++; break;
				case '0': $rules['disabled']++; break;
			}
		}
		
		return $this->responseView('', 'hoffi_dm_main', array(
			'wiresets' => $wireset,
			'dice' => $dice,
			'rules' => $rules,
			'showHint' => $showHint,
 		));
	}

	protected function _getWireset($id)
	{
		$info = $this->_getWiresetModel()->getWiresetById($id);
		if (!$info)
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('hoffi_WiresetNotFound'), 404));
		}
		if ($info['dietypes']=='__all')
		{
			$info['dietypes'] = array_keys($this->_getAllDice());
			$info['all_dietypes'] = 1;
		}
		else
		{
			$info['all_dietypes'] = 0;
			$info['dietypes'] = explode(",",$info['dietypes']);
		}
		$info['protected'] = $this->_checkProtectedTag($info['tag'], false);
		return $info;
	}

	protected function _getWiresetModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Wireset');
	}

	protected function _getAllDice()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Dice')->getAllDice(true);
	}

	protected function _getAllRules()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Rules')->getAllRules(true);
	}

	protected function _checkProtectedTag($tag,$redirect=true)
	{
		$isProtected = in_array($tag, $this->_protectedTags);
		if (!$redirect)
		{
			return $isProtected;
		}
		if($isProtected)
		{
				throw $this->responseException(
					$this->responseError(new XenForo_Phrase('h_dm_protected_wireset'))
				);
		}
	}
	
}