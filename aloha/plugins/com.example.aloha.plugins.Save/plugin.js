/*
* Aloha Editor
* Author & Copyright (c) 2010 Gentics Software GmbH
* aloha-sales@gentics.com
* Licensed unter the terms of http://www.aloha-editor.com/license.html
*/
if(typeof EXAMPLE=="undefined"||!EXAMPLE)
{
   var EXAMPLE={}
}
EXAMPLE.DummySavePlugin=new GENTICS.Aloha.Plugin("com.example.aloha.DummySave");
EXAMPLE.DummySavePlugin.languages=["en","de","fi","fr","it"];
EXAMPLE.DummySavePlugin.init=function(){
   var that=this;
   var saveButton=new GENTICS.Aloha.ui.Button({label:this.i18n("save"),onclick:function(){that.save()}});
   GENTICS.Aloha.Ribbon.addButton(saveButton)
};
EXAMPLE.DummySavePlugin.save=function(){
   //get page url
   var content="";
   var regions = {};
   regions["page"] = document.location.href;
   jQuery.each(GENTICS.Aloha.editables,function(index,editable){
      //content=content+"Editable ID: "+editable.getId()+"\nHTML code: "+editable.getContents()+"\n\n"
      regions[editable.getId().replace(/editor_/,"")] = editable.getContents();
   });
   //alert(regions);
   //var regions = {'div': 'data', 'div2' : 'data2'};
   //alert(this.i18n("saveMessage")+"\n\n"+content)
   $.post( "simple-cms/php/submit.php", regions, function(data){
      if($.trim(data) == "success")
      {
         //if data saved successfully to db, refresh page
	 alert("page saved successfully");
         window.location.reload();
      }
      else if($.trim(data) == "fail")
      {
         alert("FAILURE saving page " + document.location.href + "\nContact the site admin for more information.");
      }
   });
};

