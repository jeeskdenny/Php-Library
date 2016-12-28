<?php
/**
 * 
 * Databsae Config! Please Make Your Configuration changed below 
 * add Host name
 * add username to connect mysql 
 * add password to connect mysql
 * add database name
 *
 * @author  Jees K Denny
 * @version 1.0, 28/12/16
 * @since   28/12/16
 */

class Database
{
	private $conn;
	private $host;
	private $user;
	private $pass;
	private $databaseName;
	private $port;
	private $debug;
	
	function __construct()
	{
		$this->conn = false; 
		$this->host = 'localhost'; //hostname
		$this->user = 'root'; //username
		$this->pass = ''; //password
		$this->databaseName = 'dbname'; //name of your database
		$this->port = '3306'; //port 
		$this->debug = true;
		$this->connect();
	}

	public function connect() 
	{
		if (!$this->conn) 
		{
			$this->conn = mysqli_connect($this->host, $this->user, $this->pass, $this->databaseName);
			mysqli_set_charset($this->conn,"utf8");
			if (!$this->conn) 
			{
				if($this->conn->connect_errno > 0)
				{
			    	die('Unable to connect to database [' . $this->conn->connect_error . ']');
				}
			} 
		}
		return $this->conn;
	}

	public function disconnect()
	{
	    if($this->conn)
	    {
	        if(mysqli_close($this->conn))
	        {
	            $this->conn = false; 
	            return true; 
	        }
	        else
	        {
	            return false; 
	        }
	    }
	}

	public function prepare($value)
	{
		return $this->conn->prepare($value);
	}

 	public function insert_id()
 	{
        return $this->conn->insert_id;
    }

    public function affected_rows()
    {
    	return $this->conn->affected_rows;
    }
}