<?php

/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_ControllerAdmin_Dice extends XenForo_ControllerAdmin_Abstract
{

	protected function _preDispatch($action)
	{
		$this->assertAdminPermission('dicemanager');
	}

	public function actionIndex()
	{
		return $this->responseView('', 'hoffi_die_list', array(
			'dice' => $this->_getDiceModel()->getAllDice(),
			'canusedice' => XenForo_Visitor::getInstance()->hasAdminPermission('dicemanager')
		));
	}

	protected function _getDicemanagerAddEditResponse($type = 'add', array $data = array())
	{
		if ($type != 'edit')
		{
			$type = 'add';
			$data = array();
		}
		return $this->responseView('', 'hoffi_die_edit', array(
			'die' => $data,
			'type' => $type
		));
	}

	public function actionAdd()
	{
		return $this->_getDicemanagerAddEditResponse();
	}

	public function actionEdit()
	{
		$tag = $this->_input->filterSingle('tag', XenForo_Input::STRING);
		$data = $this->_getDie($tag);

		return $this->_getDicemanagerAddEditResponse('edit', $data);
	}

	public function actionEnable()
	{
		$this->_enableDisable($this->_input->filterSingle('tag', XenForo_Input::STRING));

		return $this->responseRedirect(
										XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('dm-dice')
		);
	}

	public function actionDisable()
	{
		$this->_enableDisable($this->_input->filterSingle('tag', XenForo_Input::STRING));

		return $this->responseRedirect(
										XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('dm-dice')
		);
	}

	public function actionImportConfirm()
	{
		return $this->responseView('', 'hoffi_die_import');
	}

	public function actionSave()
	{
		$this->_assertPostOnly();

		$tag = $this->_input->filterSingle('tag', XenForo_Input::STRING);

		$dwInput = $this->_input->filter(array(
				'tag' => XenForo_Input::STRING,
				'title' => XenForo_Input::STRING,
				'image' => XenForo_Input::STRING,
				'active' => XenForo_Input::UINT,
				'sides' => XenForo_Input::UINT,
				'values' => XenForo_Input::STRING,
		));

		$dw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Dice');
		if ($this->_getDiceModel()->getDieByTag($tag))
		{
			$dw->setExistingData($tag);
		}
		$dw->set('tag', $tag);
		$dw->bulkSet($dwInput);
		$dw->save();
		$this->_refreshCache();

		return $this->responseRedirect(	XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('dm-dice') );
	}

	public function actionDelete()
	{
		if ($this->isConfirmedPost())
		{
			return $this->_deleteData(
											'Hoffi_DM_DataWriter_Dice', 'tag', XenForo_Link::buildAdminLink('dm-dice')
			);
		}
		else
		{
			$tag = $this->_input->filterSingle('tag', XenForo_Input::STRING);
			$die = $this->_getDie($tag);

			$viewParams = array(
					'die' => $die
			);
			return $this->responseView('', 'hoffi_die_delete', $viewParams);
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
			case 'die': // Single
				$this->_doImport($file);
				break;
			case 'dice': // Bulk
				foreach ($file->die as $die)
				{
					$this->_doImport($die, true);
				}
				break;
			default:
				// Wrong XML
				throw new XenForo_Exception(new XenForo_Phrase('h_dm_invalid_die_xml'), true);
				break;
		}

		return $this->responseRedirect(
										XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('dm-dice')
		);
	}

	protected function _doImport($file, $ignore = false)
	{
		$dw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Dice');

		if (!is_array($this->_getDiceModel()->getDieByTag($file->tag)))
		{
			$dw->bulkSet(array(
					'tag' => $file->tag,
					'title' => $file->title,
					'image' => $file->image,
					'sides' => $file->x_sided_die,
					'values' => $file->values,
					'active' => $file->active
			));

			$dw->save();
		}
		else if (!$ignore)
		{
			throw new XenForo_Exception(new XenForo_Phrase('h_dm_import_exists'), true);
		}
		$this->_refreshCache();
	}

	public function actionBulk()
	{
		$diceList = $this->_getDiceModel()->getAllDice();

		$this->_routeMatch->setResponseType('xml');

		$viewParams = array(
				'dicelist' => $diceList,
				'xml' => $this->_getDiceModel()->getDiceBulkXml($diceList)
		);

		return $this->responseView('Hoffi_DM_ViewAdmin_Dice', '', $viewParams);
	}

	public function actionExport()
	{
		$tag = $this->_input->filterSingle('tag', XenForo_Input::STRING);
		$die = $this->_getDie($tag);

		$this->_routeMatch->setResponseType('xml');

		$viewParams = array(
				'die' => $die,
				'xml' => $this->_getDiceModel()->getDieXml($die)
		);

		return $this->responseView('Hoffi_DM_ViewAdmin_Dice', '', $viewParams);
	}

	protected function _enableDisable($tag)
	{
		$die = $this->_getDie($tag);
		$dw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Dice');
		$dw->setExistingData($tag);

		$dw->set('active', ($die['active'] ? 0 : 1));

		$dw->save();

		$this->_refreshCache();

		return $this->responseRedirect(
										XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('dm-dice')
		);
	}

	protected function _getDie($tag)
	{
		$info = $this->_getDiceModel()->getDieByTag($tag);
		if (!$info)
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('hoffi_DieNotFound'), 404));
		}

		return $info;
	}

	protected function _getDiceModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Dice');
	}

	protected function _refreshCache()
	{
		$allDice = $this->_getDiceModel()->getAllDice();
		XenForo_Application::setSimpleCacheData('hAllDice', $allDice);
	}

}
