<?php
/**
 * Dice Roller
 * @author Hoffi
 *
 * @category Xenforo Hoffi
 * @package DiceRoller
 */
class Hoffi_DM_Dice_RollViewer
{

	private $rolled;
	private $dicelist, $result, $comment, $hash;
	private $wireset, $rule, $dice;
	private $model_ws, $model_rule, $model_dice;

	function __construct($diceData)
	{
		$this->rolled = $diceData;
		// Now we are ready...
		return true;
	}

	public function render($comment="")
	{
		$return = 'DiceInfos:==>';
		foreach($this->rolled as $die => $info)
		{
			$return .= $die.':::'.var_export($info,true);
			$return .= '--- rolled '.$info['count'].' '.$info['title'].' with the following results: '.implode(',',$info['result']).'||';
		}
		return $return;
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
		$dietypes = explode(",",$this->wireset['dietypes']);
		$this->dice = array();
		foreach($dietypes as $tag)
		{
			$die = $this->model_dice->getDieByTag($tag);
			$this->dice[$tag] = $die;
			$this->_addDie($tag, $die);
		}
	}

	private function _loadRule()
	{
		$this->rule = $this->model_rule->getRuleById($this->wireset['rule_id']);
		if ($this->rule)
			$this->rule['options'] = array_flip(explode("\n",$this->rule['optionlist']));
	}

	public function setComment($string)
	{
		$this->comment = $string;
	}

	public function setDieCount($tag, $count)
	{
		if ($count > $this->wireset['max_dice'])
			$count = $this->wireset['max_dice'];
		$this->dicelist[$tag]['count'] = $count;
	}

	private function _addDie($tag,$die,$count=0)
	{
		if (!array_key_exists($tag,$this->dicelist))
		{
			$this->dicelist[$tag] = array(
				'sides' => $die['sides'],
				'title' => $die['title'],
				'values' => $this->calcValues($die['values'],$die['sides']),
				'count' => $count
			);
		}

		return true;
	}

	private function calcValues($values,$sides=0)
	{
		$return = array();
		if (trim($values)=='')
			return range(1,$sides);

		if (strpos($values,",") !== false)
		{
			$parts = explode(",",$values);
			foreach($parts as $part)
			{
				$return += $this->calcValues($part);
			}
		}
		else
		{
			if (strpos($values,"*")!==false)
			{
				$calc = explode("*",$values);
				$return = array_fill(1,(int)$calc[0],(int)$calc[1]);
			}
			else if (strpos($values,"-")!==false)
			{
				$calc = explode("-",$values);
				$return = range((int)$calc[0],(int)$calc[1]);
			}
			else
			{
				$return = array((int)$values);
			}
		}
		if ($sides > 0 AND count($return) < $sides)
		{
			$return += array_fill(1,$sides-count($return),$sides);
		}
		else if (count($return)>$sides)
		{
			$return = array_slice($return,0,$sides);
		}
		return $return;
	}

	public function rollAll()
	{
		if (!is_array($this->dicelist))
				return false;

		foreach($this->dicelist as $tag => $die)
		{
			$this->dicelist[$tag]['result'] = array();
			for($i=0;$i<$die['count'];$i++)
			{
				$a = $die['values'];
				shuffle($a);
				$this->dicelist[$tag]['result'][] = $a[0];
			}
		}
	}

	public function setOption($tag,$value)
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

		return $this->_checkRule($this->rule['php_callback_class'], $this->rule['php_callback_method']);
	}

	private function _checkRule($callback_class,$callback_method)
	{
		$rule = new $callback_class;
		$this->result = $rule->$callback_method($this->dicelist,$this->rule['options']);
		return $this->result;
	}

	public function prepareSave()
	{
		$dataDw = XenForo_DataWriter::create('Hoffi_DM_DataWriter_Roll');
	// 	$dataDw->bulkSet($extra);
		$dataDw->set('user_id', XenForo_Visitor::getUserId());
		$dataDw->set('comment', $this->comment);
		$dataDw->set('thread_id',0);
		$dataDw->set('data', serialize($this->dicelist));
		$dataDw->set('hash', $this->hash);
		$dataDw->save();
		$id = $dataDw->get('autoIncrement');
		return $id;
	}

	public function getFeedback()
	{
		$feedback = "Rolled ";
		$d = array();
		//var_export($this->dicelist);
		foreach($this->dicelist as $tag => $dice)
		{
			$d[] = $dice['count']. " " . $dice['title'];
		}
		$feedback .= implode(", ",$d);
		if ($this->comment)
		{
			$feedback .= ' for ' . $this->comment;
		}
		return $feedback;
	}

	public function get($tag=false)
	{
		if ($tag === false)
			return $this->dicelist;

		else if (array_key_exists($tag, $this->dicelist))
			return $this->dicelist[$tag];

		else
			return false;
	}

}
