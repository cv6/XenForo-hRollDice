<?php

class Hoffi_API_Client {

	private $url = 'http://xf.cv6.de/api/';
	// private $url = 'http://localhost/api/';
	private $addonid, $domain, $ip, $key;
	private $data;
	static $instance = array();

	public function __construct()
	{
		$this->domain = filter_input(INPUT_SERVER, 'SERVER_NAME');
		$this->ip = filter_input(INPUT_SERVER, 'SERVER_ADDR');
	}

	public static function getInstance($addOnId, $key, $version = 0)
	{
		$iKey = $addOnId."-".$key;
		if (!array_key_exists($iKey, self::$instance))
		{
			$i = new Hoffi_API_Client();
			$i->setAddOn($addOnId);
			$i->setKey($key);
			$i->setVersion($version);
			self::$instance[$iKey] = $i;

		}
		return self::$instance[$iKey];
	}

	public function setAddOn($addOnId)
	{
		$this->addonid = $addOnId;
	}

	public function setKey($key)
	{
		$this->key = $key;
	}

	public function setVersion($ver)
	{
		$this->version = $ver;
	}

	public function checkKey($store = true)
	{
		$keyName = $this->key;
		$keyValues = XenForo_Application::getOptions()->$keyName;
		if (!is_array($keyValues))
		{
			$keyValues = array('Key' => NULL);
		}
		if (!array_key_exists('Key',$keyValues) OR empty($keyValues['Key']))
		{
			$infos = $this->_fetchInfos();
			if (!empty($infos['key']) AND $store === true)
			{
				$this->_saveKey($infos['key']);
			}
			$key = $infos['key'];
		}
		else
		{
			$key = $keyValues['Key'];
		}
		return $key;
	}

	private function _saveKey($key)
	{
		$keyName = $this->key;
		$dw = XenForo_DataWriter::create('XenForo_DataWriter_Option', XenForo_DataWriter::ERROR_SILENT);
		$dw->setExistingData($this->key);
		$dw->set('option_value', $key);
		$dw->save();
		XenForo_Application::getOptions()->$keyName = $key;
	}

	private function _fetchInfos(array $params = array())
	{
		try {
			$connection = XenForo_Helper_Http::getClient($this->url . "index.php");
			$connection->setParameterPost('addon', $this->addonid);
			$connection->setParameterPost('domain', $this->domain);
			$connection->setParameterPost('ver', $this->version);
			$connection->setParameterPost('ip', $this->ip);
			foreach ($params as $key => $value)
			{
				$connection->setParameterPost($key, $value);
			}
			$json = $connection->request('POST');
			if (!$json || $json->getStatus() != 200)
			{
				return false;
			}
		}
		catch (Zend_Http_Client_Exception $e) {
			return false;
		}
		$this->data = json_decode(utf8_encode($json->getBody()), true);
		return $this->data;
	}

		// Used for Options
	  public static function optionsRenderKey(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
			$addOnID = $preparedOption['addon_id'];
			$editLink = $view->createTemplateObject('option_list_option_editlink', array(
					'preparedOption' => $preparedOption,
					'canEditOptionDefinition' => $canEdit
			));

			if (empty($preparedOption['option_value']))
			{
				$preparedOption['option_value'] = array(
						'getKey' => true,
						'Key' => ''
				);
			}
			else if (!empty($preparedOption['option_value']['Key']))
			{
				$preparedOption['option_value']['getKey'] = false;
			}

			return $view->createTemplateObject('option_template_hoffi_'.$addOnID, array(
				'fieldPrefix' => $fieldPrefix,
				'listedFieldName' => $fieldPrefix . '_listed[]',
				'preparedOption' => $preparedOption,
				'editLink' => $editLink,
				'KeyName' => $addOnID
			));

    }


	  public static function optionsVerifyKey(array &$values, XenForo_DataWriter $dw, $fieldName)
    {
				if (!empty($values['getKey']))
				{
					$addOnID = $values['getKey'];
					$addOnModel = XenForo_Model::create('XenForo_Model_AddOn');
					$addon = $addOnModel->getAddOnById($addOnID);
					$api = self::getInstance($addOnID, $fieldName, $addon['version_id']);
					$newKey = $api->checkKey(false);
					$values['Key'] = $newKey;
				}
				else
				{
					$values['getKey'] = false;
				}
			return true;
    }

}