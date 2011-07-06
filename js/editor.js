var editor;
var editor_visible = false;

function save(){
  //the editor always copies the data to a <textarea> with id=wysihat_textarea before submitting
  $('wysihat_textarea').setValue(WysiHat.Formatting.getApplicationMarkupFrom(editor));
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

        var form = new Element('form', {'enctype':'multipart/form-data', 'name':'submit_content', 'id':'wysihat_form', 'action':'simple-cms/php/submit.php', 'method':'post'});

        var textarea = '<textarea id="wysihat_textarea" name="content" style="display:none;"></textarea>\n';
        var div_id = '<input type="hidden" name="div_id" value="' + id.up().identify() + '" />\n';
	var page_url = '<input type="hidden" name="page" value="' + document.location.href + '" />\n';
        var cancel = '<button onclick="location.reload(true);" type="button">Cancel</button>\n';
        var submit = '<input onclick="save();" type="submit" name="submit" value="Submit" />';

	var inner_form = textarea + div_id + page_url + cancel + submit;

	form.update(inner_form);
	$(id).insert({after: form});

	//if(id.up().getStyle('overflow') == 'hidden'){
	//   id.up().setStyle({overflow: scroll});
        //}
        
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

