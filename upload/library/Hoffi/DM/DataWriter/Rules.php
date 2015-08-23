<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_DataWriter_Rules extends XenForo_DataWriter
{
	protected $_existingDataErrorPhrase = 'h_dm_RulesNotFound';

	protected function _getFields()
	{
		return array(
			'xf_hoffi_dm_rules' => array(
				'rule'			=> array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 20, 'requiredError' => 'h_dm_please_enter_tag_using_alphanumeric', 'verification' => array('Hoffi_DM_Helpers_Dice', 'validateTag') ),
				'title'			=> array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 50, 'requiredError' => 'please_enter_valid_title' ),
				'active'		=> array('type' => self::TYPE_UINT, 'default' => 1),
				'php_callback_class'	=> array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 200, 'requiredError' => 'please_enter_valid_callback_class' ),
				'php_callback_method'	=> array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 50, 'requiredError' => 'please_enter_valid_callback_method' ),
				'optionlist'			=> array('type' => self::TYPE_STRING, 'maxLength' => 250 ),
			)
		);
	}

	/**
	* Gets the actual existing data out of data that was passed in. See parent for explanation.
	*
	* @param mixed
	*
	* @return array|false
	*/
	protected function _getExistingData($data)
	{
		if (!$id = $this->_getExistingPrimaryKey($data, 'rule'))
		{
			return false;
		}

		return array('xf_hoffi_dm_rules' => $this->_getRuleModel()->getRuleById($id));
	}

	/**
	* Gets SQL condition to update the existing record.
	*
	* @return string
	*/
	protected function _getUpdateCondition($tableName)
	{
		return 'rule = ' . $this->_db->quote($this->getExisting('rule'));
	}


	/**
	 * @return XenForo_Model_ThreadPrefix
	 */
	protected function _getRuleModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Rules');
	}

	protected function _getWiresetModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Wireset');
	}

	/**
	 * Validates that the specified callback class and method are present and correct
	 *
	 * @param string $class
	 * @param string $method
	 *
	 * @return boolean
	 */
	protected function _validateCallback($class, $method)
	{
		if (!XenForo_Application::autoload($class) || !method_exists($class, $method))
		{
			$this->error(new XenForo_Phrase('please_enter_valid_callback_method'), 'php_callback_method');
			return false;
		}

		return true;
	}

	protected function _preSave()
	{
		$this->_validateCallback($this->get('php_callback_class'), $this->get('php_callback_method'));
	}
	
	// Deprecated in case of foreign keys
	protected function _preDelete()
	{
//		$tag = $this->get('rule');
//		$wiresets = $this->_getWiresetModel()->getWiresetsByRule($id);
//		if(!empty($wiresets))
//		{
//			$dw = XenForo_DataWriter::create('Hoffi_DataWriter_Wireset');
//			$dw->removeRule($tag);
//		}
	}


}