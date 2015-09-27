<?php

/**
 * @desc Api class structure
 * 
 * @license GNU GENERAL PUBLIC LICENSE version 2
 * @package pipe-email-php-reader
 * @author Ivan Ivanov
 * @copyright 2015 http://wwork.eu/
 * 
 * 
 */

if(count(get_included_files()) ==1) exit("Direct access not permitted.");
require_once('BaseStructura.php');

class Api extends BaseStructura {
    /**
     * Property: method
     * The HTTP method this request was made in, either GET, POST, PUT or DELETE
     */
    private $APIKey;

    const ACTION_EMAIL = 'get_email';
    const ACTION_SUBJECT = 'get_by_subject';
    const ACTION_COUNT_BY_SENDER = 'get_count_by_sender';
    const ACTION_COUNT_BY_DOMAIN = 'get_count_by_domain';
    const ACTION_PRECENT_SPF = 'get_precent_spf';
    
    protected $method;
    protected $request;
    
    /**
     * Constructor: __construct
     */
    public function __construct() {
        parent::__construct();
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * 
     * @desc generate respons
     * @return json
     */
    private function _response($data, $status = 200) {
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
        return json_encode($data);
    }

    /**
     * 
     * @desc security clean $data
     */
    private function _cleanInputs($data) {
        $clean_input = Array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->_cleanInputs($v);
            }
        } else {
            $clean_input = trim(htmlspecialchars(strip_tags($data)));
        }
        return $clean_input;
    }
    
    /**
     * 
     * @desc return status
     */
    private function _requestStatus($code) {
        $status = array(  
            200 => 'OK',
            404 => 'Not Found',   
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        ); 
        return ($status[$code])?$status[$code]:$status[500]; 
    }
    
    /**
     * @property json $userEmail
     */
    public function getUserEmail($email = '') {
        $res = array();
        $q = "SELECT `id`, `from`, `domain`, `subject`, `date` FROM  `emails` WHERE  `from` LIKE  '%$email%'";
        $result = mysql_query($q, $this->_db);
        
        while ($row = mysql_fetch_assoc($result))
            $res[] = $row;

        return $this->_response($res);
    }
    
    /**
     * @property json $fromSubject
     */
    public function getFromSubject($subject = '') {
        $res = array();
        $q = "SELECT `id`, `from`, `domain`, `subject`, `date` FROM  `emails` WHERE  `subject` LIKE  '%$subject%'";
        $result = mysql_query($q, $this->_db);
        
        while ($row = mysql_fetch_assoc($result))
            $res[] = $row;

        return $this->_response($res);
    }
    
    /**
     * @property json $userEmailCount
     */
    public function getUserEmailCount($email = '') {
        $res = array();
        $q = "SELECT `from`, COUNT(id) as count FROM `emails` WHERE `from` LIKE  '%$email%' GROUP BY `from`";
        $result = mysql_query($q, $this->_db);
        
        while ($row = mysql_fetch_assoc($result))
            $res[] = $row;


        return $this->_response($res);
    }
    
    /**
     * @desc Yes there was option to have no "domain" field in DB
     * @desc and query to be as following
     * SELECT *, SUBSTRING_INDEX(`from`, '@', -1) as example FROM `emails`
     * 
     * @property json $domainCount
     */
    public function getDomainCount($domain = '') {
        $res = array();
        $q = "SELECT domain, COUNT(id) as count FROM `emails` WHERE `domain` LIKE  '%$domain%' GROUP BY `domain`";
        $result = mysql_query($q, $this->_db);
        
        while ($row = mysql_fetch_assoc($result))
            $res[] = $row;

        
        return $this->_response($res);
    }
    
    /**
     * @property json $spfPrecent
     */
    public function getSpfPrecent() {
        $total = 0;
        $spf = 0;
        $q = "SELECT `id`, `from`, `domain`, `subject`, `date` FROM `emails`";
        $result = mysql_query($q, $this->_db);
        $total = mysql_num_rows($result);
        
        $q = "SELECT `id`, `from`, `domain`, `subject`, `date` FROM `emails` WHERE `spf`=1";
        $result = mysql_query($q, $this->_db);
        $spf = mysql_num_rows($result);
        $precent = ($spf / $total) * 100;
        $precent = ($precent != (int)$precent) ? bcadd($precent, '0', 2) : $precent;
        $res = array('spf' => $precent);

        
        return $this->_response($res);
    }
    
    /**
     * @desc get request type
     * @return json
     */
    public function getMethodRequest() {
        
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");
        
        switch($this->method) {
        case 'DELETE':
        case 'POST':
            $this->request = $this->_cleanInputs($_POST);
            return $this->action;
            break;
        case 'GET':
            $this->request = $this->_cleanInputs($_GET);
            return $this->action;
            break;
        case 'PUT':
        default:
            return $this->_response('Invalid Method', 405);
            break;
        }
    }

    /**
     * @desc detect action
     * @return json
     */
    public function getAction() {
        
        
        if(isset($this->request['action'])) {
            switch($this->request['action']) {
            case self::ACTION_EMAIL:
                return $this->getUserEmail($this->request['value']);
            case self::ACTION_SUBJECT:
                return $this->getFromSubject($this->request['value']);
            case self::ACTION_COUNT_BY_SENDER:
                return $this->userEmailCount;
            case self::ACTION_COUNT_BY_DOMAIN:
                return $this->domainCount;
            case self::ACTION_PRECENT_SPF:
                return $this->spfPrecent;
            }
        }
        
        return $this->_response('Invalid Method', 405);
    }
}