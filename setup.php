#!/usr/bin/php
<?php
include("includes/sqlStrings.php");
include("includes/thirdparty/simple_html_dom.php");

#==================================================================
#add_region_to_db
#==================================================================
function add_region_to_db($page, $region)
{
   #get the id of the editable region using the simple_html_dom api
   $dom = str_get_html($region);
   $div = $dom->find('div',0);
   $id = $div->id;

   echo "*** page: $page, div id: $id ***\n";
   echo "$region";
   echo "******\n";

   #add page, div id, region content to the database
   $page = mysql_real_escape_string($page);
   $id = mysql_real_escape_string($id);
   $region = mysql_real_escape_string($region);

   $query = "INSERT INTO regions (page, div_id, content) VALUES (\"$page\", \"$id\", \"$region\")";
   $result = @mysql_query($query);
   if(!result)
   {
      echo "query: $query\n";
      echo "mysql error:" . mysql_error();
   }
}

#==================================================================
#MAIN
#==================================================================
#parse through all files and subdirectories in the templates folder
$dir = "templates/";

$inside_editable_region = false;
$editable_region = "";

if (is_dir($dir))
{
   if ($dh = opendir($dir))
   {
      while (($file = readdir($dh)) !== false)
      {
         #echo "filename: $file : filetpye: " . filetype($dir . $file) . "<br/>\n";
	 if(filetype($dir . $file) == "file")
	 {
            $html = file($dir . $file);
	    foreach($html as $line)
	    {
               #echo "$line";
               $tr_line = trim($line);
	       if ($tr_line == "<!--START EDITABLE REGION-->")
	       {
                  $inside_editable_region = true;
	       }
	       else if ($tr_line == "<!--END EDITABLE REGION-->")
	       {
                  add_region_to_db($dir . $file, $editable_region);
		  $editable_region = "";
		  $inside_editable_region = false;
	       }
	       else if ($inside_editable_region)
	       {
                  $editable_region .= $line;
	       }
	    }
	 }
      }
      closedir($dh);
   }
}
#$html = file("templates/index.html");
#for()
?>
