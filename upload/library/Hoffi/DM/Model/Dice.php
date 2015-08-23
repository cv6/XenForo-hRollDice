<?php

/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_Model_Dice extends XenForo_Model {

	/**
	 * Gets the specified Dice Wireset
	 *
	 * @param string $id
	 *
	 * @return array|false
	 */
	public function getDieByTag($tag)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_hoffi_dm_dice
			WHERE tag = ?
		', $tag);
	}
	/**
	 * Fetches all Dice Wiresets from the Database
	 * @param bool $only_active set this true, to get only active Dice
	 * @return false|array
	 */
	public function getAllDice($only_active = false)
	{
		return $this->fetchAllKeyed('
			SELECT *
			FROM xf_hoffi_dm_dice ' .
			($only_active?' WHERE active = 1 ':'').
			' ORDER BY tag
		', 'tag');
	}

	public function getDiceBulkXml(array $code)
	{
		$document = new DOMDocument('1.0', 'utf-8');
		$document->formatOutput = true;

		$rootNode = $document->createElement('dice');

		foreach($code as $tag => $die)
		{
			$dieNode = $document->createElement('die');
			$rootNode->appendChild($dieNode);
			$dieNode->appendChild($document->createElement('tag', $die['tag']));
			$dieNode->appendChild($document->createElement('title', $die['title']));
			$dieNode->appendChild($document->createElement('image', $die['image']));
			$dieNode->appendChild($document->createElement('active', $die['active']));
			$dieNode->appendChild($document->createElement('x_sided_die', $die['sides']));
			$dieNode->appendChild($document->createElement('values', $die['values']));
		}
		$document->appendChild($rootNode);
		return $document;
	}

	public function getDieXml(array $code)
	{
		$document = new DOMDocument('1.0', 'utf-8');
		$document->formatOutput = true;

		$rootNode = $document->createElement('die');
		$document->appendChild($rootNode);

		$rootNode->appendChild($document->createElement('tag', $code['tag']));
		$rootNode->appendChild($document->createElement('title', $code['title']));
		$rootNode->appendChild($document->createElement('image', $code['image']));
		$rootNode->appendChild($document->createElement('active', $code['active']));
		$rootNode->appendChild($document->createElement('x_sided_die', $code['sides']));
		$rootNode->appendChild($document->createElement('values', $code['values']));
		return $document;
	}

	public function getDiceForAdminQuickSearch($searchText)
	{
		$quotedString = XenForo_Db::quoteLike($searchText, 'lr', $this->_getDb());

		return $this->fetchAllKeyed('
			SELECT * FROM xf_hoffi_dm_dice
			WHERE title LIKE ' . $quotedString . ' OR tag LIKE ' . $quotedString . '
			ORDER BY tag
		', 'tag');
	}

}