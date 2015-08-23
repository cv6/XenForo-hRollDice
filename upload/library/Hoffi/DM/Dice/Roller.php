<?php

/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_Dice_Roller
{

	private $rolledDice, $result, $comment, $hash, $thread_id;
	private $wireset, $rule, $dice, $sum;
	private $model_ws, $model_rule, $model_dice;

	function __construct($ModelWireset, $ModelDice, $ModelRules, $hash, $thread_id)
	{
		// Without Hash, I can't roll.
		if (!$hash OR $hash == "")
			return false;

		$this->hash = $hash;
		$this->thread_id = $thread_id;

		$this->model_ws = $ModelWireset;
		$this->model_rule = $ModelRules;
		$this->model_dice = $ModelDice;
		$this->rolledDice = array();
		$this->dice = array();
		$this->options = array();
		$this->result = null;
		// Now we are ready...
		return true;
	}

	public function setWiresetByTag($WiresetTag)
	{
		// Now Build first Data
		$this->wireset = $this->model_ws->getWiresetById($WiresetTag);
		$this->_loadDietype();
		$this->_loadRule();
	}

	private function _loadDietype()
	{
		if ($this->wireset['dietypes'] == "__all")
		{
			$dice = $this->model_dice->getAllDice(true);
			foreach ($dice as $tag => $die)
			{
				$this->_buildDie($tag, $die);
			}
		}
		else
		{
			$dietypes = explode(",", $this->wireset['dietypes']);
			$this->dice = array();
			foreach ($dietypes as $tag)
			{
				$die = $this->model_dice->getDieByTag($tag);
				$this->_buildDie($tag, $die);
			}
		}
	}

	private function _buildDie($tag, $die)
	{
		$this->dice[$tag] = $die;
		$this->dice[$tag]['source'] = $this->_calcValues($die['values'], $die['sides']);
		$this->dice[$tag]['max'] = max($this->dice[$tag]['source']);
		$this->rolledDice[$tag] = array_merge(
						$this->dice[$tag], array(
				'count' => 0,
				'winners' => array(),
				'explode' => array()
						)
		);
		unset($this->rolledDice[$tag]['source']);
		unset($this->rolledDice[$tag]['values']);
	}

	private function _loadRule()
	{
		$this->rule = $this->model_rule->getRuleById($this->wireset['rule']);
		if ($this->rule and ! empty($this->rule['optionlist']))
		{
			$this->rule['options'] = array_flip(explode("\n", $this->rule['optionlist']));
		}
		else
		{
			$this->rule['options'] = array();
		}
	}

	public function setComment($string)
	{
		$this->comment = $string;
	}

	public function setDieCount($tag, $count)
	{
		if (!array_key_exists($tag, $this->dice))
			return false;

		$count = min($this->wireset['max_dice'], $count);
		if ($count > 0)
		{
			$this->rolledDice[$tag]['count'] = $count;
		}
	}

	private function _calcValues($values, $sides = 0)
	{
		$return = array();
		if (trim($values) == '')
			return range(1, $sides);

		if (strpos($values, ",") !== false)
		{
			$parts = explode(",", $values);
			foreach ($parts as $part)
			{
				$return = array_merge($return, $this->_calcValues($part));
			}
		}
		else if (strpos($values, "*") !== false)
		{
			$calc = explode("*", $values);
			$return = array_fill(1, (int) $calc[0], (int) $calc[1]);
		}
		else if (strpos($values, "-") > 0)
		{
			$calc = explode("-", $values);
			$return = range((int) $calc[0], (int) $calc[1]);
		}
		else
		{
			$return = array((int) $values);
		}
		if ($sides > 0 AND count($return) < $sides)
		{
			$return += array_fill(1, $sides - count($return), $sides);
		}
		else if (count($return) > $sides AND $sides > 0)
		{
			$return = array_slice($return, 0, $sides);
		}
		return $return;
	}

	public function rollAll()
	{
		if (!is_array($this->rolledDice))
			return false;

		foreach ($this->rolledDice as $tag => $die)
		{
			if ($die['count'] > 0)
			{
				$this->rolledDice[$tag]['result'] = array();
				for ($i = 0; $i < $die['count']; $i++)
				{
					$side = mt_rand(1, $this->dice[$tag]['sides']) - 1;
					$this->rolledDice[$tag]['result'][$i] = $this->dice[$tag]['source'][$side];
					if ($this->rolledDice[$tag]['result'][$i] == $this->dice[$tag]['max'] && $this->wireset['explode'] == 'yes')
					{
						$die['count'] ++;
						$this->rolledDice[$tag]['explode'][$i] = $i;
					}
				}
			}
			else
			{
				unset($this->rolledDice[$tag]);
			}
		}
		return count($this->rolledDice);
	}

	public function sortRoll()
	{
		if ($this->wireset['sort_dice'])
		{
			foreach ($this->rolledDice as $tag => $die)
			{
				asort($this->rolledDice[$tag]['result']);
			}
		}
	}

	// 'everytime','highest_three','winning','no','explode'
	public function buildSum()
	{
		$this->sum = 0;
		switch ($this->wireset['build_sum'])
		{
			case 'everytime':
				foreach ($this->rolledDice as $tag => $die)
				{
					$this->sum += array_sum($die['result']);
				}
				break;
			case 'winning':
				foreach ($this->rolledDice as $tag => $die)
				{
					foreach ($die['result'] as $index => $value)
					{
						if (in_array($index, $this->rolledDice[$tag]['winners']))
						{
							$this->sum += $value;
						}
					}
				}
				break;
			case 'no':
			default:
				break;
		}
	}

	public function setOption($tag, $value)
	{
		if (array_key_exists($tag, $this->rule['options']))
		{
			$this->rule['options'][$tag] = $value;
			return true;
		}
		else
			return false;
	}

	public function checkRule()
	{
		if (!$this->rule)
			return null;

		if (array_key_exists('php_callback_class', $this->rule) && array_key_exists('php_callback_method', $this->rule) AND
						XenForo_Helper_Php::validateCallback($this->rule['php_callback_class'], $this->rule['php_callback_method']))
		{
			$rule = new $this->rule['php_callback_class'];
			$method = $this->rule['php_callback_method'];
			$this->result = $rule->$method($this->rolledDice, $this->rule['options']);
			return $this->result;
		}
		else
			return false;
	}

	public function prepareSave()
	{
		$dataDw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Roll');
		// 	$dataDw->bulkSet($extra);
		$dataDw->set('user_id', XenForo_Visitor::getUserId());
		$dataDw->set('comment', $this->comment);
		$dataDw->set('thread_id', $this->thread_id);
		$dataDw->set('data', serialize($this->rolledDice));
		$dataDw->set('options', serialize($this->rule['options']));
		$dataDw->set('hash', $this->hash);
		$dataDw->set('wireset', $this->wireset['tag']);
		$dataDw->set('wins', $this->result);
		if ($this->wireset['build_sum'] != 'no')
		{
			$dataDw->set('result_sum', $this->sum);
		}
		$dataDw->save();
		$id = $dataDw->get('autoIncrement');
		return $id;
	}
	
	public function updateCounters()
	{
		
	}

	public function getFeedback()
	{
		$feedback = array();
		//var_export($this->dicelist);
		foreach ($this->rolledDice as $tag => $dice)
		{
			$feedback['dice'][$tag] = $dice['count'];
		}
		if ($this->comment)
		{
			$feedback['comment'] = $this->comment;
		}
		$feedback['wireset']['tag'] = $this->wireset['tag'];
		$feedback['wireset']['title'] = $this->wireset['title'];
		return $feedback;
	}

	public function get($tag = false)
	{
		if ($tag === false)
			return $this->rolledDice;

		else if (array_key_exists($tag, $this->rolledDice))
			return $this->rolledDice[$tag];
		else
			return false;
	}

}
