<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_ViewAdmin_Rules extends XenForo_ViewAdmin_Base
{
	public function renderXml()
	{
		XenForo_Application::autoload('Zend_Debug');
		if (array_key_exists('rule', $this->_params))
		{
			$title = str_replace(' ', '-', utf8_romanize(utf8_deaccent($this->_params['rule']['rule'])));
		}
		else
		{
			$title = '_all__';
		}
		$this->setDownloadFileName('rule_' . $title . '.xml');
		return $this->_params['xml']->saveXml();
	}
}