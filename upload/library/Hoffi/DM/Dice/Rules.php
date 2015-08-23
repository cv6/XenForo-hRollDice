<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_Dice_Rules
{

	public static function HighestDie(&$roll,array $options)
  {

  }

	public static function LowestDie(&$roll,array $options)
  {

  }

	public static function specialSavageWorlds(&$roll, array $options)
	{
		$win = 0;
		if (self::_neededOptions(array('difficulty'), $options))
		{
			$highestValue = 0;
			$highestIndex = array();
			$highestTag = null;
			$calcValue = 0;
			$calcIndex = array();
			foreach ($roll as $tag => $die)
			{
				foreach($die['result'] as $index => $res)
				{
					if ($res > $highestValue)
					{
						$highestValue = $res;
						$highestIndex = array($index);
						$highestTag = $tag;
					}
					if($res == $roll[$tag]['max'])
					{
						// OH, Exploding die
						$calcValue += $res;
						$calcIndex[] = $index;
					}
					else 
					{
						// Neither exploding, not a higher value
						// but, reset the calc values
						// Just, Check the cals Valuers for a higher result.
						$calcValue += $res;
						$calcIndex[] = $index;
						if ($calcValue > $highestValue)
						{
							// Okay, this exploding was the greatest until now.
							$highestValue = $calcValue;
							$highestTag = $tag;
							$highestIndex = $calcIndex;
						}
						$calcValue = 0;
						$calcIndex = array();
					}
				}				
			}
			if ($highestValue >= $options['difficulty'])
			{
				$roll[$highestTag]['winners'] = $highestIndex;
				$win = count($highestIndex);
			}
		}
		return $win;
	}
	
	public static function specialDSA(&$roll, array $options)
	{
		$win = 0;
		$avPoints = 0;
		// OK, a DSA Roll should contain three d20 and three options.
		// But, the tag is maybe another, so we just dont check ist. Only if the roll is lower that the diff. Only one Die Typ allowed.
		//if (array_key_exists('dsa1', $options) and array_key_exists('dsa2', $options) and array_key_exists('dsa3', $options) and array_key_exists('points', $options))
		if (self::_neededOptions(array('dsa1','dsa2','dsa3','points'), $options))
		{
			$dietypeCounter = 0;
			$avPoints = $options['points'];
			foreach ($roll as $tag => $die)
			{
				$dietypeCounter++;
				$i = 1;
				foreach($die['result'] as $index => $res)
				{
					if ($i <= 3)
					{
						if ($res <= $options['dsa'.$i])
						{
							$roll[$tag]['winners'][] = $index;
						}
						else if ($res <= ($options['dsa'.$i]+$avPoints))
						{
							$roll[$tag]['winners'][] = $index;
							$avPoints -= ($res - $options['dsa'.$i]);
						}
					}
					else
					{
						// This should no be used anyway, dsa has only three dice
						return false;
					}
				}
				if (count($roll[$tag]['winners']) == 3 && $dietypeCounter == 1)
				{
					$win = 3;
					$roll[$tag]['points'] = $avPoints;
				}
			}
		}
		return $win;
	}

	public static function specialDSA5(&$roll, array $options)
	{
		$win = 0;
		$avPoints = 0;
		// OK, a DSA5 Roll should contain three d20 and three options, skill value and difficulty
		// But, the tag is maybe another, so we just dont check ist. Only if the roll is lower that the diff. Only one Die Typ allowed.
		//if (array_key_exists('dsa1', $options) and array_key_exists('dsa2', $options) and array_key_exists('dsa3', $options) and array_key_exists('points', $options))
		if (self::_neededOptions(array('dsa1','dsa2','dsa3','points','difficulty'), $options))
		{
			$dietypeCounter = 0;
			$avPoints = $options['points'];
			$diff = $options['difficulty'];
			if ($diff > 0) $avPoints += $diff;
			foreach ($roll as $tag => $die)
			{
				$dietypeCounter++;
				$i = 1;
				foreach($die['result'] as $index => $res)
				{
					if ($i <= 3)
					{
						if ($res <= ($options['dsa'.$i]+$diff))
						{
							$roll[$tag]['winners'][] = $index;
						}
						else if ($res <= ($options['dsa'.$i]+$avPoints+$diff))
						{
							$roll[$tag]['winners'][] = $index;
							$avPoints -= ($res - ($options['dsa'.$i]+$diff));
						}
					}
					else
					{
						// This should no be used anyway, dsa has only three dice
						return false;
					}
				}
				if (count($roll[$tag]['winners']) == 3 && $dietypeCounter == 1)
				{
					$win = 3;
					$roll[$tag]['points'] = min($avPoints,$options['points']);
					$roll[$tag]['qs'] = ceil($roll[$tag]['points']/3);
				}
			}
		}
		return $win;
	}

	public static function speacialDandD(&$roll, array $options)
	{
		$win = 0;
		if (self::_neededOptions(array('bonus','difficulty'), $options))
		{
			// D&D rolls only one die. So, we do, that every die is a single test with equals skill
			// Or set inside the wireset that only one die can be rolled.
			foreach ($roll as $tag => $die)
			{
				foreach($die['result'] as $index => $res)
				{
					if ($res+$options['bonus'] > $options['difficulty'])
					{
						$win++;
						$roll[$tag]['winners'][] = $index;
					}
				}
			}
		}
		return $win;
	}

	public static function speacialShadowrun4(&$roll, array $options)
	{
		$options = array('rollover' => 3);
		$data = self::aboveX($roll, $options);
		return $data;
	}
	
	public static function speacialShadowrun5(&$roll, array $options)
	{
		$options = array('rollover' => 4);
		$data = self::aboveX($roll, $options);
		return $data;
	}
	
	public static function aboveOrEquals(&$roll, array $options)
	{
		$win = 0;
		if (self::_neededOptions(array('rollover'), $options))
		{
			$diff = $options['rollover'];
			foreach ($roll as $tag => $die)
			{
				foreach($die['result'] as $index => $res)
				{
					if ($res >= $diff)
					{
						$roll[$tag]['winners'][] = $index;
						$win++;
					}
				}
			}
		}
		return $win;		
	}
	
	public static function underOrEquals(&$roll, array $options)
	{
		$win = 0;
		if (self::_neededOptions(array('difficulty'), $options))
		{
			$diff = $options['difficulty'];
			foreach ($roll as $tag => $die)
			{
				foreach($die['result'] as $index => $res)
				{
					if ($res <= $diff)
					{
						$roll[$tag]['winners'][] = $index;
						$win++;
					}
				}
			}
		}
		return $win;
	}
	
	public static function aboveX(&$roll, array $options)
  {
		$win = 0;
		if (self::_neededOptions(array('rollover'), $options))
		{
			$diff = $options['rollover'];
			foreach ($roll as $tag => $die)
			{
				foreach($die['result'] as $index => $res)
				{
					if ($res > $diff)
					{
						$roll[$tag]['winners'][] = $index;
						$win++;
					}
				}
			}
		}
		return $win;
  }

	public static function UnderX(&$roll, array $options)
	{
		$win = 0;
		if (self::_neededOptions(array('difficulty'), $options))
		{
			$diff = $options['difficulty'];
			foreach ($roll as $tag => $die)
			{
				foreach($die['result'] as $index => $res)
				{
					if ($res < $diff)
					{
						$roll[$tag]['winners'][] = $index;
						$win++;
					}
				}
			}
		}
		return $win;
	}

	public static function aPair(&$roll, array $options = array())
	{
		$win = 0;
		foreach($roll as $die)
		{
			$found = array();
			foreach($die['result'] as $res)
			{
				if (in_array($res,$found))
					$win = true;
				$found[] = $res;
			}
		}
		return $win;
	}

	public static function countPairs(&$roll, array $options = array())
	{
		$win = 0;
		// No Options
		foreach($roll as $tag => $die)
		{
			$found = $die['result'];
//			foreach($die['result'] as $index => $res)
//			{
//				$found[$index] = $res;
//			}
			asort($found);
			$oldVal = null;
			$c = 0;
			foreach($found as $index => $value)
			{
				if ($value == $oldVal)
				{
					if (++$c == 2) $roll[$tag]['winners'][] = $oldIndex;
					$roll[$tag]['winners'][] = $index;
				}
				else
				{
					// newVal
					$oldVal = $value;
					$oldIndex = $index;
					if ($c >= 2) $win++;
					$c = 1;
				}
			}
			// Check, if the last was a pair
			if ($c >= 2) $win++;
		}
		return $win;
	}

	public static function _default(&$roll, array $options = array())
  {

  }
	
	protected static function _neededOptions($list, $options)
	{
		$found = true;
		foreach($list as $option)
		{
			if (array_key_exists($option, $options) && $found) 
			{
				$found = true;
			}
		}
		return $found;
	}
}