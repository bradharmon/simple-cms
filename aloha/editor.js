GENTICS.Aloha.settings = {
	logLevels: {'error': true, 'warn': true, 'info': true, 'debug': false},
	errorhandling : false,
	ribbon: true,	
	"i18n": {
		// you can either let the system detect the users language (set acceptLanguage on server)
		// In PHP this would would be '<?=$_SERVER['HTTP_ACCEPT_LANGUAGE']?>' resulting in 
		// "acceptLanguage": 'de-de,de;q=0.8,it;q=0.6,en-us;q=0.7,en;q=0.2'
		// or set current on server side to be in sync with your backend system 
		"current": "en" 
	},
	"plugins": {
	 	"com.gentics.aloha.plugins.Format": {
                        // all elements with no specific configuration get this configuration
			config : [ 'b', 'i','sub','sup', 'p', 'title', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre', 'removeFormat'],
			editables : {
		  	}
		},
	 	"com.gentics.aloha.plugins.List": { 
		 	// all elements with no specific configuration get an UL, just for fun :)
			config : [ 'ul' ],
		  	editables : {
				// all divs get OL
				'div'		: [ 'ol' ], 
		  	}
		},
	 	"com.gentics.aloha.plugins.Link": {
		 	// all elements with no specific configuration may insert links
			config : [ 'a' ],
		  	cssclass : 'link',
		  	// use all resources of type website for autosuggest
		  	objectTypeFilter: ['website'],
		  	// handle change of href
		  	onHrefChange: function( obj, href, item ) {
			  	if ( item ) {
					jQuery(obj).attr('data-name', item.name);
			  	} else {
					jQuery(obj).removeAttr('data-name');
			  	}
		  	}
		},
	 	"com.example.aloha.plugins.Logout": {
			editables : {
		  	}
		},
  	}
};

$(document).ready(function() {
	$('.editor').aloha();	
});

