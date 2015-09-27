<?php



/* 

 * @desc configuration

 */



/** 

 * What's the max # of seconds to try to process

 */

define('MAX_TIME_LIMIT', 600);



/**

 * Configure your MySQL database connection here

 */



/** 

 * MySQL hostname

 */

define('DB_HOST', 'localhost');



/**

 * The name of the database for WordPress

 */

define('DB_NAME', '******');



/**

 * MySQL database username

 */

define('DB_USER', '******');



/**

 * MySQL database password

 */

define('DB_PASSWORD', '******');



set_time_limit(MAX_TIME_LIMIT);

ini_set('max_execution_time', MAX_TIME_LIMIT);
