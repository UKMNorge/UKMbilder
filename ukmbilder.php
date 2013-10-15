<?php  
/* 
Plugin Name: UKMbilder
Plugin URI: http://www.ukm-norge.no
Description: Bildemodul for UKM Norge. Brukes til opplasting av bilder fra mønstringer
Author: UKM Norge / M Mandal 
Version: 3.0 
Author URI: http://www.ukm-norge.no
*/

## HOOK MENU
if(is_admin()) {
	add_action('admin_menu', 'UKMimages_menu');
}
## CREATE A MENU
function UKMimages_menu() {
	$page = add_menu_page('UKMbilder', 'Bilder', 'publish_posts', 'UKMbilder','UKMbilder', 'http://ico.ukm.no/photocamera-20.png', 11);
	add_action( 'admin_print_styles-' . $page, 'UKMbilder_scripts_and_styles' );

}

function UKMbilder_scripts_and_styles(){
	wp_enqueue_script('bootstrap_js');
	wp_enqueue_style('bootstrap_css');

	wp_enqueue_style('UKMbilder_css', plugin_dir_url( __FILE__ ) . 'UKMbilder.css');
	
	wp_enqueue_style( 'jquery-ui-style', WP_PLUGIN_URL .'/UKMresources/css/jquery-ui-1.7.3.custom.css');
	
	wp_enqueue_script('jquery');
	wp_enqueue_script('jqueryGoogleUI', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js');

	// The jQuery UI widget factory, can be omitted if jQuery UI is already included
	wp_enqueue_script('jquery_ui_widget', plugin_dir_url(__FILE__) . 'jqueryuploader/js/vendor/jquery.ui.widget.js');
	// The Load Image plugin is included for the preview images and image resizing functionality
	wp_enqueue_script('load-image', plugin_dir_url(__FILE__) . 'jqueryuploader/js/vendor/load-image.min.js');
	// The Canvas to Blob plugin is included for image resizing functionality
	wp_enqueue_script('canvas-to-blob', plugin_dir_url(__FILE__) . 'jqueryuploader/js/vendor/canvas-to-blob.min.js');
	// The Iframe Transport is required for browsers without support for XHR file uploads
	wp_enqueue_script('iframe-transport', plugin_dir_url(__FILE__) . 'jqueryuploader/js/jquery.iframe-transport.js');	
	// The basic File Upload plugin
	wp_enqueue_script('fileupload', plugin_dir_url(__FILE__) . 'jqueryuploader/js/jquery.fileupload.js');	
	// The File Upload processing plugin
	wp_enqueue_script('fileupload-process', plugin_dir_url(__FILE__) . 'jqueryuploader/js/jquery.fileupload-process.js');	
	// The File Upload image preview & resize plugin 
	wp_enqueue_script('fileupload-image', plugin_dir_url(__FILE__) . 'jqueryuploader/js/jquery.fileupload-image.js');	
	// The File Upload validation plugin
	wp_enqueue_script('fileupload-validate', plugin_dir_url(__FILE__) . 'jqueryuploader/js/jquery.fileupload-validate.js');	
}

function UKMbilder() {
	if(!isset($_GET['action']))
		$_GET['action'] = 'start';
		
	require_once('UKM/related.class.php');
	require_once('UKM/innslag.class.php');
	require_once('UKM/monstring.class.php');
	$monstring = new monstring(get_option('pl_id'));


	switch( $_GET['action'] ) {
		case 'start':
			require_once('controller_start.inc.php');
			echo TWIG('hendelser.twig.html', $INFOS, dirname(__FILE__));
			break;
		case 'upload':
			require_once('controller_upload.inc.php');
			echo TWIG('upload.twig.html', $INFOS, dirname(__FILE__));
			break;		
	}
}