#!/usr/bin/php
<?php
include("../php/sqlStrings.php");
include("../php/thirdparty/simple_html_dom.php");

#==================================================================
#add_region_to_db
#==================================================================
function add_region_to_db($page, $region)
{
   #get the id of the editable region using the simple_html_dom api
   $dom = str_get_html($region);
   $div = $dom->find('div', 0);
   $id = $div->id;

   #echo "*** page: $page, div id: $id ***\n";
   #echo trim($div->innertext) . "\n";
   #echo "******\n";

   #add page, div id, region content to the database
   $page = mysql_real_escape_string($page);
   $id = mysql_real_escape_string($id);
   $region = mysql_real_escape_string(trim($div->innertext));

   $query = "INSERT INTO regions (page, div_id, content) VALUES (\"$page\", \"$id\", \"$region\")";
   $result = @mysql_query($query);
   if(!result)
   {
      echo "query: $query\n";
      echo "mysql error:" . mysql_error();
   }
}

#==================================================================
#add_div_to_php
#==================================================================
function add_div_to_php($page, $region)
{
   global $php_page;

   #replace the inner html of the div with php code
   #that pulls the html from the database
   $dom = str_get_html($region);
   $div = $dom->find('div', 0);
   $id = $div->id;

   $div->innertext = "<?php get_region_from_db(\"$page\", \"$id\") ?>";

   #set div class="editor"
   $div->class = 'editor';

   #add to php_page
   $php_page .= $div;
}

#==================================================================
#insert_scripts
#==================================================================
function insert_scripts($dom)
{
   $head = $dom->find('head', 0);
   $head_html = $head->innertext;
   $head_html .= "\n";
   $head_html .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/editor.css\" />\n";
   $head_html .= "<script type=\"text/javascript\" src=\"js/thirdparty/prototype.js\"></script>\n";
   $head_html .= "<script type=\"text/javascript\" src=\"js/thirdparty/wysihat.js\"></script>\n";
   $head_html .= "<script type=\"text/javascript\" src=\"js/editor.js\"></script>\n";
   $head->innertext = $head_html;

   return explode("\n", $dom);
}

#==================================================================
#MAIN
#==================================================================
#parse through all files and subdirectories in the templates folder
$dir = "../templates/";

$inside_editable_region = false;
#FIXME: this relative directory for the include won't be correct
#       for websites with a directory structure
$editable_region = "";
$php_page = "<?php\ninclude(\"php/sqlStrings.php\");\ninclude(\"php/getRegionFromDB.php\");\n?>\n";

if (is_dir($dir))
{
   if ($dh = opendir($dir))
   {
      while (($file = readdir($dh)) !== false)
      {
         #echo "filename: $file : filetpye: " . filetype($dir . $file) . "<br/>\n";
	 if(filetype($dir . $file) == "file")
	 {
            #$html = file($dir . $file);
	    #add our scripts in the <head>
            $dom = file_get_html($dir . $file);
	    $html = insert_scripts($dom);
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
		  add_div_to_php($dir . $file, $editable_region);
		  $editable_region = "";
		  $inside_editable_region = false;
	       }
	       else if ($inside_editable_region)
	       {
                  $editable_region .= $line . "\n";
	       }
	       else
	       {
	          #add each line from html to the new php page
		  #except the editable regions
	          $php_page .= $line . "\n";
	       }
	    }
	    #FIXME: create proper file and directory structure
	    file_put_contents("../index.php", $php_page);
	 }
      }
      closedir($dh);
   }
}
?>
