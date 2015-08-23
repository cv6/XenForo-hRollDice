<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_DataWriter_Wireset extends XenForo_DataWriter {

	protected $_existingDataErrorPhrase = 'Hoffi_WiresetNotFound';

	protected function _getFields()
	{
		return array(
				'xf_hoffi_dm_wireset' => array(
						'tag' => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 10,
								'requiredError' => 'h_dm_please_enter_tag_using_alphanumeric', 'verification' => array('Hoffi_DM_Helpers_Dice', 'validateTag'),),
						'title' => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 50,
								'requiredError' => 'please_enter_valid_title'
						),
						'dietypes' => array('type' => self::TYPE_STRING, 'required' => false, 'maxLength' => 100,
								'requiredError' => 'h_dm_error_nodietype', 'default' => '__all'
						),
						'description' => array('type' => self::TYPE_STRING, 'maxLength' => 255,
								'default' => ''
						),
						'image' => array('type' => self::TYPE_STRING, 'maxLength' => 255,
								'default' => ''
						),
						//'all_dietypes' => array('type' => self::TYPE_UINT, 'default' => 0),
						'active' => array('type' => self::TYPE_UINT, 'default' => 1),
						'allow_comment' => array('type' => self::TYPE_UINT, 'default' => 0),
						'display_order' => array('type' => self::TYPE_UINT, 'default' => 1),
						'count_dice' => array('type' => self::TYPE_UINT, 'default' => 1),
						'sort_dice' => array('type' => self::TYPE_UINT, 'default' => 0),
						'build_sum' => array('type' => self::TYPE_STRING, 'default' => 'no'),
						'min_dice' => array('type' => self::TYPE_UINT, 'required' => true, 'requiredError' => 'h_dm_error_mindice'),
						'max_dice' => array('type' => self::TYPE_UINT, 'required' => true, 'requiredError' => 'h_dm_error_maxdice'),
						'explode' => array('type' => self::TYPE_STRING, 'default' => 'no'),
						'rule' => array('type' => self::TYPE_STRING, 'default' => NULL)
				)
		);
	}

	protected function _getExistingData($data)
	{
		if (!$id = $this->_getExistingPrimaryKey($data, 'tag'))
		{
			return false;
		}

		return array('xf_hoffi_dm_wireset' => $this->_getWiresetModel()->getWiresetById($id));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'tag = ' . $this->_db->quote($this->getExisting('tag'));
	}

	protected function _getWiresetModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Wireset');
	}
	
	public function removeRule($rule_id)
	{
		$this->_db->query('
					UPDATE xf_hoffi_dm_wireset SET
						rule_id = 0
					WHERE rule = ?
				', array($rule_id));
		return true;		
	}
	
}