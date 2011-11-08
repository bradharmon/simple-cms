<?php
//database connect
define ('DB_USER', 'hsproduc_cms');
define ('DB_PASSWORD', 'cms');
define ('DB_HOST', 'localhost');
define ('DB_NAME', 'hsproduc_cms');

try
{
   $dbh = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);
   $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}
catch(PDOException $e)
{
   echo $e->getMessage();
   die();
}

//website settings
define('CMS_DIR', dirname(__FILE__));      //simple-cms absolute path
define('WEBSITE_DIR', dirname(CMS_DIR));   //simple-cms parent absolute path

define('ROOT_URL', 'http://'. $_SERVER['HTTP_HOST']);
define('CMS_URL', 'http://'. $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']));
//remove trailing '/'
define('WEBSITE_URL', 'http://'. preg_replace( '/\/$//', '', $_SERVER['HTTP_HOST'] . dirname(dirname(dirname($_SERVER['PHP_SELF'])))) );
?>
