<?php

/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_Model_Rules extends XenForo_Model
{

	/**
	 * Gets the specified Dice Wireset
	 *
	 * @param string $id
	 *
	 * @return array|false
	 */
	public function getRuleById($id)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_hoffi_dm_rules
			WHERE rule = ?
		', $id);
	}

	/**
	 * Fetches all Dice Wiresets from the Database
	 * @param bool $only_active set this true, to get only active Dice
	 * @return false|array
	 */
	public function getAllRules($only_active = false)
	{
		$ret = $this->fetchAllKeyed('
			SELECT *
			FROM xf_hoffi_dm_rules ' .
						($only_active ? ' WHERE active = 1 ' : '') .
						' ORDER BY title
		', 'rule');
		return $ret;
	}

	public function getRuleHelp()
	{
		// Test
		$array = array();
		$array = array('tag' => 'dice', 'theOptions' => 'Whow');
		return $array;
	}

	public function getRuleXml(array $code)
	{
		$document = new DOMDocument('1.0', 'utf-8');
		$document->formatOutput = true;

		$rootNode = $document->createElement('rule');
		$document->appendChild($rootNode);

		$rootNode->appendChild($document->createElement('tag', $code['rule']));
		$rootNode->appendChild($document->createElement('title', $code['title']));
		$rootNode->appendChild($document->createElement('active', $code['active']));
		$rootNode->appendChild($document->createElement('callback_classname', $code['php_callback_class']));
		$rootNode->appendChild($document->createElement('callback_method', $code['php_callback_method']));
		$rootNode->appendChild($document->createElement('options', $code['optionlist']));

		return $document;
	}

	public function getRuleBulkXml(array $code)
	{
		$document = new DOMDocument('1.0', 'utf-8');
		$document->formatOutput = true;

		$rootNode = $document->createElement('rules');

		foreach ($code as $tag => $rule)
		{
			$ruleNode = $document->createElement('rule');
			$rootNode->appendChild($ruleNode);
			$ruleNode->appendChild($document->createElement('tag', $rule['rule']));
			$ruleNode->appendChild($document->createElement('title', $rule['title']));
			$ruleNode->appendChild($document->createElement('active', $rule['active']));
			$ruleNode->appendChild($document->createElement('callback_classname', $rule['php_callback_class']));
			$ruleNode->appendChild($document->createElement('callback_method', $rule['php_callback_method']));
			$ruleNode->appendChild($document->createElement('options', $rule['optionlist']));
		}
		$document->appendChild($rootNode);
		return $document;
	}

	public function getRulesForAdminQuickSearch($searchText)
	{
		$quotedString = XenForo_Db::quoteLike($searchText, 'lr', $this->_getDb());

		return $this->fetchAllKeyed('
			SELECT * FROM xf_hoffi_dm_rules
			WHERE title LIKE ' . $quotedString . '
			ORDER BY title
		', 'rule');
	}

}
