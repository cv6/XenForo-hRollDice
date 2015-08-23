<?php

/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_DataWriter_Dice extends XenForo_DataWriter {

	protected $_existingDataErrorPhrase = 'Hoffi_DiceNotFound';

	protected function _getFields()
	{
		return array(
				'xf_hoffi_dm_dice' => array(
						'tag' => array(
								'type' => self::TYPE_STRING,
								'required' => true,
								'maxLength' => 10,
								'requiredError' => 'h_dm_please_enter_tag_using_alphanumeric',
								'verification' => array('Hoffi_DM_Helpers_Dice', 'validateTag')
						),
						'title' => array(
								'type' => self::TYPE_STRING,
								'required' => true,
								'maxLength' => 50,
								'requiredError' => 'please_enter_valid_title'
						),
						'image' => array('type' => self::TYPE_STRING, 'maxLength' => 255,
								'default' => ''
						),
						'active' => array('type' => self::TYPE_UINT, 'default' => 1),
						'sides' => array('type' => self::TYPE_UINT, 'required' => true, 'requiredError' => 'hoffi_dice_error_sides'),
						'values' => array('type' => self::TYPE_STRING, 'default' => 'no'),
				)
		);
	}

	protected function _getExistingData($data)
	{
		if (!$tag = $this->_getExistingPrimaryKey($data, 'tag'))
		{
			return false;
		}

		return array('xf_hoffi_dm_dice' => $this->getModelFromCache('Hoffi_DM_Model_Dice')->getDieByTag($tag));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'tag = ' . $this->_db->quote($this->getExisting('tag'));
	}

	public function save()
	{
		$return = parent::save();
		return $return;
	}

}
