<?php

if (!defined('PATH')) exit('Direct access to script is not allowed.');

//------------------------------------------------------------
//	Date: 2011 02 19
//	Desc: this file generates string array.
//------------------------------------------------------------

class Strings extends Singleton
{
    private $strings = array();

    public function __construct()
    {
        $this->strings['COPYRIGHTS'] = '&copy; MSGhost, 2011';
        $this->strings['STATISTICS'] = 'Statistics';
        $this->strings['MESSAGE_TO_ADMIN'] = 'Message for administrator';
        $this->strings['ID'] = 'Id';
        $this->strings['NAME'] = 'Name';
        $this->strings['EMAIL'] = 'Email';
        $this->strings['LOGIN'] = 'Login';
        $this->strings['LOGOUT'] = 'Logout';
        $this->strings['REGISTER'] = 'Register';
        $this->strings['USERNAME'] = 'Username';
        $this->strings['PASSWORD'] = 'Password';
        $this->strings['SUBMIT'] = 'Submit';
        $this->strings['HOME'] = 'Home';
        $this->strings['FORUM'] = 'Forum';
        $this->strings['ABOUT'] = 'About';
        $this->strings['STATUS'] = 'Status';
        $this->strings['ADDS'] = 'Adds';
        $this->strings['ADMIN'] = 'Admin';
        $this->strings['BACK'] = 'Back';
        $this->strings['ERROR'] = 'Error';
        $this->strings['GUEST'] = 'Guest';
        $this->strings['TITLE'] = 'Title';
        $this->strings['MESSAGE'] = 'Message';
        $this->strings['THREAD_NEW'] = 'New thread';
        $this->strings['PLUGINS'] = 'Plugins';
        $this->strings['SUCCESS'] = 'Operation was sucessful.';
        $this->strings['PAGES'] = 'Pages';
        $this->strings['NEW_PAGE'] = 'New page:';
        $this->strings['CREATE'] = 'Create';
        $this->strings['ALIAS'] = 'Alias';
        $this->strings['ERR_LOGIN'] = 'Wrong email or password.';
        $this->strings['ERR_ID_INVALID'] = 'Specified page id is invalid';
        $this->strings['ERR_FILE_NOT_FOUND'] = 'File %s not found';
        $this->strings['ERR_DIR_NOT_FOUND'] = 'Directory not found';
        $this->strings['ERR_MYSQL_CONNECT_FAILED'] = 'Attempt to connect to MySQL database failed';
        $this->strings['ERR_MYSQL_SELECT_DB_FAILED'] = 'Attempt to select database failed';
        $this->strings['ERR_MYSQL_QUERY_FAILED'] = 'MySQL query failed';
        $this->strings['ERR_MYSQL_NO_ENTRY'] = 'Entry in database not found.';
        $this->strings['ERR_VARS_VAR_NOT_FOUND'] = 'Variable %s not found';
        $this->strings['ERR_UNKNOWN_COMPONENT'] = 'Unknown component type: %s';
        $this->strings['QUERIES_EXECUTED'] = 'Queries executed: %d';
        $this->strings['GENERATION_TIME'] = 'Page was generated in %f seconds';
        $this->strings['ERR_FAILED_TO_ADD_PAGE'] = 'There was an error trying to add a new page.';
        $this->strings['ERR_CLONE_NOT_IMPLEMENTED'] = 'This object has no clone method.';
        $this->strings['ERR_UNSERIALIZE_NOT_IMPLEMENTED'] = 'This object does not implement unserialize method.';
        $this->strings['ERR_WRONG_CONSTRUCTOR_ARGUMENT_COUNT'] = 'Wrong constructor argument count specified.';
        $this->strings['ERR_MISSING_REQUIRED_DATA'] = 'Missing required data.';
        $this->strings['temp'] = '';
    }

    public function get ()
    {
       return $this->strings;
    }
}