<?php
include('../settings.php');
include(CMS_DIR . '/login/random_dot_org.php');

function createSalt()
{
   global $dbh;

   #get a random string from random.org
   $true_random = random_dot_org();

   #random number from mysql
   $sth = $dbh->query("SELECT rand() AS rand");
   list($dbRand) = $sth->fetch(PDO::FETCH_NUM);

   $time = microtime();

   $rand = mt_rand();

   #salt is generated from all of the above randomness
   return hash('sha256', $rand_dot_org . $dbRand . $time . $rand);
}
?>
