<?php

/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_Install
{

	public static function install($installedAddon)
	{
		$db = XenForo_Application::get('db');

		$version = is_array($installedAddon) ? $installedAddon['version_id'] : 0;

		self::_log(var_export($installedAddon, true));

		if ($version == 0)
		{
			self::_log('Installing');
			self::_log('Creating tables...');
			foreach (self::getTables() AS $table => $tableSql)
			{
				self::_log($table);
				try {
					$db->query($tableSql);
				}
				catch (Zend_Db_Exception $e) {
					self::_logError($e);
				}
			}

			self::_log('Altering tables...');
			foreach (self::getAlters() AS $table => $alterSql)
			{
				self::_log($table);
				try {
					$db->query($alterSql);
				}
				catch (Zend_Db_Exception $e) {
					self::_logError($e);
				}
			}

			self::_log('Inserting into tables...');
			foreach (self::getData() AS $table => $dataSql)
			{
				self::_log($table);
				try {
					$db->query($dataSql);
				}
				catch (Zend_Db_Exception $e) {
					self::_logError($e);
				}
			}
		}
		else
		{
			self::update($version);
		}
	}

	public static function getTables()
	{

		$tables = array(
				'xf_hoffi_dm_dice' =>
					"CREATE TABLE `xf_hoffi_dm_dice` (
						`tag` VARCHAR(10) NOT NULL COLLATE 'latin1_swedish_ci',
						`title` VARCHAR(50) NOT NULL COLLATE 'latin1_swedish_ci',
						`sides` SMALLINT(3) NOT NULL,
						`values` VARCHAR(200) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
						`active` TINYINT(1) NOT NULL DEFAULT '0',
						`image` VARCHAR(50) NULL DEFAULT NULL,
						PRIMARY KEY (`tag`)
					)
					COLLATE='utf8_general_ci'
					ENGINE=InnoDB;",
				
				'xf_hoffi_dm_rules' => 
					"CREATE TABLE `xf_hoffi_dm_rules` (
							`rule` VARCHAR(10) NOT NULL,
							`title` VARCHAR(50) NOT NULL COLLATE 'latin1_swedish_ci',
							`active` TINYINT(1) NOT NULL,
							`php_callback_class` VARCHAR(100) NOT NULL COLLATE 'latin1_swedish_ci',
							`php_callback_method` VARCHAR(50) NOT NULL COLLATE 'latin1_swedish_ci',
							`optionlist` VARCHAR(250) NOT NULL COLLATE 'latin1_swedish_ci',
							PRIMARY KEY (`rule`)
						)
						COLLATE='utf8_general_ci'
						ENGINE=InnoDB;",
				
				'xf_hoffi_dm_wireset' => 
					"CREATE TABLE `xf_hoffi_dm_wireset` (
							`tag` VARCHAR(10) NOT NULL,
							`title` VARCHAR(100) NOT NULL,
							`description` MEDIUMTEXT NOT NULL,
							`build_sum` ENUM('everytime','highest_three','winning','no','explode') NOT NULL DEFAULT 'no',
							`max_dice` SMALLINT(3) NOT NULL,
							`min_dice` SMALLINT(3) NOT NULL,
							`allow_comment` INT(1) NOT NULL,
							`explode` ENUM('yes','double','no','once') NOT NULL,
							`active` INT(1) NOT NULL,
							`count_dice` SMALLINT(3) NOT NULL DEFAULT '1',
							`sort_dice` TINYINT(3) NOT NULL DEFAULT '0',
							`rule` VARCHAR(10) NULL DEFAULT NULL,
							`dietypes` VARCHAR(100) NOT NULL,
							`image` VARCHAR(255) NULL DEFAULT NULL,
							`display_order` INT(11) NOT NULL DEFAULT '1',
							PRIMARY KEY (`tag`),
							UNIQUE INDEX `title` (`title`),
							INDEX `FK_rules` (`rule`),
							CONSTRAINT `FK_rules` FOREIGN KEY (`rule`) REFERENCES `xf_hoffi_dm_rules` (`rule`) ON UPDATE CASCADE ON DELETE SET NULL
						)
						COLLATE='utf8_general_ci'
						ENGINE=InnoDB;",
				
				'xf_hoffi_dm_rolls' => 
					"CREATE TABLE `xf_hoffi_dm_rolls` (
						`roll_id` INT(10) NOT NULL AUTO_INCREMENT,
						`hash` VARCHAR(35) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
						`post_id` INT(10) NOT NULL,
						`user_id` INT(10) NOT NULL,
						`thread_id` INT(10) NOT NULL,
						`comment` VARCHAR(200) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
						`data` TEXT NOT NULL COLLATE 'latin1_swedish_ci',
						`wins` INT(1) NULL DEFAULT NULL,
						`result_sum` INT(10) NULL DEFAULT NULL,
						`wireset` VARCHAR(50) NULL DEFAULT NULL,
						`roll_state` ENUM('visible','moderated','deleted') NOT NULL DEFAULT 'visible',
						`roll_time` INT(10) NOT NULL,
						`options` TEXT NULL,
						PRIMARY KEY (`roll_id`),
						INDEX `FK_xf_hoffi_dm_rolls_xf_hoffi_dm_wireset` (`wireset`),
						CONSTRAINT `FK_xf_hoffi_dm_rolls_xf_hoffi_dm_wireset` FOREIGN KEY (`wireset`) REFERENCES `xf_hoffi_dm_wireset` (`tag`) ON UPDATE NO ACTION ON DELETE SET NULL
					)
					COLLATE='utf8_general_ci'
					ENGINE=InnoDB;"
		);

		return $tables;
	}

	public static function getData()
	{
		$data = array(
				'xf_admin_search_type' => "
					INSERT IGNORE INTO xf_admin_search_type
						(search_type, handler_class, display_order)
					VALUES
						('h_dm_dice', 'Hoffi_DM_AdminSearchHandler_Dice', 3600),
						('h_dm_wiresets', 'Hoffi_DM_AdminSearchHandler_Wiresets', 3601),
						('h_dm_rules', 'Hoffi_DM_AdminSearchHandler_Rules', 3602)",
				'xf_content_type' => "
				INSERT IGNORE INTO xf_content_type
					(content_type, addon_id, fields)
				VALUES
					('dice_roll', 'hRollDice', '');",
				'xf_content_type_field' => "
				INSERT IGNORE INTO xf_content_type_field
					(content_type, field_name, field_value)
				VALUES
					('dice_roll', 'alert_handler_class', 'Hoffi_DM_AlertHandler_Roll'),
					('dice_roll', 'moderator_log_handler_class', 'Hoffi_DM_ModeratorLogHandler_Roll'),
					('dice_roll', 'stats_handler_class', 'Hoffi_DM_StatsHandler_Dice');"
		);

		return $data;
	}

	public static function getRemoveData()
	{
		$data = array(
				'xf_admin_search_type' => "
				DELETE FROM xf_admin_search_type
					WHERE search_type IN ('h_dm_dice','h_dm_wiresets','h_dm_rules')",
				'xf_content_type_field' => "
				DELETE FROM xf_content_type_field
					WHERE content_type = 'dice_roll'",
				'xf_content_type' => "
				DELETE FROM xf_content_type
					WHERE content_type = 'dice_roll'"
		);

		return $data;
	}

	public static function getAlters()
	{
		$alters = array();

		$alters['xf_user'] = "
			ALTER TABLE xf_user ADD diceroll_count INT UNSIGNED NOT NULL DEFAULT 0,
				ADD INDEX diceroll_count (diceroll_count)
		";
		$alters['xf_forum'] = "
		ALTER TABLE `xf_forum`
			ADD COLUMN `h_dm_allowdiceroll` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `default_sort_direction`,
			ADD COLUMN `h_dm_wiresets` TEXT NULL,
			ADD COLUMN `h_dm_dicecount` TINYINT(3) UNSIGNED NOT NULL DEFAULT '5' AFTER `h_dm_allowdiceroll`;
		";
		$alters['xf_thread'] = "
			ALTER TABLE `xf_thread`
				ADD COLUMN `h_dice_rolls` INT UNSIGNED NOT NULL DEFAULT '0'";

		return $alters;
	}

	public static function getAltersUninstall()
	{
		$alters = array();

		$alters['xf_user'] = "ALTER TABLE `xf_user` DROP COLUMN `diceroll_count`;";
		$alters['xf_forum'] = "ALTER TABLE `xf_forum`
			DROP COLUMN `h_dm_allowdiceroll`,
			DROP COLUMN `h_dm_dicecount`,
			DROP COLUMN `h_dm_wiresets`;";

		return $alters;
	}

	public static function uninstall()
	{
		$db = XenForo_Application::get('db');
		self::_log("Uninstall");
		foreach (array_reverse(self::getTables()) AS $tableName => $tableSql)
		{
			self::_log($tableName);
			try {
				$db->query("DROP TABLE IF EXISTS `$tableName`");
			}
			catch (Zend_Db_Exception $e) {
				self::_logError($e);
			}
		}


		foreach (self::getAltersUninstall() AS $tableName => $alterSql)
		{
			self::_log($tableName);
			try {
				$db->query($alterSql);
			}
			catch (Zend_Db_Exception $e) {
				self::_logError($e);
			}
		}

		foreach (self::getRemoveData() AS $tableName => $alterSql)
		{
			self::_log($tableName);
			try {
				$db->query($alterSql);
			}
			catch (Zend_Db_Exception $e) {
				self::_logError($e);
			}
		}
	}

	public static function update($version)
	{
		// Actually no update...#
			self::_log('Updating from ' . $version);
			$api = Hoffi_API_Client::getInstance('hRollDice', 'hKeyDice', $version);
			$api->checkKey();
	}

	private static function _logError($e)
	{
		$text = "Error: " . $e->getCode() . " - " . $e->getMessage();
		self::_log($text . "\n" . $e->getTraceAsString(), 'hDiceError');
		self::_log($text);
	}

	private static function _log($message, $log = 'hDiceInstall')
	{
		XenForo_Helper_File::log($log, $message);
	}

}
