<?php

/**
* class Email save data in database using class EmailPipe
* 
* Configure your mail server to pipe emails to this script.
*
* @license GNU GENERAL PUBLIC LICENSE version 2
* @package pipe-email-php-reader
* @author Ivan Ivanov
* @copyright 2015 http://wwork.eu/
*/

if(count(get_included_files()) ==1) exit("Direct access not permitted.");
require_once('mimeDecode.php');
require_once('BaseStructura.php');

class EmailPipe extends BaseStructura {
    
    private $paramsDecode = Array(
	'decode_headers' => TRUE,
	'include_bodies' => TRUE, 
	'decode_bodies' => TRUE,
    );
    
    private $_decoded;
    private $_email;
    private $spf;
    
    private $body;
    private $subject;
    private $fromEmail;
    private $files = array();


    public function __construct() {
        parent::__construct();
        $decoder = new Mail_mimeDecode($this->readEmail());
        $this->_decoded = $decoder->decode($this->paramsDecode);
        $this->decode();
        $this->saveToDb();
    }
   
    /**
     * @desc read email text
     * @return String email conntent
     */
    private function readEmail() {
         $fd = fopen('php://stdin','r');

         while(!feof($fd)){ $this->_email .= fread($fd,1024); }
         return $this->_email;
    }
    
    /**
     * @desc decode email
     */
    private function decode() {

         $from = $this->_decoded->headers['from'];
         $this->fromEmail = preg_replace('/.*<(.*)>.*/',"$1",$from);
         $this->spf = ((isset($this->_decoded->headers['received-spf']) && strpos($this->_decoded->headers['received-spf'],'pass') !== false)?1:0);
         // Set the $subject
         $this->subject = $this->_decoded->headers['subject'];
         
         // Find the email body, and any attachments
         // $body_part->ctype_primary and $body_part->ctype_secondary make up the mime type eg. text/plain or text/html
         if(is_array($this->_decoded->parts)){
             foreach($this->_decoded->parts as $idx => $body_part){
                 $this->decodePart($body_part);
             }
         }
    }
    
    /**
     * 
     * @desc decode attachment
     * @return boolean
     */
    private function decodePart($body_part){
        
        if(array_key_exists('name',$body_part->ctype_parameters)){ // everyone else I've tried
            $filename = $body_part->ctype_parameters['name'];
        }else if($body_part->ctype_parameters && array_key_exists('filename',$body_part->ctype_parameters)){ // hotmail
            $filename = $body_part->ctype_parameters['filename'];
        }else{
            //return FALSE;
        }

        $mimeType = "{$body_part->ctype_primary}/{$body_part->ctype_secondary}"; 
        
        switch($body_part->ctype_primary){
        case 'text':
            $this->body = $body_part->body; // If there are multiple text/plain parts, we will only get the last one. 
            break;
        case 'multipart':
            if(is_array($body_part->parts)){
                foreach($body_part->parts as $ix => $sub_part){
                    $this->decodePart($sub_part);
                }
            }
            break;
        default:
            $this->saveFile($filename, $body_part->body,$mimeType);
            break;
        }
    }
   
    /**
     * 
     * @desc attachment in array
     */
    protected function saveFile($name, $contents, $mimeType){
        
        $this->files[$name] = Array(
            'size' => mb_strlen($contents, '8bit'), 
            'mime' => $mimeType,
            'contents' => $contents,
        );
    }
    
    /**
     * @desc save in DB
     */
    protected function saveToDb() {
        
        $q = "INSERT INTO `emails` (`from`, `domain`, `subject`, `body`, `spf`) VALUES ('" .
            mysql_real_escape_string($this->fromEmail) . "','" .
            array_pop(explode('@', mysql_real_escape_string($this->fromEmail))) . "','" .
            mysql_real_escape_string($this->subject) . "','" .
            mysql_real_escape_string($this->body) . "','" . 
            $this->spf . "')";

        mysql_query($q, $this->_db) or die(mysql_error());
            
            if(count($this->files) > 0){
            $id = mysql_insert_id($this->_db);
            $q = "INSERT INTO `files_content` (`id`, `email_id`, `name`, `mime`, `size`, `data`) VALUES ";
            $filesar = Array();
            $i_mark = false;
            foreach($this->files as $f => $data){
                    if($i_mark)
                            $q .= ", ";
                    else $i_mark = true;
                    $q .= "(NULL, '$id', '$f', '".$data['mime']."', '".$data['size']."', '".mysql_real_escape_string($data['contents'])."')";
            }
            $q .= ";";

            mysql_query($q, $this->_db) or die(mysql_error());
            
        }
    }
}