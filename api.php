<?php
//  Use -q so that php doesn't print out the HTTP headers

/**
* @desc API give access through GET parameter
* @desc response json
*
* @license GNU GENERAL PUBLIC LICENSE version 2
* @package pipe-email-php-reader
* @author Ivan Ivanov
* @copyright 2015 http://wwork.eu/
*/

require_once 'include/config.php'; 
require_once 'include/Api.php'; 

$email = new Api();
echo $email->methodRequest;

?>

