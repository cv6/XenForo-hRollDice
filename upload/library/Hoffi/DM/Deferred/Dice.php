<?php

class Hoffi_DM_Deferred_Dice extends XenForo_Deferred_Abstract
{
	public function execute(array $deferred, array $data, $targetRunTime, &$status)
	{
		$data = array_merge(array(
			'position' => 0,
			'batch' => 100
		), $data);
		$data['batch'] = max(1, $data['batch']);
		
		$dw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Roll', XenForo_DataWriter::ERROR_SILENT);

		if ($data['position'] == 0)
		{
			// Reset first
			$dw->resetThreadCounter();
			$dw->resetUserCounter();
		}

		/* @var $resourceModel XenResource_Model_Resource */
		$rollModel = XenForo_Model::create('Hoffi_DM_Model_Rolls');

		$rollIds = $rollModel->getRollsInRange($data['position'], $data['batch']);
		if (sizeof($rollIds) == 0)
		{
			return true;
		}
		
		foreach ($rollIds AS $rollId => $roll)
		{
			$data['position'] = $rollId;
			$dw->updateThreadDiceRolls($roll['thread_id'],1);
			$dw->updateUserDiceRolls($roll['user_id'],1);
		}

		$rbPhrase = new XenForo_Phrase('rebuilding');
		$typePhrase = new XenForo_Phrase('h_dm_rolls');
		$status = sprintf('%s... %s (%s)', $rbPhrase, $typePhrase, XenForo_Locale::numberFormat($data['position']));

		return $data;
	}

	public function canCancel()
	{
		return true;
	}
}