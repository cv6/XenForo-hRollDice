<?php

/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_Model_Wireset extends XenForo_Model
{

	/**
	 * Gets the specified Dice Wireset
	 *
	 * @param string $id
	 *
	 * @return array|false
	 */
	public function getWiresetById($id)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_hoffi_dm_wireset
			WHERE tag = ?
		', $id);
	}

	/*
	 * duplicate for logic reason
	 * @see getWiresetById
	 */

	public function getWiresetByTag($tag)
	{
		return $this->getWiresetById($tag);
	}


	/**
	 * Gets the specified Dice Wiresets specified by on or more tags
	 * $tag can be an array or a comma seperated list
	 *
	 * @param mixed $tags
	 *
	 * @return array
	 */
	public function getWiresetsByTag($tags, $only_active = true)
	{
		if (!is_array($tags))
		{
			$tags = explode(",",$tags);
		}
		return $this->fetchAllKeyed('
			SELECT *
				FROM xf_hoffi_dm_wireset
				WHERE tag IN ('.$this->_getDb()->quote($tags).') ' .
						($only_active ? ' AND active = 1 ' : '') .'
				ORDER BY display_order
		', 'tag');
	}

	/**
	 * Gets the specified Dice Wireset
	 *
	 * @param string $title
	 *
	 * @return array|false
	 */
	public function getWiresetByTitle($title)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_hoffi_dm_wireset
			WHERE title = ?
		', $title);
	}

	/**
	 * Fetches all Dice Wiresets from the Database
	 * @return false|array
	 */
	public function getAllWiresets($only_active = false)
	{
		return $this->fetchAllKeyed('
			SELECT *
			FROM xf_hoffi_dm_wireset ' .
										($only_active ? ' WHERE active = 1 ' : '') .
										' ORDER BY display_order
		', 'tag');
	}

	public function getWiresetsByRule($rule)
	{
		return $this->fetchAllKeyed('
			SELECT *
				FROM xf_hoffi_dm_wireset
				WHERE rule = ?
				ORDER BY display_order', 'tag', array($rule) );
	}

	public function getWiresetHelp()
	{
		// Test
		$array = array();
		$array = array('tag' => 'dice', 'theOptions' => 'Whow');
		return $array;
	}

	public function getWiresetXml(array $wireset)
	{
		$document = new DOMDocument('1.0', 'utf-8');
		$document->formatOutput = true;

		$rootNode = $document->createElement('wireset');
		$dietypes_node = $document->createElement('dietypes');
		foreach ((array) $wireset['dietypes'] as $die)
		{
			$dietypes_node->appendChild($document->createElement('die', $die));
		}

		$document->appendChild($rootNode);

		$rootNode->appendChild($document->createElement('tag', $wireset['tag']));
		$rootNode->appendChild($document->createElement('title', $wireset['title']));
		$rootNode->appendChild($document->createElement('description', $wireset['description']));
		$rootNode->appendChild($document->createElement('image', $wireset['image']));
		$rootNode->appendChild($document->createElement('active', $wireset['active']));
		$rootNode->appendChild($document->createElement('display_order', $wireset['display_order']));
		$rootNode->appendChild($document->createElement('count_dice', $wireset['count_dice']));
		$rootNode->appendChild($document->createElement('sort_dice', $wireset['sort_dice']));
		$rootNode->appendChild($document->createElement('calculate', $wireset['build_sum']));
		$rootNode->appendChild($document->createElement('min', $wireset['min_dice']));
		$rootNode->appendChild($document->createElement('max', $wireset['max_dice']));
		$rootNode->appendChild($document->createElement('explode', $wireset['explode']));
		$rootNode->appendChild($dietypes_node);
		$rootNode->appendChild($document->createElement('rule', $wireset['rule']));
		$rootNode->appendChild($document->createElement('comments', $wireset['allow_comment']));

		return $document;
	}

	public function getWiresetBulkXml(array $code)
	{
		$document = new DOMDocument('1.0', 'utf-8');
		$document->formatOutput = true;

		$rootNode = $document->createElement('wiresetlist');

		// Now, it's a bulk
		foreach ($code as $tag => $wireset)
		{
			$wsNode = $document->createElement('wireset');
			$dietypes_node = $document->createElement('dietypes');
			foreach ((array) $wireset['dietypes'] as $die)
			{
				$dietypes_node->appendChild($document->createElement('die', $die));
			}

			$rootNode->appendChild($wsNode);

			$wsNode->appendChild($document->createElement('tag', $wireset['tag']));
			$wsNode->appendChild($document->createElement('title', $wireset['title']));
			$wsNode->appendChild($document->createElement('description', $wireset['description']));
			$wsNode->appendChild($document->createElement('image', $wireset['image']));
			$wsNode->appendChild($document->createElement('active', $wireset['active']));
			$wsNode->appendChild($document->createElement('display_order', $wireset['display_order']));
			$wsNode->appendChild($document->createElement('count_dice', $wireset['count_dice']));
			$wsNode->appendChild($document->createElement('sort_dice', $wireset['sort_dice']));
			$wsNode->appendChild($document->createElement('calculate', $wireset['build_sum']));
			$wsNode->appendChild($document->createElement('min', $wireset['min_dice']));
			$wsNode->appendChild($document->createElement('max', $wireset['max_dice']));
			$wsNode->appendChild($document->createElement('explode', $wireset['explode']));
			$wsNode->appendChild($dietypes_node);
			$wsNode->appendChild($document->createElement('rule', $wireset['rule']));
			$wsNode->appendChild($document->createElement('comments', $wireset['allow_comment']));
		}
		$document->appendChild($rootNode);
		return $document;
	}

	public function getWiresetForAdminQuickSearch($searchText)
	{
		$quotedString = XenForo_Db::quoteLike($searchText, 'lr', $this->_getDb());

		return $this->fetchAllKeyed('
			SELECT * FROM xf_hoffi_dm_wireset
			WHERE title LIKE ' . $quotedString . ' OR tag LIKE ' . $quotedString . '
			ORDER BY display_order
		', 'tag');
	}

}
