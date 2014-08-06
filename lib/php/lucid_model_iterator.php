<?php

class lucid_model_iterator  implements Iterator
{
    public function current ()
    {
        #echo('current called<br />');
        return $this;
    }
    
    public function key ()
    {
        #echo('key called<br />');
        return $this->row;
    }
    
    public function next ()
    {
        #echo('next called<br />');
        $this->row++;
    }
    
    public function rewind ()
    {
        # echo('rewind called<br />');
        $this->row = 0;
    }
    
    public function valid ()
    {
        #echo('valid called<br />');
        return ($this->row < count($this->_data));
    }
}

?>