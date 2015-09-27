<?php

/* 
 * @desc Base class structure
 * 
 * @license GNU GENERAL PUBLIC LICENSE version 2
 * @package pipe-email-php-reader
 * @author Ivan Ivanov
 * @copyright 2015 http://wwork.eu/
 */

if(count(get_included_files()) ==1) exit("Direct access not permitted.");


class BaseStructura {
    
    protected $data = array();
    protected $_db;
    
    public function __construct() {
        $this->dbConnection();
    }
    
    public function __autoload($classname) {
        $filename = $classname .".php";
        include_once($filename);
    }
     
    
    public function __destruct() {
        mysql_close($this->_db);
    }
   
    /**
     * 
     * @desc if method exist getFunction
     * @return resultat getFunction
     */
    public function __get($name) {

         if (array_key_exists($name, $this->data)) {
             return $this->data[$name];
         }

         $method = 'get' . ucfirst($name);
         if(is_callable(array($this, $method))) {
             $this->data[$name] = $this->$method();
             return $this->data[$name];
         }

         return NULL;
    }

     /**
      * @desc set DB connection
      */
     protected function dbConnection() {
          $this->_db = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
          mysql_select_db(DB_NAME, $this->_db);
     }

}