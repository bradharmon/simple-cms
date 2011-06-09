<?php
include("sqlStrings.php");
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
$query = "SELECT content FROM regions WHERE page = \"editor\" AND div_id = \"div1\" ORDER by id DESC LIMIT 1";
$result = mysql_query($query);
$row = mysql_fetch_row($result);
$div1 = $row[0];
$query = "SELECT content FROM regions WHERE page = \"editor\" AND div_id = \"div3\" ORDER by id DESC LIMIT 1";
$result = mysql_query($query);
$row = mysql_fetch_row($result);
$div3 = $row[0];
    echo '<div id="div1" class="editor">'.$div1.'</div>';
    echo '<div id="div2">div2 text</div>';
    echo '<div id="div3" class="editor">'.$div3.'</div>';
?>
</body>
</html>

