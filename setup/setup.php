#!/usr/bin/php
<?php
include_once('../settings.php');
include_once(CMS_DIR . '/php/thirdparty/simple_html_dom.php');

#==================================================================
#add_region_to_db
#==================================================================
function add_region_to_db($page, $region)
{
   global $anon_num, $dbh;
   #get the id of the editable region using the simple_html_dom api
   $html = str_get_html($region);
   $div = $html->find('div', 0);
   $id = $div->id;
   #assign a unique id to the div if it doesn't have one
   if($id == "")
   {
      $id = "anonymous_element_$anon_num";
      $div->id = $id;
   }

   #add page, div id, region content to the database
   $rel_page = preg_replace('/^\./', '', $page);  #remove the beginning '.'
   preg_match('/\w*:\/\/w*\.?[\w-_]*\.?[A-Za-z]*:?\d*\/(.*)/', WEBSITE_URL . $rel_page, $rel_page);

   $page = $rel_page[1];
   $region = trim($div->innertext);
   try {
      $sth = $dbh->prepare("INSERT INTO regions (date, page, div_id, content) VALUES (NOW(), ?, ?, ?)");
      $sth->execute(array($page, $id, $region));
   } catch (PDOException $e) {
      echo __FILE__ . ": " . __LINE__ . "<br>\n";
      echo "page: '$page', id: '$id';, region: '$region'<br>\n";
      die();
      #echo $e->getMessage();
   }
}

#==================================================================
#add_div_to_php
#==================================================================
function add_div_to_php($page, $region)
{
   global $php_page, $anon_num;

   $rel_page = preg_replace('/^\./', '', $page);  #remove the beginning '.'
   preg_match('/\w*:\/\/w*\.?[\w-_]*\.?[A-Za-z]*:?\d*\/(.*)/', WEBSITE_URL . $rel_page, $rel_page);

   #replace the inner html of the div with php code
   #that pulls the html from the database
   $html = str_get_html($region);
   $div = $html->find('div', 0);
   $id = $div->id;
   #assign a unique id to the div if it doesn't have one
   if($id == "")
   {
      $id = "anonymous_element_$anon_num";
      $div->id = $id;
      $anon_num++;
   }

   $div->innertext = "<?php get_region_from_db(\"$rel_page[1]\", \"$id\") ?>";

   #set div class="editor"
   #$div->class = 'editor';

   #add to php_page
   $php_page .= $div;
}

#==================================================================
#insert_scripts
#==================================================================
function insert_scripts($html)
{
   $head = $html->find('head', 0);
   $head_html = $head->innertext;
   $head_html .= "\n";
   $head_html .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/editor.css\" />\n";
   $head_html .= "<script type=\"text/javascript\" src=\"js/thirdparty/prototype.js\"></script>\n";
   $head_html .= "<script type=\"text/javascript\" src=\"js/thirdparty/wysihat.js\"></script>\n";
   $head_html .= "<script type=\"text/javascript\" src=\"js/editor.js\"></script>\n";
   $head->innertext = $head_html;

   #return explode("\n", $html);
   return $html;
}

#==================================================================
#translate_links
#==================================================================
function translate_links($html, $file_list)
{
   #given the html and a list of pages on the website, if there is 
   #a link in the html that points to a page on the website, 
   #make sure the link ends in .php instead of .html

   #remove './' from beginning of file list
   $file_list = preg_replace('/^\.\//', '', $file_list);

   $links = $html->find('a');
   foreach($links as $element)
   {
      $link = $element->href;
      foreach($file_list as $file)
      {
         #if any file in the tree matches a link
	 #change the link's extension to .php
         if(false !== strpos($link, $file))
	 {
            $element->href = preg_replace('/html$/', 'php', $link);
	    break;
	 }
      }
   }

   return $html;
}
#==================================================================
#dir_to_array
#==================================================================
function dir_to_array($directory) {
	$array_items = array();
	if ($handle = opendir($directory)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				if (is_dir($directory. "/" . $file)) {
					$array_items = array_merge($array_items, dir_to_array($directory. "/" . $file));
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
   #take off leading '..' or '.'
   $rel_dir = preg_replace('/^\.\./', '', dirname($file));
   $rel_dir = preg_replace('/^\./', '', $rel_dir);

   #Make the absolute dir from the website root directory
   #and the relative dir of the file inside the templates
   #dir
   return WEBSITE_DIR . $rel_dir;
}

#==================================================================
#get_absolute_path
#==================================================================
function get_absolute_path($file)
{
   #take off leading '..' or '.'
   $rel_path = preg_replace('/^\.\./', '', $file);
   $rel_path = preg_replace('/^\./', '', $rel_path);

   #Make the absolute path from the website root directory
   #and the relative path of the file inside the templates
   #dir
   return WEBSITE_DIR . $rel_path;
}

#==================================================================
#php_page_header
#==================================================================
function php_page_header()
{
   return "<?php
   session_start();

   include(\"". CMS_DIR ."/settings.php\");
   include(\"". CMS_DIR ."/php/getRegionFromDB.php\");
?>";
}

#==================================================================
#MAIN
#==================================================================

#globals
$anon_num = 1; #used to generate a unique div id for div's witout one

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
      //get css and js directories as well
      $css_tree = dir_to_array('../css');
      $js_tree = dir_to_array('../js');
      $dir_tree = array_merge($dir_tree, $css_tree, $js_tree);
      foreach ($dir_tree as $file)
      {
	 //if this is a file with a .html extension
	 if(filetype($file) == "file" && preg_match('/html$/', $file))
	 {
	    #add our scripts in the <head>
            $html = file_get_html($file);
	    $html = insert_scripts($html);
	    $html = translate_links($html, $dir_tree);
            $html = explode("\n", $html);
	    foreach($html as $line)
	    {
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
            make_new_dir($new_dir);

            #echo "copy file: $file to $new_file<br>\n";
            if(!copy($file, $new_file))
	    {
               echo "could not copy file: '$file' to new destination: $new_file<br>\n";
	    }
	 }
      }
      closedir($dh);
   }
}
?>

