// How to use $ sign as an alias for jQuery object
/*(function($){
	$(document).ready(function(){
		var text = $('h1').text();
		alert(text);
	})
})(jQuery);*/

/*
global $user;
drupal_add_js(array('interact' => array('username' => $user->name)), 'setting'); // using module name (interact)
as the key of our array
drupal_add_js('alert(Drupal.settings.interact.username);', array('type' => 'inline', 'scope' => 'footer'));
*/

/*
drupal_add_library('interact', 'corner'); // first param module name and second is library name
drupal_add_js('jQuery(".roundme").corner();', array('type' => 'inline', 'scope' => 'footer'));

$build = array(
	'#type' => 'markup',
	'#markup' => '<div class="roundme">This is some text in the rounded corners.</div>'
);
return $build;


Implements hook_library().
function interact_library(){
	$libraries['corner'] = array(
		'title' => 'jQuery Corner',
		'website' => 'http://jquery.malsup.com/corner/',
		'version' => '2.11',
		'js' => array(
			drupal_get_path('module', 'interact'). '/jquery.corner.js' => array(),
		)
	);

	return $libraries;
}

Implements hook_library_alter().

function interact_library_alter(&$libraries, $modules){
	return;
}
*/

/**
  * Special bits to take note of:
  * - Code isassigned to an object in Drupal behaviors.
  * - Code that add behaviors is wrapped in the 'attach''property.
  * - Anything that has been attached to should get a new CSS class.
  * - Run Drupal.attachBehaviors() to attach beahviors to new HTML.
*/

(function($){

	Drupal.behaviors.interact = { // Drupal.beahviors are the existing variables and we are adding a new item to it
		// and that new item should be the name of our module and we are assigning a configuration to it.
		attach: function(context, settings){ // so attach is the key in configuration so we are assign a function to attach key
			// a function that takes two parameters context can contain a pointer to the elements added to the dom

			// Fadding in and out.
			$('h1:not(.interact-processed)').each(function(){
				$(this).addClass('interact-processed');
				$(this).hover(
					function(){
						$('.region-content').fadeOut(1000);
					},
					function (){
						$('.region-content').fadeIn(1000);
					}
				);
			});

			// What happens when you click on a paragraph after attaching behaviors? can you fix that?
			$('p').click(function(){
				$('h1').after('<h1>This is a new heading</h1>');
				Drupal.attachBehaviors(); // after adding this line now the content will fadeout with the new h1 added after
				// to add the behavior only to the context of the content(<h1>This is a new heading</h1>) just added
			});
		}
	}

})(jQuery);

/*
How to use Ajax in Drupal

first step to add ajax
drupal_add_js('misc/ajax.js'); // if this func drupal_add_js('misc/ajax.js'); is called in many other modules
so it will only be loaded only once in the final rendered file
second step is to define a place/container where chnage will occur
$ajax_link = '<p>'. l(t('Click me to replace content above', 'interact/ajax-callback/nojs/'. array('attributes' => array('class' => array('use-ajax')))). '</p>';
$build = array(
	'ajax_example' => array(
		'#type' => 'markup',
		'#markup' => '<div id="changeme">This is some text that should be changed.</div><div class="roundme" style="background:silver; padding:20px;">
		This is some text in a box with rounded corners.</div>'. $ajax_link,
	),
);

The ajax library will change nojs to read ajax
if js is enabled then ajax will pass as first param
interact/ajax-callback/ajax/ 
if not then nojs will pass as first param
interact/ajax-callback/nojs/

Now create menu callback

$items['interact/ajax-callback'] = array(
	'title' => 'This is a callback function for an ajax page',
	'description' => 'Callback function for an ajax page',
	'page callback' => 'interact_ajax_callback',
	'theme callback' => 'ajax_base_page_theme', // sets base theme to be teh same as current page.
	// ajax_base_page_theme actually the js and css returned with ajax response is also added to the page
	'access arguments' => array('access content'),
	'type' => MENU_CALLBACK,
);

return $items;

An ajax callback which will load some new text for the heading tag.

function interact_ajax_callback($type = 'ajax'){ // pass this param to distinguish between ajax callbacks and non ajax
	//callbacks this is actually the first param passed in url
	
	if($type == 'ajax'){
		$commands = array();
		$commands[] = ajax_command_html('#changeme', '<strong>This is some new content.</strong>');
		$page = array('#type' => 'ajax', '#commands' => $commands);
		ajax_deliver($page);
		// The idea of commands is that we will put all our actions in this array and then we will perform a series of 
		//actions
	}
	else{
		$output = t("This is what would be seen if Javascript is not enabled.");
		return $output;
	}
}

*/