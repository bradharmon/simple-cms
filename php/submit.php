<?php
session_start();

include('../settings.php');
include(CMS_DIR . '/login/session.php');

if(isLoggedIn())
{
   //foreach($_POST as $key => $val)
   //{
   //   echo "$key:\n";
   //   echo "$val\n\n";
   //}
   //exit;

   #regex that matches everything after the domain name
   preg_match('/\w*:\/\/w*\.?[\w-_]*\.?[A-Za-z]*:?\d*\/(.*)/', $_POST['page'], $page);
   $page = $page[1];

   #append index.php as default if a php file isn't specified
   if(!preg_match('/\.php$/', $page))
   {
      $page .= 'index.php';
   }
   
   #iterate over each region posted for saving to the db
   foreach($_POST as $id => $content)
   {
      //echo "$id\n";
      //echo "$content\n";
      if ($id === "page"){ continue; }
      try {
         #check to make sure the page/region are in the db
         $sth = $dbh->prepare("SELECT id FROM regions WHERE page = ?");
         $sth->execute(array($page));
         if($sth->rowCount())
         {
	    echo "page exists\n";
            #check to see if same content exists before inserting
            $sth = $dbh->prepare("SELECT id FROM regions WHERE page = ? AND div_id = ? AND content = ?");
            $sth->execute(array($page, $id, $content));
            if($sth->rowCount())
            {
	       echo "same content exists\n";
               #if content already exists, moving the date to current forces the
               #older content to be displayed
               $sth = $dbh->prepare("UPDATE regions SET date = NOW() where page = ? AND div_id = ? AND content = ?");
               $sth->execute(array($page, $id, $content));
            }
            else
            {
	       echo "saving content\n";
               $sth = $dbh->prepare("INSERT INTO regions (date, page, div_id, content) VALUES(NOW(), ?, ?, ?)");
               $sth->execute(array($page, $id, $content));
	       echo "content saved\n";
            }
         }
         else
         {
            #page/region isn't in db - probably someone messing with the system
            #silently ignore for now
            #echo "page: '$page'<br>\n";
            #echo "div_id: '$div_id'<br>\n";
            #echo "content: '$content'<br>\n";
         }
      } catch(PDOException $e) {
         echo $e->getMessage();
      }
   }
}
   
#don't redirect to $_POST['page'], as this is a security risk
#where a link could be constructed that forwards to any page
#header("Location: ". ROOT_URL . "/$page");
?>	
