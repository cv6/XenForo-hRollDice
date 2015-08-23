<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_ViewAdmin_Dice extends XenForo_ViewAdmin_Base
{
	public function renderXml()
	{
		XenForo_Application::autoload('Zend_Debug');
		if (array_key_exists('die', $this->_params))
		{
			$title = str_replace(' ', '-', utf8_romanize(utf8_deaccent($this->_params['die']['tag'])));
		}
		else
		{
			$title = '_all__';
		}
		$this->setDownloadFileName('die_' . $title . '.xml');
		return $this->_params['xml']->saveXml();
	}
}