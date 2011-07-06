<?php
session_start();

include('../settings.php');
include(CMS_DIR . '/login/session.php');

//retrieve our data from GET
$verification_hash = $_GET['code'];
$email = $_GET['email'];

try {
   //set user as verified
   $sth = $dbh->prepare("UPDATE users SET verified = TRUE WHERE verification_hash = ? AND email = ?");
   $sth->execute(array($verification_hash, $email));

   if($sth->rowCount())
   {
      //clear out verification hash
      $sth = $dbh->prepare("UPDATE users SET verification_hash = '' WHERE email = ?");
      $sth->execute(array($email));
   }
   else
      push_error("Invalid verification link.");
   die_on_error("register.php");
} catch (PDOException $e) {
   echo $e->getMessage();
   die();
}

push_status("REGISTRATION SUCCESSFUL!");
header('Location: login.php');
?>
