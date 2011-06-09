<?php
include("sqlStrings.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title>WYSIWYG</title>

  <style type="text/css" media="screen">
    textarea {
      width: 100%;
      height: 100px;
    }

    .editor {
      clear: both;
      min-height: 100px;
      margin: 5px 0;
      padding: 5px;
      border: 3px solid red;
      outline: none;
      font-family: helvetica;
      font-size: 12px;
    }

    .editor p {
      margin: 0;
    }

    .editor_toolbar .button {
      float: left;
      margin: 2px 5px;
    }

    .editor_toolbar a {
      text-decoration: none;
    }

    .editor_toolbar .selected {
      color: red !important;
    }

    h3 {
      font-family: verdana;
      font-weight: bold;
      font-size: 10px;
      color: #333;
    }
  </style>

  <script type="text/javascript" src="js/prototype.js"></script>
  <script type="text/javascript" src="js/wysihat.js"></script>

  <script type="text/javascript" charset="utf-8">
    var editor;
    var editor_visible = false;

    function save(){
      //the editor always copies the data to a <textarea> with id=content before submitting
      $('content').setValue(WysiHat.Formatting.getApplicationMarkupFrom(editor));
      //submit
      document.submit_content.submit();
    }

    WysiHat.Commands.promptLinkSelection = function() {
      if (this.linkSelected()) {
        if (confirm("Remove link?"))
          this.unlinkSelection();
      } else {
        var value = prompt("Enter a URL", "http://www.google.com/");
        if (value)
          this.linkSelection(value);
      }
    }

    document.observe("dom:loaded", function() {
      var regions = $$('.editor');
      regions.each(function(id) {
        id.observe('click', function(event){
          if(!editor_visible){
            editor = $(id);
	    editor.contentEditable = true;
	    
	    //create form around current div
	    var textarea = '<textarea id="content" name="content" style="display:none;"></textarea>';
	    var extra_info = '<input type="hidden" name="div_id" value="' + id.identify() + '" />';
	    $(id).insert({after: textarea});
	    var form = new Element('form', {'enctype':'multipart/form-data', 'name':'submit_content', 'id':'editor_content', 'action':'test.php', 'method':'post'});
	    Element.wrap($('content'), form);
	    $('content').insert({after: extra_info});
	    var cancel = '<button onclick="location.reload(true);" type="button">Cancel</button>';
	    var submit = '<input onclick="save();" type="submit" name="submit" value="Submit" />';
	    form.insert({after: cancel});
	    form.insert({after: submit});

	    //add wysiHat stuff
            Object.extend(editor, WysiHat.Commands);
            var toolbar = new WysiHat.Toolbar(editor);
            toolbar.addButtonSet(WysiHat.Toolbar.ButtonSets.Basic);
	    //add link button to toolbar
            toolbar.addButton({
              label: "Link",
              handler: function(editor) { return editor.promptLinkSelection(); }
            });

            /*
	    //what to do when we paste stuff...
            editor.observe("paste", function(event) {
              (function() {
                // window.getSelection().setBookmark();
                // editor.innerHTML = editor.innerHTML;
                // window.getSelection().moveToBookmark();
              }).defer();
            });
	    */

	    editor_visible = true;
          } //editor_visible
        }); //id.observe
      });  //regions.each
    }); //dom:loaded
  </script>
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

