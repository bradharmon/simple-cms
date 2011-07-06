<?php
#include('../settings.php');
include(CMS_DIR . '/login/session.php');

function get_region_from_db($page, $id)
{
   global $dbh;

   try {
      $sth = $dbh->prepare("SELECT content FROM regions WHERE page = ? AND div_id = ? ORDER by date DESC LIMIT 1");
      $sth->execute(array($page, $id, ));
      list($content) = $sth->fetch(PDO::FETCH_NUM);
   } catch (PDOException $e) {
         echo $e->getMessage();
   }

   #if we're logged in, display the editor
   if(isLoggedIn()){echo "<div class=\"editor\">\n";}

   echo $content;

   if(isLoggedIn()){echo "</div>";}
}
?>
