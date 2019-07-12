<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();
session_start();

define('DB_USER', 'root');
define('DB_PWD', 'malc0lm.d99');
define('DB_NAME', 'amazon_review');
define('DB_HOST', '51.15.193.78');
define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME .'');

define('ROOT_DIR', '/var/www/html/amzrs/scraper-amazon-inorganic/');
?>
