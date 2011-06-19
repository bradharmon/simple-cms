<?php
include("sqlStrings.php");

#foreach($_POST as $key => $value)
#{
#   echo "$key => $value<br/>\n";
#}

#regex that matches everything after the domain name
preg_match('/\w*:\/\/w*\.?[\w-_]*\.?[A-Za-z]*:?\d*\/(.*)/', $_POST['page'], $page);
#echo "regex match: $page[1]";
$page = mysql_real_escape_string($page[1]);
$div_id = mysql_real_escape_string($_POST['div_id']);
$content = mysql_real_escape_string($_POST['content']);

#make sure the page we want to update is already in the database
if(!mysql_num_rows(mysql_query("SELECT id FROM regions WHERE page = \"$page\"")))
{
   #echo "<br /><br />The page you are trying to update does not exist! Database NOT updated!<br />";
   #echo "page: $page<br />";
}
#check to see if content exists before inserting
else if(mysql_num_rows(mysql_query("SELECT id FROM regions WHERE page = \"$page\" AND content = \"$content\"")))
{
   $query = "UPDATE regions SET date = NOW() where page = \"$page\" AND content = \"$content\"";
   $result = @mysql_query($query);
}
else
{
   $query = "INSERT INTO regions (date, page, div_id, content) VALUES (NOW(), \"$page\", \"$div_id\", \"$content\")";
   $result = @mysql_query($query);
   if(!$result)
   {
      #echo 'query: '.$query. '<br/>';
      #echo 'mysql error: '. mysql_error();
   }
}

#redirect to the page that was just submitted
header("Location: " . $_POST['page']);
?>	
