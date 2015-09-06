<?php

/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_EventListener_Dice
{

	public static function listenController($class, &$extend)
	{
		switch ($class)
		{
			case 'XenForo_ControllerPublic_Help':
				$extend[] = 'Hoffi_DM_ControllerPublic_Help_Dice';
				break;
			case 'XenForo_ControllerAdmin_Forum':
				$extend[] = 'Hoffi_DM_ControllerAdmin_DiceManager_Forum';
				break;
			case 'XenForo_ControllerAdmin_Stats':
				$extend[] = 'Hoffi_DM_ControllerAdmin_DiceManager_Stats';
				break;
			case 'XenForo_ControllerPublic_InlineMod_Post':
				$extend[] = 'Hoffi_DM_ControllerPublic_InlineMod_Post';
				break;
			case 'XenForo_ControllerPublic_Thread':
				$extend[] = 'Hoffi_DM_ControllerPublic_Thread';
				break;
			case 'XenForo_ControllerPublic_Forum':
				$extend[] = 'Hoffi_DM_ControllerPublic_Forum';
				break;
		}
	}

	public static function listenModel($class, &$extend)
	{
		switch ($class)
		{
			case 'XenForo_Model_Forum':
				$extend[] = 'Hoffi_DM_Model_DiceManager_Forum';
				break;
			case 'XenForo_Model_Thread':
				$extend[] = 'Hoffi_DM_Model_DiceManager_Thread';
				break;
			case 'XenForo_Model_User':
				$extend[] = 'Hoffi_DM_Model_DiceManager_User';
				break;
			case 'XenForo_Model_Post':
				$extend[] = 'Hoffi_DM_Model_DiceManager_Post';
				break;
			case 'XenForo_Model_Stats':
				$extend[] = 'Hoffi_DM_Model_DiceManager_Stats';
				break;
		}
	}

	public static function listenDataWriter($class, &$extend)
	{
		switch ($class)
		{
			case 'XenForo_DataWriter_User':
				$extend[] = 'Hoffi_DM_DataWriter_DiceManager_User';
				break;
			case 'XenForo_DataWriter_Forum':
				$extend[] = 'Hoffi_DM_DataWriter_DiceManager_Forum';
				break;
			case 'XenForo_DataWriter_Discussion_Thread':
				$extend[] = 'Hoffi_DM_DataWriter_DiceManager_Thread';
				break;
			case 'XenForo_DataWriter_DiscussionMessage_Post':
				$extend[] = 'Hoffi_DM_DataWriter_DiceManager_DiscussionMessage';
				break;
		}
	}

	public static function helper(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
		//Get the static variable $helperCallbacks and add a new item in the array.
		XenForo_Template_Helper_Core::$helperCallbacks += array(
			'explode' => array('Hoffi_DM_Helpers_Dice', 'helperExplode'),
			'wins' => array('Hoffi_DM_Helpers_Dice', 'helperWins')
		);
	}

	public static function templateCreate(&$templateName, array &$params, XenForo_Template_Abstract $template)
	{
		switch ($templateName)
		{
			case 'application_splash':
				$params['canManageDice'] = XenForo_Visitor::getInstance()->hasAdminPermission('dicemanager');
				break;
		}
	}
	
	public static function userCriteria($rule, array $data, array $user, &$returnValue)
	{
		switch ($rule)
		{
			case 'diceroll_count':
				if (isset($user['diceroll_count']) && $user['diceroll_count'] >= $data['rolls'])
				{
					$returnValue = true;
				}
			break;
		}
	}

}
