<?php

if (!defined('PATH')) exit('Direct access to script is not allowed.');

//------------------------------------------------------------
//	Date: 2011 02 19
//	Desc: MySQL database interface.
//------------------------------------------------------------

class DB extends Singleton {
	
    private $db = null;
    private $queryCounter = 0;
    
	protected function __construct() {
		$server = Config::WEB_DB_SERVER;
		$database = Config::WEB_DB_DATABASE;
		$username = Config::WEB_DB_USERNAME;
		$password = Config::WEB_DB_PASSWORD;
    	$this->db = new PDO("mysql:host=$server;dbname=$database;charset=utf8mb4", $username, $password);
    	$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function __destruct() {
    	$this->db = null;
    }

	public function execute() {
		$args = func_get_args();
        $stmt = $this->db->prepare(array_shift($args));
		$stmt->execute($args);
		$this->queryCounter++;
		return $stmt;
	}

    /**
     * Sample: insert('INSERT INTO users (name, email) VALUES (?, ?)', $name, $email);
     */
	public function insert() {
		$args = func_get_args();
        $stmt = $this->db->prepare(array_shift($args));
        $stmt->execute($args);
        $this->queryCounter++;
		return $stmt->rowCount() > 0;
	}

    public function getLastInsertId() {
        return $this->db->lastInsertId();
    }

    /**
     * Sample: update('users SET name=?, email=?', $name, $email);
     */
	public function update() {
		$args = func_get_args();
		$query = "UPDATE " . array_shift($args);
		$stmt = $this->db->prepare($query);
		$stmt->execute($args);
		$this->queryCounter++;
		return $stmt->rowCount() > 0;
	}

	/**
	 * Sample: update('DELETE FROM users WHERE userId=?', $userId);
     * @return bool True if atleast one row was deleted
	 */
	public function delete()  {
		$args = func_get_args();
		$stmt = $this->db->prepare(array_shift($args));
		$stmt->execute($args);
		$this->queryCounter++;
		return $stmt->rowCount() > 0;
	}

    /**
     * Sample: insertArray('INSERT INTO users (name, email) VALUES (?, ?), (?, ?)', array($name1, $email1, $name2, $email2));
     * @return bool True if atleast one row was inserted
     */
	public function insertArray() {
		$args = func_get_args();
		$stmt = $this->db->prepare(array_shift($args));
		$stmt->execute(array_shift($args));
		$this->queryCounter++;
		return $stmt->rowCount() > 0;
	}

    /**
     * Sample: selectColumnArray('SELECT id FROM users WHERE name=?', $userName);
     * @return array Array consisting of single table column values
     */
    public function selectColumnArray() {
        $args = func_get_args();
        $stmt = $this->db->prepare(array_shift($args));
        $stmt->execute($args);
        $ret = array();
        while ($obj = $stmt->fetch()) {
            $ret[] = $obj;
        }
        $this->queryCounter++;
        return $ret;
    }

    /**
     * Sample: selectObjectArray('SELECT * FROM users WHERE name = ?', $userName);
     * @param $query - Query with parameters
     * @return array Object array
     */
    public function selectObjectArray($query) {
    	$ret = array();
    	$stmt = $this->db->query($query);
    	$stmt->setFetchMode(PDO::FETCH_OBJ);
    	while ($obj = $stmt->fetch()) {
    		$ret[] = $obj;
    	}
    	$this->queryCounter++;
    	return $ret;
    }
    
    public function getObject() {
		$args = func_get_args();
		$query = array_shift($args);
		$stmt = $this->db->prepare($query);
		$stmt->setFetchMode(PDO::FETCH_OBJ);
		$stmt->execute($args);
    	$this->queryCounter++;
    	return $stmt->fetch();
    }
    
    /**
     * Returns first column specified in SELECT query.
     * @return Column content.
     */
    public function getFirstColumn($query) {
    	$statement = $this->db->query($query);
    	$statement->setFetchMode(PDO::FETCH_NUM);
    	$this->queryCounter++;
        $column = $statement->fetch();
    	return $column[0];
    }
    
    /**
     * Takes first two SELECT columns. First will be used as key,
     * second as value.
     * @return Array with containing key value pairs.
     */
    public function getKeyValueArray($query) {
    	$ret = array();
    	$statement = $this->db->query($query);
    	$statement->setFetchMode(PDO::FETCH_NUM);
    	while ($arr = $statement->fetch()) {
    		$ret[$arr[0]] = $arr[1];
    	}
    	$this->queryCounter++;
    	return $ret;
    }
    
    public function getQueryCounter() {
        return $this->queryCounter;
    }
	
}