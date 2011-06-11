<?php
include("php/sqlStrings.php");

function get_region_from_db($page, $id)
{
   $page = mysql_real_escape_string($page);
   $id = mysql_real_escape_string($id);

   $query = "SELECT content FROM regions WHERE page = \"$page\" AND div_id = \"$id\" ORDER by id DESC LIMIT 1";
   $result = mysql_query($query);
   $row = mysql_fetch_row($result);
   return $row[0];
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>WYSIWYG</title>

  <link rel="stylesheet" type="text/css" href="css/editor.css" />

  <script type="text/javascript" src="js/thirdparty/prototype.js"></script>
  <script type="text/javascript" src="js/thirdparty/wysihat.js"></script>
  <script type="text/javascript" src="js/editor.js"></script>
</head>

<body>
<?php
    echo '<div id="div1" class="editor">'. get_region_from_db("templates/index.html", "div1") .'</div>';
    echo '<div id="div2">div2 text</div>';
    echo '<div id="div3" class="editor">'. get_region_from_db("templates/index.html", "div3") .'</div>';
?>
</body>
</html>

