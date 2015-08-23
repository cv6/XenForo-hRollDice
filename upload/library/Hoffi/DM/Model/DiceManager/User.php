<?php

class Hoffi_DM_Model_DiceManager_User extends XFCP_Hoffi_DM_Model_DiceManager_User {

	public function __construct()
	{
		parent::__construct();
		self::$userContentChanges['xf_hoffi_dm_rolls'] = array(array('user_id'));
	}
					
	protected function _getModelRoll()
	{
		return $this->getModelFromCache('Hoffi_Model_Rolls');
	}

	protected function _getModelDice()
	{
		return $this->getModelFromCache('Hoffi_Model_Dice');
	}

	protected function _getModelWiresets()
	{
		return $this->getModelFromCache('Hoffi_Model_Wireset');
	}

	protected function _getModelRules()
	{
		return $this->getModelFromCache('Hoffi_Model_Rules');
	}

	public function prepareUserConditions(array $conditions, array &$fetchOptions)
	{
		$result = parent::prepareUserConditions($conditions, $fetchOptions);

		if (!empty($conditions['diceroll_count']) && is_array($conditions['diceroll_count']))
		{
			$result .= ' AND (' . $this->getCutOffCondition("user.diceroll_count", $conditions['diceroll_count']) . ')';
		}

		return $result;
	}

	public function prepareUserOrderOptions(array &$fetchOptions, $defaultOrderSql = '')
	{
		$choices = array(
			'diceroll_count' => 'user.diceroll_count'
		);
		$order = $this->getOrderByClause($choices, $fetchOptions);
		if ($order)
		{
			return $order;
		}

		return parent::prepareUserOrderOptions($fetchOptions, $defaultOrderSql);
	}

	protected function _getWiresetByTag($tag)
	{
		$wiresets = XenForo_Application::getSimpleCacheData('hAllWiresets');
		if (empty($wiresets))
		{
			$wiresets = $this->_getModelWiresets()->getAllWiresets();
			XenForo_Application::getSimpleCacheData('hAllWiresets', $wiresets);
		}
		return (array_key_exists($tag, $wiresets) ? $wiresets[$tag] : false);
	}

}
