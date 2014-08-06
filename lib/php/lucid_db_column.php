<?php

class lucid_db_column
{
	function __construct($idx, $name, $type, $length, $default_value, $is_nullable)
	{
		$this->idx = $idx;
		$this->name = $name;
		$this->type = $type;
		$this->length = $length;
		$this->default_value = $default_value;
		$this->is_nullable = $is_nullable;
	}
    
    public function is_valid_data($test_val)
    {
        #echo('checking '.$test_val.'. must be: '.$this->type.'<br />');
        #echo('tests: '.is_int($test_val).'/'.is_float($test_val).'/'.is_string($test_val).'/'.is_bool($test_val).'<br />');
        switch($this->type)
        {
            case 'int':
                return (is_numeric($test_val) and (intval($test_val) == $test_val));
                break;
            case 'float':
                return (is_numeric($test_val) and (floatval($test_val) == $test_val));
                break;
            case 'string':
                return is_string($test_val);
                break;
            case 'boolean':
                return is_bool($test_val);
                break;
            default:
                return true;
                break;
        }
        return true;
    }
}

?>