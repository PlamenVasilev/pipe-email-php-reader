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
define('DB_NAME', 'tarnovoi_email');

/**
 * MySQL database username
 */
define('DB_USER', 'tarnovoi_email');

/**
 * MySQL database password
 */
define('DB_PASSWORD', 'N=XQoKvo6o*T');

set_time_limit(MAX_TIME_LIMIT);
ini_set('max_execution_time', MAX_TIME_LIMIT);