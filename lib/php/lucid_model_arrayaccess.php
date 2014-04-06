<?php

class lucid_model_arrayaccess extends lucid_model_sqlclause implements ArrayAccess
{
	public function offsetExists ( $offset )
	{
		return isset($this->_data[$this->row][$offset]);
	}
	
	public function offsetGet ( $offset )
	{
		if($this->_columns[$this->_column_idx[$offset]]->type == 'timestamp')
		{
			#echo('found a timestamp. the type is: '.gettype($this->_data[$this->row][$offset])."\n");
			if(gettype($this->_data[$this->row][$offset]) == 'string')
			{
				#echo("need to convert to php DateTime class\n");
				#echo("original value is: ".$this->_data[$this->row][$offset]."\n");
				$this->_data[$this->row][$offset] = new DateTime($this->_data[$this->row][$offset]);
				#echo("new value is: ".print_r($this->_data[$this->row][$offset],true)."\n");
			}
			#echo('the type is now: '.gettype($this->_data[$this->row][$offset])."\n");
			
		}
		if($this->_columns[$this->_column_idx[$offset]]->type == 'boolean')
		{
			$current_type = gettype($this->_data[$this->row][$offset]);
			if($current_type != 'boolean')
			{
				#echo("need to convert boolean from ".$current_type."\n");
				if($current_type == 'string')
				{
					#echo("it is currently a string. comparing value ".$this->_data[$this->row][$offset]." to 'false'\n");
					$this->_data[$this->row][$offset] = ($this->_data[$this->row][$offset] == 'true');
				}
				else if($current_type == 'integer')
				{
					#echo("it is currently an integer. comparing value ".$this->_data[$this->row][$offset]." to numeric 1\n");
					$this->_data[$this->row][$offset] = ($this->_data[$this->row][$offset] == 1);
				}
				else
				{
					throw new Exception("lucid_model was unable to convert value ".$this->_data[$this->row][$offset]." of type ".$current_type." to a boolean.");
				}
				#echo('final type is: '.gettype($this->_data[$this->row][$offset])."\n");
			}
		}
		if($this->_columns[$this->_column_idx[$offset]]->type == 'integer')
		{
			$current_type = gettype($this->_data[$this->row][$offset]);
			if($current_type != 'integer')
			{
				$this->_data[$this->row][$offset] = intval($this->_data[$this->row][$offset]);
			}
		}
		if($this->_columns[$this->_column_idx[$offset]]->type == 'double')
		{
			$current_type = gettype($this->_data[$this->row][$offset]);
			if($current_type != 'double')
			{
				$this->_data[$this->row][$offset] = floatval($this->_data[$this->row][$offset]);
			}
		}
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
		
		if(!is_null($this->_parent_model))
		{
			$this->_parent_model[$this->_table.'__'.$offset] = $value;
		}
	}
	
	public function offsetUnset ( $offset )
	{
		$this->_changed_idx[$this->row][$offset] = true;
		unset($this->_data[$this->row][$offset]);
	}
	
}

?>