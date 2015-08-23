<?php

/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_ControllerAdmin_Rules extends XenForo_ControllerAdmin_Abstract
{

	// You can add here several tags, that can't be deleted or disabled.
	protected $_protectedTags = array('norule');

	protected function _preDispatch($action)
	{
		$this->assertAdminPermission('dicemanager');
	}

	public function actionIndex()
	{
		$rules = $this->_getModelRules()->getAllRules();
		foreach ($rules as $tag => $rule)
		{
			$rules[$tag]['protected'] = (int) $this->_checkProtectedTag($tag, false);
		}
		return $this->responseView('', 'hoffi_rule_list',
										array(
								'rules' => $rules,
								'canusedice' => XenForo_Visitor::getInstance()->hasAdminPermission('dicemanager')
		));
	}

	protected function _getDicemanagerAddEditResponse(array $rule)
	{
		return $this->responseView('', 'hoffi_rule_edit', array(
								'rule' => $rule
		));
	}

	public function actionAdd()
	{
		return $this->_getDicemanagerAddEditResponse(array());
	}

	public function actionEdit()
	{
		$id = $this->_input->filterSingle('rule', XenForo_Input::STRING);
		$rule = $this->_getRule($id);

		return $this->_getDicemanagerAddEditResponse($rule);
	}

	public function actionEnable()
	{
		$id = $this->_input->filterSingle('rule', XenForo_Input::STRING);
		$this->_enableDisable($id);

		return $this->responseRedirect(
										XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('dm-rules')
		);
	}

	public function actionDisable()
	{
		$tag = $this->_input->filterSingle('rule', XenForo_Input::STRING);
		$this->_checkProtectedTag($tag);
		$this->_enableDisable($tag);

		return $this->responseRedirect(
										XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('dm-rules')
		);
	}

	public function actionImportConfirm()
	{
		return $this->responseView('', 'hoffi_rule_import');
	}

	public function actionSave()
	{
		$this->_assertPostOnly();

		$tag = $this->_input->filterSingle('rule', XenForo_Input::STRING);
		$protected = $this->_checkProtectedTag($tag, false);

		$dwInput = $this->_input->filter(array(
				'rule' => XenForo_Input::STRING,
				'title' => XenForo_Input::STRING,
				'php_callback_class' => XenForo_Input::STRING,
				'php_callback_method' => XenForo_Input::STRING,
				'optionlist' => XenForo_Input::STRING,
				'active' => XenForo_Input::UINT,
		));
		if ($protected)
		{
			// Special Protected handling
			// Do not change tag. everytime active and in front.
			unset($dwInput['rule']);
			$dwInput['active'] = 1;
		}

		$dw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Rules');
		$original_rule = $this->_input->filterSingle('original_rule', XenForo_Input::STRING);
		if ($original_rule)
		{
			$dw->setExistingData($original_rule);
		}
		$dw->bulkSet($dwInput);
		$dw->save();

		$this->_refreshCache();
		
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('dm-rules')
		);

	}

	public function actionDelete()
	{
		$tag = $this->_input->filterSingle('rule', XenForo_Input::STRING);
		$this->_checkProtectedTag($tag);
		if ($this->isConfirmedPost())
		{

			return $this->_deleteData(
											'Hoffi_DM_DataWriter_Rules', 'rule', XenForo_Link::buildAdminLink('dm-rules')
			);
		}
		else
		{
			$rule = $this->_getRule($tag);
			$wiresets = $this->_getModelWiresets()->getWiresetsByRule($tag);
			$viewParams = array(
					'rule' => $rule,
					'wiresets' => $wiresets
			);
			return $this->responseView('', 'hoffi_rule_delete', $viewParams);
		}
	}

	public function actionImport()
	{
		$this->_assertPostOnly();

		$fileTransfer = new Zend_File_Transfer_Adapter_Http();
		if ($fileTransfer->isUploaded('upload_file'))
		{
			$fileInfo = $fileTransfer->getFileInfo('upload_file');
			$fileName = $fileInfo['upload_file']['tmp_name'];
		}
		else
		{
			$fileName = $this->_input->filterSingle('server_file', XenForo_Input::STRING);
		}

		if (!file_exists($fileName) || !is_readable($fileName))
		{
			throw new XenForo_Exception(new XenForo_Phrase('please_enter_valid_file_name_requested_file_not_read'), true);
		}

		$file = new SimpleXMLElement($fileName, null, true);
		switch ($file->getName())
		{
			case 'rule': // Single
				$this->_doImport($file);
				break;
			case 'rules': // Bulk
				foreach ($file->rule as $rule)
				{
					$this->_doImport($rule, true);
				}
				break;
			default:
				// Wrong XML
				throw new XenForo_Exception(new XenForo_Phrase('h_dm_invalid_rule_xml'), true);
				break;
		}

		return $this->responseRedirect(
										XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('dm-rules')
		);
	}

	protected function _doImport($file, $ignore = false)
	{
		$dw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Rules');
		if (!is_array($this->_getModelRules()->getRuleById($file->tag)))
		{
			$dw->bulkSet(array(
					'rule' => $file->tag,
					'title' => $file->title,
					'active' => $file->active,
					'php_callback_class' => $file->callback_classname,
					'php_callback_method' => $file->callback_method,
					'optionlist' => $file->options
			));

			$dw->save();

			$this->_refreshCache();

		}
		else if (!$ignore)
		{
			throw new XenForo_Exception(new XenForo_Phrase('h_dm_import_exists'), true);
		}
	}

	public function actionExport()
	{
		$tag = $this->_input->filterSingle('rule', XenForo_Input::STRING);
		$rule = $this->_getRule($tag);
		$this->_routeMatch->setResponseType('xml');

		$viewParams = array(
				'rule' => $rule,
				'xml' => $this->_getModelRules()->getRuleXml($rule)
		);

		return $this->responseView('Hoffi_DM_ViewAdmin_Rules', '', $viewParams);
	}

	public function actionBulk()
	{
		$rules = $this->_getModelRules()->getAllRules();
		$this->_routeMatch->setResponseType('xml');

		$viewParams = array(
				'rulelist' => $rules,
				'xml' => $this->_getModelRules()->getRuleBulkXml($rules)
		);

		return $this->responseView('Hoffi_DM_ViewAdmin_Rules', '', $viewParams);
	}

	protected function _enableDisable($id)
	{
		$rule = $this->_getRule($id);
		$dw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Rules');
		$dw->setExistingData($rule['rule']);

		$dw->set('active', ($rule['active'] ? 0 : 1));

		$dw->save();

		$this->_refreshCache();

		return $this->responseRedirect(
										XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('dm-rules')
		);
	}

	protected function _getRule($tag)
	{
		$info = $this->_getModelRules()->getRuleById($tag);
		if (!$info)
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('hoffi_RuleNotFound' . $tag . '-'), 404));
		}
		$info['protected'] = $this->_checkProtectedTag($info['rule'], false);
		return $info;
	}

	protected function _getModelRules()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Rules');
	}

	protected function _getModelWiresets()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Wireset');
	}
	
	protected function _checkProtectedTag($tag, $redirect = true)
	{
		$isProtected = in_array($tag, $this->_protectedTags);
		if (!$redirect)
		{
			return $isProtected;
		}
		if ($isProtected)
		{
			throw $this->responseException(
							$this->responseError(new XenForo_Phrase('h_dm_protected_rule'))
			);
		}
	}
	
	
	protected function _refreshCache()
	{
//		$allRules = $this->_getModelRules()->getAllRules();
//		XenForo_Application::setSimpleCacheData('hAllRules', $allRules);
	}
}
