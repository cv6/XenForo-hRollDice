<?php

class Hoffi_DM_Dice_BbCode
{
	public static function parseDice($tag, array $rendererStates, XenForo_BbCode_Formatter_Base $formatter)
	{
		if (!empty($tag['tag']))
		{
			$ModelDice = XenForo_Model::create('Hoffi_DM_Model_Dice');
			$die = $ModelDice->getDieByTag($tag['option']);
			if (empty($die))
			{
				return 'invalid';
			}
			$hash = md5('bbc'.time().$tag['tag'].rand(100,200));
			XenForo_Helper_Cookie::setCookie('dice_hash', $hash, 360);
			$roller = new Hoffi_Dice_Roller(
							XenForo_Model::create('Hoffi_Model_Wireset'),
							$ModelDice, 
							XenForo_Model::create('Hoffi_Model_Rules'), 
							$hash
			);
			$roller->setWiresetByTag('dice');
			$roller->setDieCount($tag['option'], $tag['children'][0]);
			$roller->rollAll();
			$roller->checkRule();
			$roller->prepareSave();
			return "Rolled";
		}
		
		
	}
	
	
}