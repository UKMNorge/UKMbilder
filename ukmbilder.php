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
add_action('wp_ajax_UKMbilder_upload', 'UKMbilder_upload');
add_action('wp_ajax_UKMbilder_tagme', 'UKMbilder_tagme');
add_action('wp_ajax_UKMbilder_innslag', 'UKMbilder_innslag');
add_action('wp_ajax_UKMbilder_compress', 'UKMbilder_compress');
add_action('wp_ajax_UKMbilder_do_tag', 'UKMbilder_do_tag');
add_action('wp_ajax_UKMbilder_band_images', 'UKMbilder_band_images');
add_action('wp_ajax_UKMbilder_image_reauthor', 'UKMbilder_image_reauthor');
add_action('wp_ajax_UKMbilder_image_move', 'UKMbilder_image_move');
add_action('wp_ajax_UKMbilder_image_delete', 'UKMbilder_image_delete');

if(is_admin()) {
	add_action('UKM_admin_menu', 'UKMimages_menu');
}
## CREATE A MENU
function UKMimages_menu() {
	UKM_add_menu_page('content', 'UKMbilder', 'Bilder', 'edit_posts', 'UKMbilder','UKMbilder', 'http://ico.ukm.no/photocamera-20.png', 1);
	UKM_add_scripts_and_styles('UKMbilder', 'UKMbilder_scripts_and_styles' );

}

function UKMbilder_upload() {
	require_once('ajax_uploaded_image.inc.php');
	die();
}

function UKMbilder_innslag() {
	require_once('ajax_program.inc.php');
	die();
}

function UKMbilder_tagme() {
	require_once('ajax_tagme.inc.php');
	die();
}

function UKMbilder_compress() {
	require_once('ajax_compress.inc.php');
	die();
}

function UKMbilder_do_tag() {
	require_once('ajax_do_tag.inc.php');
	die();
}

function UKMbilder_band_images() {
	require_once('ajax_band_images.inc.php');
	die();
}

function UKMbilder_image_reauthor(){
	require_once('ajax_image_reauthor.inc.php');
	die();
}

function UKMbilder_image_delete(){
	require_once('ajax_image_delete.inc.php');
	die();
}

function UKMbilder_image_move() {
	require_once('ajax_image_move.inc.php');
	die();
}

function UKMbilder_syncfolder() {
	return '/home/ukmno/private_sync/';
}

function UKMbilder_users() {
	global $blog_id;
	$current = get_current_user_id();
	$users = get_users( array('blog_id' => $blog_id) );
	
	$all_users = array();
	foreach($users as $user) {
		$all_users[] = array('name' => $user->user_nicename,
						   'id' => $user->ID,
						   'active' => $user->ID == $current);
	}
	return $all_users;
}

function UKMbilder_scripts_and_styles(){
	wp_enqueue_script('handlebars_js');
	wp_enqueue_script('bootstrap_js');
	wp_enqueue_style('bootstrap_css');

	wp_enqueue_style('UKMbilder_css', plugin_dir_url( __FILE__ ) . 'UKMbilder.css');
	wp_enqueue_script('UKMbilder_js', plugin_dir_url(__FILE__) . 'UKMbilder.js');
	
	wp_enqueue_style( 'jquery-ui-style', WP_PLUGIN_URL .'/UKMresources/css/jquery-ui-1.7.3.custom.css');
	
	wp_enqueue_script('jquery');
	wp_enqueue_script('jqueryGoogleUI', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js');



	wp_enqueue_style( 'blueimp-gallery-css', plugin_dir_url( __FILE__ ) . 'jqueryuploader/css/blueimp-gallery.min.css');


	// CSS to style the file input field as button and adjust the Bootstrap progress bars
	wp_enqueue_style( 'jquery-fileupload-css', plugin_dir_url( __FILE__ ) . 'jqueryuploader/css/jquery.fileupload.css');
	wp_enqueue_style( 'jquery-fileupload-ui-css', plugin_dir_url( __FILE__ ) . 'jqueryuploader/css/jquery.fileupload-ui.css');
	
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
	// The File Upload user interface plugin
	wp_enqueue_script('fileupload-ui', plugin_dir_url(__FILE__) . 'jqueryuploader/js/jquery.fileupload-ui.js');
	// The File Upload processing plugin
	wp_enqueue_script('fileupload-process', plugin_dir_url(__FILE__) . 'jqueryuploader/js/jquery.fileupload-process.js');	
	// The File Upload image preview & resize plugin 
	wp_enqueue_script('fileupload-image', plugin_dir_url(__FILE__) . 'jqueryuploader/js/jquery.fileupload-image.js');	
	// The File Upload validation plugin
	wp_enqueue_script('fileupload-validate', plugin_dir_url(__FILE__) . 'jqueryuploader/js/jquery.fileupload-validate.js');	
	

}

function UKMbilder() {
	if(!isset($_GET['action']))
		$_GET['action'] = 'upload';
		
	require_once('UKM/related.class.php');
	require_once('UKM/innslag.class.php');
	require_once('UKM/monstring.class.php');
	$monstring = new monstring(get_option('pl_id'));


	switch( $_GET['action'] ) {
		case 'upload':
			require_once('controller_upload.inc.php');
			break;
		case 'list':
			require_once('controller_list.inc.php');
			break;
	}
	$INFOS['active'] = $_GET['action'];
	echo TWIG($_GET['action'].'.twig.html', $INFOS, dirname(__FILE__));
}