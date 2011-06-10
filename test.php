<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
</head>
<body>
<?php
 
include("includes/sqlStrings.php");
#include("includes/settings.php");

foreach($_POST as $key => $value)
{
   echo "$key => $value<br/>\n";
}

$page = mysql_real_escape_string("templates/index.html");
$div_id = mysql_real_escape_string($_POST['div_id']);
$content = mysql_real_escape_string($_POST['content']);
$query = "INSERT INTO regions (page, div_id, content) VALUES (\"$page\", \"$div_id\", \"$content\")";
$result = @mysql_query($query);
if(!$result)
{
   echo 'query: '.$query. '<br/>';
   echo 'mysql error: '. mysql_error();
}

?>	
</body>
</html>
