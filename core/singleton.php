<?php

class Singleton {
	private static $instances = array();
	
	protected function __construct() {}
	
    public function __clone() {
    	throw new BadMethodCallException(Strings::getInstance()->get('STR', 'ERR_CLONE_NOT_IMPLEMENTED'));
    }

	public function __wakeup() {
		throw new BadMethodCallException(Strings::getInstance()->get('STR', 'ERR_UNSERIALIZE_NOT_IMPLEMENTED'));
	}

	public static function getInstance() {
		$cls = get_called_class();
		if (!isset(self::$instances[$cls])) {
			self::$instances[$cls] = new static;
		}
		return self::$instances[$cls];
	}
}