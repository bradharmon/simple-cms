<?php
session_start();

include('../settings.php');
include(CMS_DIR . '/login/session.php');

//log the user out
logout();
//FIXME: redirect to page given in post
#regex that matches everything after the domain name
preg_match('/\w*:\/\/w*\.?[\w-_]*\.?[A-Za-z]*:?\d*\/(.*)/', $_GET['page'], $page);
$page = $page[1];

#append index.php as default if a php file isn't specified
if(!preg_match('/\.php$/', $page))
{
   $page .= 'index.php';
}

header("Location: ". ROOT_URL . "/$page");
?>
