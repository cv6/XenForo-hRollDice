<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_Model_Dicemanager_Stats extends XFCP_Hoffi_DM_Model_Dicemanager_Stats
{
	protected $_statsHandlerCache = array();

	protected $_statsTypes = array();

	protected $_statsTypeHandlerLookupMap = array();

	public function getStatsTypePhrasesDice(array $statsTypes)
	{
		$phrases = array();

		foreach ($this->_getStatsContentTypeHandlerNames() AS $statsType => $statsHandlerName)
		{
			$statsHandler = $this->_getStatsHandler($statsHandlerName);

			$phrases = array_merge($phrases, $statsHandler->getStatsTypes());
		}

		return $phrases;
	}

	public function filterGraphDataDates(array $plots, array $dateMap)
	{
		$dates = array();
		foreach ($dateMap AS $map)
		{
			$dates += $map;
		}
		ksort($dates);

		$dateIds = array_keys($dates);
		$dateIdMap = array_flip($dateIds);

		foreach ($plots AS &$plot)
		{
			foreach ($plot AS &$data)
			{
				$data[0] = $dateIdMap[$data[0]];
			}
		}

		foreach ($dateMap AS $type => $dates)
		{
			$new = array();
			foreach ($dates AS $k => $v)
			{
				$new[$dateIdMap[$k]] = $v;
			}
			$dateMap[$type] = $new;
		}

		return array(
			'plots' => $plots,
			'dateMap' => $dateMap
		);
	}

	/**
	 * Fetch all stats handler content types
	 *
	 * @return array
	 */
	protected function _getStatsContentTypeHandlerNames()
	{
		$classes = array();
		foreach ($this->getContentTypesWithField('stats_handler_class') AS $class)
		{
			if (class_exists($class))
			{
				$classes[] = $class;
			}
		}

		return $classes;
	}

	/**
	 * Fetch all stats types
	 *
	 * @return array
	 */
	public function getStatsTypes()
	{
		if (empty($this->_statsTypes))
		{
			$this->_statsTypes = array();

			foreach ($this->_getStatsContentTypeHandlerNames() AS $contentType => $statsHandlerName)
			{
				$this->_statsTypes[$contentType] = $this->_getStatsHandler($statsHandlerName)->getStatsTypes();
			}
		}

		return $this->_statsTypes;
	}

	/**
	 * Fetch an array allowing a stats type to be mapped back to its stats handler
	 *
	 * @return array
	 */
	public function getStatsTypeHandlerLookupMap()
	{
		if (empty($this->_statsTypeHandlerLookupMap))
		{
			$this->_statsTypeHandlerLookupMap = array();

			foreach ($this->_getStatsContentTypeHandlerNames() AS $contentType => $statsHandlerName)
			{
				foreach ($this->_getStatsHandler($statsHandlerName)->getStatsTypes() AS $statsType => $_null)
				{
					$this->_statsTypeHandlerLookupMap[$statsType] = $statsHandlerName;
				}
			}
		}

		return $this->_statsTypeHandlerLookupMap;
	}

	/**
	 * Fetch options for a list of stats types to be used with <xen:options source="{this}" />
	 *
	 * @param array $selected Selected options
	 *
	 * @return array
	 */
	public function getStatsTypeOptions(array $selected = array())
	{
		$statsTypeOptions = array();

		foreach ($this->getStatsTypes() AS $contentType => $statsTypes)
		{
			foreach ($statsTypes AS $statsType => $statsTypePhrase)
			{
				$statsTypeOptions[$contentType][] = array(
					'name' => "statsTypes[]",
					'value' => $statsType,
					'label' => $statsTypePhrase,
					'selected' => in_array($statsType, $selected)
				);
			}
		}

		return $statsTypeOptions;
	}

	/**
	 * Fetch a stats handler
	 *
	 * @param string $statsHandlerName
	 *
	 * @return XenForo_StatsHandler_Abstract
	 */
	protected function _getStatsHandler($statsHandlerName)
	{
		$statsHandlerName = XenForo_Application::resolveDynamicClass($statsHandlerName);
		if (!isset($this->_statsHandlerCache[$statsHandlerName]))
		{
			$this->_statsHandlerCache[$statsHandlerName] = new $statsHandlerName;
		}

		return $this->_statsHandlerCache[$statsHandlerName];
	}

	/**
	 * Deletes ALL data from the xf_stats_daily table. Use with care!
	 */
	public function deleteStats()
	{
		$this->_getDb()->delete('xf_stats_daily');
	}

	public function buildStatsData($start, $end)
	{
		$db = $this->_getDb();

		XenForo_Db::beginTransaction($db);

		foreach ($this->_getStatsContentTypeHandlerNames() AS $contentType => $handlerClassName)
		{
			$handlerClass = $this->_getStatsHandler($handlerClassName);

			$data = $handlerClass->getData($start, $end);

			foreach ($data AS $statsType => $records)
			{
				$statsType = $db->quote($statsType);

				foreach ($records AS $date => $counter)
				{
					$date = $db->quote($date);
					$counter = $db->quote($counter);

					$db->query("
						INSERT INTO xf_stats_daily
							(stats_date, stats_type, counter)
						VALUES
							($date, $statsType, $counter)
						ON DUPLICATE KEY UPDATE
							counter = $counter
					");
				}
			}
		}

		XenForo_Db::commit($db);
	}
}