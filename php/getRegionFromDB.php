<?php
function get_region_from_db($page, $id)
{
   $page = mysql_real_escape_string($page);
   $id = mysql_real_escape_string($id);

   $query = "SELECT content FROM regions WHERE page = \"$page\" AND div_id = \"$id\" ORDER by id DESC LIMIT 1";
   $result = mysql_query($query);
   $row = mysql_fetch_row($result);
   echo stripslashes($row[0]);
}
?>
