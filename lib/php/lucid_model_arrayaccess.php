<?php

class lucid_model_arrayaccess extends lucid_model_sqlclause implements ArrayAccess
{
	public function offsetExists ( $offset )
	{
		return isset($this->_data[$this->row][$offset]);
	}
	
	public function offsetGet ( $offset )
	{
		return (isset($this->_data[$this->row][$offset]))?$this->_data[$this->row][$offset]:null;
	}
	
	public function offsetSet ( $offset , $value )
	{
		if($this->row < 0)
		{
			$this->new_row();
		}
		
		
		if(!isset($this->_data[$this->row][$offset]))
		{
			$this->_data[$this->row][$offset] = null;
		}
		
		if($this->_data[$this->row][$offset] != $value)
		{
			$this->_changed_idx[$this->row][$offset] = true;
		}
		
		$this->_data[$this->row][$offset] = $value;
	}
	
	public function offsetUnset ( $offset )
	{
		$this->_changed_idx[$this->row][$offset] = true;
		unset($this->_data[$this->row][$offset]);
	}
	
}

?>