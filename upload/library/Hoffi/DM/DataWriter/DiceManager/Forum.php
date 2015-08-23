<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_DataWriter_DiceManager_Forum extends XFCP_Hoffi_DM_DataWriter_DiceManager_Forum {

	protected function _getFields()
	{
		$fields = parent::_getFields();
		$fields['xf_forum']['h_dm_allowdiceroll'] = array('type' => self::TYPE_UINT_FORCED, 'default' => 0);
		$fields['xf_forum']['h_dm_dicecount'] = array('type' => self::TYPE_UINT_FORCED, 'default' => 5);
		$fields['xf_forum']['h_dm_wiresets'] = array('type' => self::TYPE_STRING, 'default' => '');
		return $fields;
	}

	protected function _getDefaultOptions()
	{
		$defaultOptions = parent::_getDefaultOptions();
		$defaultOptions['h_dm_allowdiceroll'] = '0';
		$defaultOptions['h_dm_dicecount'] = '5';
		$defaultOptions['h_dm_wiresets'] = '';
		return $defaultOptions;
	}

	public function getInsertedForumId()
	{
		return $this->_db->lastInsertId();
	}

}
