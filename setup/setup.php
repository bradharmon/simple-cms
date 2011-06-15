#!/usr/bin/php
<?php
include("settings.php");
include("../php/sqlStrings.php");
include("../php/thirdparty/simple_html_dom.php");

#==================================================================
#add_region_to_db
#==================================================================
function add_region_to_db($page, $region)
{
   global $websiteBaseUrl;
   #get the id of the editable region using the simple_html_dom api
   $dom = str_get_html($region);
   $div = $dom->find('div', 0);
   $id = $div->id;

   #add page, div id, region content to the database
   $rel_page = preg_replace('/^\./', '', $page);  #remove the beginning '.'
   preg_match('/\w*:\/\/w*\.?[\w-_]*\.?[A-Za-z]*:?\d*\/(.*)/', $websiteBaseUrl . $rel_page, $rel_page);

   $page = mysql_real_escape_string($rel_page[1]);
   $id = mysql_real_escape_string($id);
   $region = mysql_real_escape_string(trim($div->innertext));

   $query = "INSERT INTO regions (date, page, div_id, content) VALUES (NOW(), \"$page\", \"$id\", \"$region\")";
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
   global $php_page, $websiteBaseUrl;

   $rel_page = preg_replace('/^\./', '', $page);  #remove the beginning '.'
   preg_match('/\w*:\/\/w*\.?[\w-_]*\.?[A-Za-z]*:?\d*\/(.*)/', $websiteBaseUrl . $rel_page, $rel_page);

   #replace the inner html of the div with php code
   #that pulls the html from the database
   $dom = str_get_html($region);
   $div = $dom->find('div', 0);
   $id = $div->id;

   $div->innertext = "<?php get_region_from_db(\"$rel_page[1]\", \"$id\") ?>";

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
#dir_to_array
#==================================================================
function dir_to_array($directory, $recursive = true) {
	$array_items = array();
	if ($handle = opendir($directory)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				if (is_dir($directory. "/" . $file)) {
					if($recursive) {
						$array_items = array_merge($array_items, dir_to_array($directory. "/" . $file, $recursive));
					}
					$file = $directory . "/" . $file;
					$array_items[] = preg_replace("/\/\//si", "/", $file);
				} else {
					$file = $directory . "/" . $file;
					$array_items[] = preg_replace("/\/\//si", "/", $file);
				}
			}
		}
		closedir($handle);
	}
	return $array_items;
}

#==================================================================
#make_new_dir
#==================================================================
function make_new_dir($new_dir)
{
   if(!is_dir($new_dir))
   {
      #make dir with permissions of 0755 and allow nested directory creation
      if(!mkdir($new_dir, 0755, true))
      {
         echo "could not create directory: $new_dir\n";
      }
   }
}

#==================================================================
#get_absolute_dir
#==================================================================
function get_absolute_dir($file)
{
   #Make the absolute dir from the website root directory
   #and the relative dir of the file inside the templates
   #dir
   global $websiteBaseDir;

   $rel_dir = preg_replace('/^\./', '', dirname($file));

   return $websiteBaseDir . $rel_dir;
}

#==================================================================
#get_absolute_path
#==================================================================
function get_absolute_path($file)
{
   #Make the absolute path from the website root directory
   #and the relative path of the file inside the templates
   #dir
   global $websiteBaseDir;

   $rel_path = preg_replace('/^\./', '', $file);

   return $websiteBaseDir . $rel_path;
}

#==================================================================
#php_page_header
#==================================================================
function php_page_header()
{
   #FIXME: this relative directory for the include won't be correct
   #       for websites with a directory structure
   return "<?php\ninclude(\"php/sqlStrings.php\");\ninclude(\"php/getRegionFromDB.php\");\n?>\n";
}

#==================================================================
#MAIN
#==================================================================
#parse through all files and subdirectories in the templates folder
$dir = "../templates/";
if (!chdir($dir))
{
   echo "could not change directories to the templates directory: $dir\n";
}
$inside_editable_region = false;
$editable_region = "";
$php_page = php_page_header();

if (is_dir('.'))
{
   if ($dh = opendir('.'))
   {
      $dir_tree = dir_to_array('.');
      foreach ($dir_tree as $file)
      {
         #echo "filename: $file : filetpye: " . filetype($file) . "<br/>\n";
	 if(filetype($file) == "file" && preg_match('/html$/', $file))
	 {
	    #add our scripts in the <head>
            $dom = file_get_html($file);
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
                  $new_file = preg_replace('/html$/', 'php', $file);
                  add_region_to_db($new_file, $editable_region);
		  add_div_to_php($new_file, $editable_region);
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
	    #write out new php files
	    $new_dir = get_absolute_dir($file);
            $new_file = get_absolute_path($file);
	    $new_file = preg_replace('/html$/', 'php', $new_file);
            make_new_dir($new_dir);
            if(!file_put_contents($new_file, $php_page))
            {
               echo "could not write file: " . $new_file;
            }
	    else
	    {
	       #clear out after successfully writing file
               $php_page = php_page_header();
	    }
	 }
	 #copy non-html file to correct location
	 else if (filetype($file) == "file" && !preg_match('/html$/', $file))
	 {
            $new_dir = get_absolute_dir($file);
            $new_file = get_absolute_path($file);
            $new_file = preg_replace('/html$/', 'php', $new_file);
            make_new_dir($new_dir);

            #echo "copy file: $file to $new_file\n";
            if(!copy($file, $new_file))
	    {
               echo "could not copy file: '$file' to new destination: $new_file\n";
	    }
	 }
      }
      closedir($dh);
   }
}
?>

