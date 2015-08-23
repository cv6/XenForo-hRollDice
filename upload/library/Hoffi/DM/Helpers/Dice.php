<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
 class Hoffi_DM_Helpers_Dice
 {
	 public static function helperExplode($index,$exploded)
	 {
		 $class = "";
		 if (in_array($index,(array)$exploded))	$class = "explode";
		 return $class;
	 }

	 public static function helperWins($index,$winning)
	 {
		 $class = "";
		 if (in_array($index,(array)$winning)) $class = "win";
		 return $class;
	 }
	 
 	public static function getDieOptionPhrase($option)
	{
		return new XenForo_Phrase('h_dm_options_'.$option);
	}
	
	public static function validateTag(&$tag)
	{
		// Empty?
		if (!$tag)
		{
			return false;
		}

		// Not Alphanumerical?
		if (!preg_match('/^[a-z0-9]+$/i', $tag))
		{
			return false;
		}
		
		// no characters?
		if ($tag === strval(intval($tag)) || $tag == '-')
		{
			return false;
		}

		return true;
	}

 }