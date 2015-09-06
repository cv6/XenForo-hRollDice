<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_DataWriter_DiceManager_User extends XFCP_Hoffi_DM_DataWriter_DiceManager_User
{

	protected function _getFields()
	{
		$fields = parent::_getFields();
		$fields['xf_thread']['diceroll_count'] = array('type' => self::TYPE_UINT_FORCED, 'default' => 0);
		return $fields;
	}

	protected function _getDefaultOptions()
	{
		$defaultOptions = parent::_getDefaultOptions();
		$defaultOptions['diceroll_count'] = '0';
		return $defaultOptions;
	}
	
	protected function _getRollModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Rolls');
	}
	
	public function rebuildCustomFields()
	{
		parent::rebuildCustomFields();
		$this->_getUserModel()->rebuildRollCounter($this->get('user_id'));
	}
}