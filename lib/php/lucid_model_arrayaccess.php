<?php

class lucid_model_arrayaccess extends lucid_model_sqlclause implements ArrayAccess
{
	public function offsetExists ( $offset )
	{
		return isset($this->data[$this->row][$offset]);
	}
	
	public function offsetGet ( $offset )
	{
		return (isset($this->data[$this->row][$offset]))?$this->data[$this->row][$offset]:null;
	}
	
	public function offsetSet ( $offset , $value )
	{
		if($this->row < 0)
		{
			$this->row = 0;
			$this->data[$this->row] = array();
		}
		
		
		if(!isset($this->data[$this->row][$offset]))
		{
			$this->data[$this->row][$offset] = null;
		}
		
		if($this->data[$this->row][$offset] != $value)
		{
			$this->changed_idx[$this->row][$offset] = true;
		}
		
		$this->data[$this->row][$offset] = $value;
	}
	
	public function offsetUnset ( $offset )
	{
		$this->changed_idx[$this->row][$offset] = true;
		unset($this->data[$this->row][$offset]);
	}
}

?>