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
        var div_id = '<input type="hidden" name="div_id" value="' + id.identify() + '" />\n';
	var page_url = '<input type="hidden" name="page" value="' + document.location.href + '" />';
	var extra_info = div_id + page_url;
        $(id).insert({after: textarea});
        var form = new Element('form', {'enctype':'multipart/form-data', 'name':'submit_content', 'id':'editor_content', 'action':'php/test.php', 'method':'post'});
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

