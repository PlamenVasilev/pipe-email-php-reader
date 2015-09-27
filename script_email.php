#!/usr/bin/php -q
<?php
//  Use -q so that php doesn't print out the HTTP headers

/**
* @desc Recieve mail and attachments with PHP.
* 
* Configure your mail server to pipe emails to this script.
*
* @license GNU GENERAL PUBLIC LICENSE version 2
* @package pipe-email-php-reader
* @author Ivan Ivanov
* @copyright 2015 http://wwork.eu/
*/

require_once 'include/config.php'; 
require_once 'include/EmailPipe.php'; 

$email = new EmailPipe();


?>

