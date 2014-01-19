<?php

class lucid_db_adaptor_mysql extends lucid_db_adaptor
{
	public function __construct()
	{
		global $lucid;
		$lucid->db = $this;
		$this->pdo = new PDO(
			'mysql:dbname='.$lucid->config['db']['database'].';host='.$lucid->config['db']['hostname'], 
			$lucid->config['db']['username'], 
			$lucid->config['db']['password']
		);
		lucid::log('database connection up');
		
	}
}

?>