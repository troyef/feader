<?php

class DBHelper{
	
	private $_dbServer;
	private $_dbUser;
	private $_dbPass;
	private $_dbDatabase;
	
	private $_connection = null;
	
	public function __construct($configuration) {
		$this->_dbServer = $configuration->dbServer;
		$this->_dbUser = $configuration->dbUser;
		$this->_dbPass = $configuration->dbPass;
		$this->_dbDatabase = $configuration->dbDatabase;
	}
	
	public function createConnection(){
		$this->_connection = mysqli_connect($this->_dbServer, $this->_dbUser, $this->_dbPass, $this->_dbDatabase);
		if (mysqli_connect_errno($mysqli))
		{
			die('Could not connect: ' . mysqli_connect_error());
		}
	}
	
	public function closeConnection(){
		$this->_connection->close();
	}
	
	public function executeQuery($query) {
		
		//error_log("Executing SQL Query: \n\n".$query."\n\n"); 
		//$timestart = microtime(1); // note 1 
		$result = mysqli_query($this->_connection, $query); 
		//error_log("Query took ".(microtime(1)-$timestart)." seconds. SQL: ".$query); 

		if (!$result) {
		    $message  = 'Invalid query: ' . $this->_connection->error . "\n";      
			$message .= 'Query: ' . $query;
		    die($message);
		}
		//var_dump($result);
	
		return $result;
	}
	

	public function executeQueryConnection($query) {
		
		$this->createConnection();
		$result = $this->executeQuery($query); 
		$this->closeConnection();
	
		return $result;
	}
	
	
}


