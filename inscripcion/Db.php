<?php 
	include 'ChromePhp.php';	
/**
 * Database wrapper for a MySQL with PHP tutorial
 * 
 * @copyright Eran Galperin
 * @license MIT License
 * @see http://www.binpress.com/tutorial/using-php-with-mysql-the-right-way/17
 */
class Db {
	// The database connection
	protected static $connection;
	
	/**
	 * Connect to the database
	 * 
	 * @return bool false on failure / mysqli MySQLi object instance on success
	 */
	public function connect() {
		
		// Try and connect to the database
		if(!isset(self::$connection)) {
			// Load configuration as an array. Use the actual location of your configuration file
			// Put the configuration file outside of the document root
			$config = parse_ini_file('../../config.ini'); 
			self::$connection = new mysqli($config['host'],$config['username'],$config['password'],$config['dbname']);
		}
		
		if (self::$connection -> connect_errno) {
			error_log("Failed to connect to MySQL: " . self::$connection -> connect_error . PHP_EOL, 3, "../../errors.log");
        }
        
		return self::$connection;
	}
	
	/**
	 * Query the database
	 *
	 * @param $query The query string
	 * @return mixed The result of the mysqli::query() function
	 */
	public function query($query) {
		// Connect to the database
		$connection = $this -> connect();
		
		// Query the database
		$result = $connection -> query($query);
		
		if($result == false)
		{
			error_log($connection -> error, 3, "../../errors.log");
		}
		
		return $result;
	}
	
	public function insert_id() {
		
		$connection = $this -> connect();
		
		return $connection -> insert_id;
	}
	
	/**
	 * Fetch rows from the database (SELECT query)
	 *
	 * @param $query The query string
	 * @return bool False on failure / array Database rows on success
	 */
	public function select($query) {
		$rows = array();
		$result = $this -> query($query);
		if($result === false) {
			return false;
		}
		while ($row = $result -> fetch_assoc()) {
			$rows[] = $row;
		}
		return $rows;
	}
	
	/**
	 * Fetch the last error from the database
	 * 
	 * @return string Database error message
	 */
	public function error() {
		$connection = $this -> connect();
		return $connection -> error;
	}
	
	/**
	 * Quote and escape value for use in a database query
	 *
	 * @param string $value The value to be quoted and escaped
	 * @return string The quoted and escaped string
	 */
	public function quote($value) {
		$connection = $this -> connect();
		return "'" . $connection -> real_escape_string($value) . "'";
	}
}