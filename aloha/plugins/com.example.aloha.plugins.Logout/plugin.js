/**
 * this is a plugin to add a logout button to the ribbon at the top of the page
 * @author laurin 
 */
if (typeof EXAMPLE == 'undefined' || !EXAMPLE) {
	var EXAMPLE = {};
}

EXAMPLE.Logout = new GENTICS.Aloha.Plugin('com.example.aloha.Logout');

/**
 * Configure the available languages
 */
EXAMPLE.Logout.languages = ['en'];

/**
 * Initialize the plugin and set initialize flag on true
 */
EXAMPLE.Logout.init = function () {

	// remember refernce to this class for callback
	var that = this;

	// create save button to ribbon
	var logoutButton = new GENTICS.Aloha.ui.Button({
		label : 'Logout',
		onclick : function() {
			that.logout();
		}
	});

	// add button to ribbon
	GENTICS.Aloha.Ribbon.addButton(logoutButton);

};

/**
 * logout
 */
EXAMPLE.Logout.logout = function () {
   //logout from the server
   $.post("simple-cms/login/logout.php", function(data){
      //alert(data);
   });
   //reload the page
   window.location = "simple-cms/login/logout.php?page="+document.location.href;
};
