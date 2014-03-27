<?php

class lucid_model_iterator extends lucid_model_arrayaccess implements Iterator
{
	public function current()
	{
		#lucid::log('current called. row is '.$this->row);
		return $this;
	}
	
	public function key()
	{
		#lucid::log('key called. row is '.$this->row);
		return $this->row;
	}
	
	public function next()
	{
		#echo('next called, row is currently '.$this->row.'. incrementing'."\n");
		$this->row++;
		return $this;
	}
	
	public function rewind()
	{
		if(!$this->_loaded)
		{
			$this->select();
		}
		#lucid::log('rewind called. row is '.$this->row);
		$this->row = 0;
	}
	
	public function valid()
	{
		if(!isset($this->_data[$this->row]))
			return false;
		return is_array($this->_data[$this->row]);
	}
}

?>