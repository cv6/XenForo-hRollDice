<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_ControllerAdmin_DiceManager_Forum extends XFCP_Hoffi_DM_ControllerAdmin_DiceManager_Forum {

	protected function _preDispatch($action)
	{
		$this->assertAdminPermission('dicemanager');
	}

	public function actionEdit()
	{
		$response = parent::actionEdit();
		$response->params['wiresets'] = $this->_getWiresetModel()->getAllWiresets();
		if (!array_key_exists('h_dm_wiresets', $response->params['forum']))
		{
			$response->params['forum']['h_dm_wiresets'] = '__all';
		}
		if ($response->params['forum']['h_dm_wiresets'] == '__all')
		{
			$response->params['forum']['h_dm_wiresets'] = array_keys($response->params['wiresets']);
			$response->params['forum']['all_wiresets'] = 1;
		}
		else
		{
			$response->params['forum']['all_wiresets'] = 0;
			$response->params['forum']['h_dm_wiresets'] = explode(",", $response->params['forum']['h_dm_wiresets']);
		}
		return $response;
	}

	public function actionSave()
	{
		$response = parent::actionSave();
		
		if ($response->redirectType == XenForo_ControllerResponse_Redirect::SUCCESS)
		{
			$writerData = $this->_input->filter(array(
					'node_id' => XenForo_Input::UINT,
					'h_dm_allowdiceroll' => XenForo_Input::INT,
					'h_dm_dicecount' => XenForo_Input::INT
			));

			$all = $this->_input->filterSingle('usable_wiresets', XenForo_Input::STRING);

			$wiresets = '__all';
			if ($all != 'all')
			{
				$wiresets = implode(",", $this->_input->filterSingle('wiresets', XenForo_Input::ARRAY_SIMPLE));
			}
			$writerData['h_dm_wiresets'] = $wiresets;

			$writer = $this->_getNodeDataWriter();

			if (empty($writerData['node_id']))
			{
				$db = XenForo_Application::get('db');
				$writerData['node_id'] = $db->fetchOne("
					SELECT node_id
					FROM xf_node
					ORDER BY node_id
					DESC
					LIMIT 1
				");
			}

			if ($writerData['node_id'])
			{
				$writer->setExistingData($writerData['node_id']);	
			}

			$writer->bulkSet($writerData);
			$writer->save();
		}
		
		XenForo_Helper_File::log('DM',"B:".$this->_input->filterSingle('saveandsetchilds', XenForo_Input::STRING));
		
		if ($this->_input->filterSingle('saveandsetchilds', XenForo_Input::STRING) AND $writer->isUpdate())
		{
			$parent_node_id = $writerData['node_id'];
			unset($writerData['node_id']);
			$children_forums = $this->_getNodeModel()->getChildNodesForNodeIds(array($parent_node_id));
			foreach($children_forums as $node_id => $node)
			{
				if ($node['node_type_id'] == "Forum")
				{
					$writer = $this->_getNodeDataWriter();
					$writer->setExistingData($node_id);
					$writer->bulkSet($writerData);
					$writer->save();
				}
			}
		}

		return $response;
	}

	protected function _getWiresetModel()
	{
		return $this->getModelFromCache('Hoffi_DM_Model_Wireset');
	}
	
		/**
	 * @return XenForo_Model_Node
	 */
	protected function _getNodeModel()
	{
		return $this->getModelFromCache('XenForo_Model_Node');
	}


}
