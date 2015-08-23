<?php

/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_ControllerAdmin_Wireset extends XenForo_ControllerAdmin_Abstract
{

	// You can add here several tags, that can't be deleted or disabled.
	protected $_protectedTags = array('dice');

	protected function _preDispatch($action)
	{
		$this->assertAdminPermission('dicemanager');
	}

	public function actionIndex()
	{
		$wiresetList = $this->_getWiresetModel()->getAllWiresets();
		foreach ($wiresetList as $tag => $wireset)
		{
			$wiresetList[$tag]['protected'] = (int) $this->_checkProtectedTag($tag, false);
		}

		return $this->responseView('', 'hoffi_wireset_list', array(
								'wiresetList' => $wiresetList
		));
	}

	protected function _getDicemanagerAddEditResponse(array $data)
	{
		return $this->responseView('', 'hoffi_wireset_edit',
										array(
								'wireset' => $data,
								'dice' => $this->_getAllDice(),
								'rules' => $this->_getAllRules()
		));
	}

	public function actionAdd()
	{
		$default = array(
				'tag' => '',
				'all_dietypes' => 1,
				'dietypes' => array(),
				'explode' => 'no',
				'build_sum' => 'no'
		);
		return $this->_getDicemanagerAddEditResponse($default);
	}

	public function actionEdit()
	{
		$tag = $this->_input->filterSingle('tag', XenForo_Input::STRING);
		$wireset = $this->_getWireset($tag);

		return $this->_getDicemanagerAddEditResponse($wireset);
	}

	public function actionToggle()
	{
		$tag = $this->_input->filterSingle('tag', XenForo_Input::STRING);
		$this->_checkProtectedTag($tag);
		$this->_enableDisable($tag);

		return $this->responseRedirect(
										XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('dm-wiresets')
		);
	}

	public function actionEnable()
	{
		$tag = $this->_input->filterSingle('tag', XenForo_Input::STRING);
		$this->_enableDisable($tag);

		return $this->responseRedirect(
										XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('dm-wiresets')
		);
	}

	public function actionDisable()
	{
		$tag = $this->_input->filterSingle('tag', XenForo_Input::STRING);
		$this->_checkProtectedTag($tag);

		$this->_enableDisable($tag);

		return $this->responseRedirect(
										XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('dm-wiresets')
		);
	}

	public function actionImportConfirm()
	{
		return $this->responseView('', 'hoffi_wireset_import');
	}

	public function actionSave()
	{
		$this->_assertPostOnly();

		$tag = $this->_input->filterSingle('tag', XenForo_Input::STRING);
		$all = $this->_input->filterSingle('usable_dietypes', XenForo_Input::STRING);

		$protected = $this->_checkProtectedTag($tag, false);

		$dietypes = '__all';
		if ($all != 'all')
		{
			$dietypes = implode(",", $this->_input->filterSingle('dietypes', XenForo_Input::ARRAY_SIMPLE));
		}

		$dwInput = $this->_input->filter(array(
				'tag' => XenForo_Input::STRING,
				'title' => XenForo_Input::STRING,
				'image' => XenForo_Input::STRING,
				'description' => XenForo_Input::STRING,
				'active' => XenForo_Input::UINT,
				'display_order' => XenForo_Input::UINT,
				'build_sum' => XenForo_Input::STRING,
				'sort_dice' => XenForo_Input::UINT,
				'count_dice' => XenForo_Input::UINT,
				'min_dice' => XenForo_Input::UINT,
				'max_dice' => XenForo_Input::UINT,
				'explode' => XenForo_Input::STRING,
				'rule' => XenForo_Input::STRING,
				'allow_comment' => XenForo_Input::UINT
		));
		if ($protected)
		{
			// Special Protected handling
			// Do not change tag. everytime active and in front.
			unset($dwInput['tag']);
			$dwInput['display_order'] = 0;
			$dwInput['active'] = 1;
		}
		if ($dwInput['rule'] == '0')
		{
			$dwInput['rule'] = NULL;
		}
		$dwInput['dietypes'] = $dietypes;
		// @todo: check, if image exists
		if ($dwInput['min_dice'] > $dwInput['max_dice'])
		{
			// Change the Values... min > max is weird.
			$t = $dwInput['min_dice'];
			$dwInput['min_dice'] = $dwInput['max_dice'];
			$dwInput['max_dice'] = $t;
			unset($t);
		}
		$dwInput['max_dice'] = min(XenForo_Application::get('options')->hOverallMaxDice, $dwInput['max_dice']);
		$dw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Wireset');
		if ($this->_getWiresetModel()->getWiresetById($tag))
		{
			$dw->setExistingData($tag);
		}
		$dw->bulkSet($dwInput);
		$dw->save();

		return $this->responseRedirect(
										XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('dm-wiresets')
		);
	}

	public function actionDelete()
	{
		$tag = $this->_input->filterSingle('tag', XenForo_Input::STRING);
		$this->_checkProtectedTag($tag);
		if ($this->isConfirmedPost())
		{
			return $this->_deleteData(
											'Hoffi_DM_DataWriter_Wireset', 'tag', XenForo_Link::buildAdminLink('dm-wiresets')
			);
		}
		else
		{
			$wireset = $this->_getWireset($tag);

			$viewParams = array(
					'wireset' => $wireset
			);
			return $this->responseView('', 'hoffi_wireset_delete', $viewParams);
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
			case 'wireset': // Single
				$this->_doImport($file);
				break;
			case 'wiresetlist': // Bulk
				foreach ($file->wireset as $wireset)
				{
					$this->_doImport($wireset, true);
				}
				break;
			default:
				// Wrong XML
				throw new XenForo_Exception(new XenForo_Phrase('h_dm_invalid_wireset_xml'), true);
				break;
		}


		return $this->responseRedirect(
										XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('dm-wiresets')
		);
	}

	private function _doImport($file, $ignore = false)
	{
		$dw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Wireset');

		$wsExists = is_array($this->_getWiresetModel()->getWiresetById($file->tag));

		if (!$wsExists)
		{

			$dietypes = "";
			foreach ($file->dietypes->children() as $die)
			{
				if (is_array($this->_getDiceModel()->getDieByTag($die)))
				{
					$dietypes .= $die . ",";
				}
			}
			$dietypes = trim($dietypes, ",");

			$dw->bulkSet(array(
					'tag' => $file->tag,
					'title' => $file->title,
					'description' => $file->description,
					'build_sum' => $file->calculate,
					'count_dice' => $file->count_dice,
					'sort_dice' => $file->sort_dice,
					'display_order' => $file->display_order,
					'min_dice' => $file->min,
					'max_dice' => $file->max,
					'explode' => $file->explode,
					'image' => $file->image,
					'dietypes' => $dietypes,
					//				'rule' => $file->rule,
					'active' => $file->active
			));

			$rule = $this->_getRuleModel()->getRuleById($file->rule);
			if (!empty($rule))
				$dw->set('rule', $rule['rule']);

			$dw->save();
		}
		else if (!$ignore)
		{
			throw new XenForo_Exception(new XenForo_Phrase('h_dm_import_exists'), true);
		}
	}

	public function actionBulk()
	{
		$wiresetList = $this->_getWiresetModel()->getAllWiresets();

		$this->_routeMatch->setResponseType('xml');

		$viewParams = array(
				'wiresetList' => $wiresetList,
				'xml' => $this->_getWiresetModel()->getWiresetBulkXml($wiresetList)
		);

		return $this->responseView('Hoffi_DM_ViewAdmin_Wireset', '', $viewParams);
	}

	public function actionExport($tag = null)
	{
		$tag = $this->_input->filterSingle('tag', XenForo_Input::STRING);

		$wireset = $this->_getWireset($tag);

		$this->_routeMatch->setResponseType('xml');

		$viewParams = array(
				'wireset' => $wireset,
				'xml' => $this->_getWiresetModel()->getWiresetXml($wireset)
		);

		return $this->responseView('Hoffi_DM_ViewAdmin_Wireset', '', $viewParams);
	}

	protected function _enableDisable($tag)
	{
		$wireset = $this->_getWireset($tag);
		$dw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Wireset');
		$dw->setExistingData($wireset['tag']);

		$dw->set('active', ($wireset['active'] ? 0 : 1));

		$dw->save();

		return $this->responseRedirect(
										XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('dm-wiresets')
		);
	}

	protected function _getWireset($id)
	{
		$info = $this->_getWiresetModel()->getWiresetById($id);
		if (!$info)
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('hoffi_WiresetNotFound'), 404));
		}
		if ($info['dietypes'] == '__all')
		{
			$info['dietypes'] = array_keys($this->_getAllDice());
			$info['all_dietypes'] = 1;
		}
		else
		{
			$info['all_dietypes'] = 0;
			$info['dietypes'] = explode(",", $info['dietypes']);
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
		return $this->_getDiceModel()->getAllDice(true);
	}

	protected function _getAllRules()
	{
		return $this->_getRuleModel()->getAllRules(true);
	}

	protected function _getRuleModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Rules');
	}

	protected function _getDiceModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Dice');
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
							$this->responseError(new XenForo_Phrase('h_dm_protected_wireset'))
			);
		}
	}
	
	
	protected function _refreshCache()
	{
		$allWS = $this->_getWiresetModel()->getAllWiresets();
		XenForo_Application::setSimpleCacheData('hAllWiresets', $allWS);
	}

}
