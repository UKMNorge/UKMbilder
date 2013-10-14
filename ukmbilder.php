<?php  
/* 
Plugin Name: UKMimages
Plugin URI: http://www.ukm-norge.no
Description: UKM Norge toolkit
Author: UKM Norge / M Mandal 
Version: 2.0 
Author URI: http://www.ukm-norge.no
*/

## HOOK MENU
if(is_admin())
	add_action('admin_menu', 'UKMimages_menu');

$UKMN['me'] = '/wp-content/plugins/UKMNorge/';
## CREATE A MENU
function UKMimages_menu() {
	global $UKMN;
	add_menu_page('UKM_images', 'Bilder', 'publish_posts', 'UKM_images','UKM_images', 'http://ico.ukm.no/photocamera-20.png', 11);
}

function UKM_images() {
	$test = get_option( 'pl_id' );
	if(!$test)
		die('<div style="margin: 20px;"><strong>Beklager, nettsiden din har blitt satt opp feil. Kontakt UKM Norge</strong></div>');
	
	require_once('UKM/inc/toolkit.inc.php');
	require_once('UKM/related.class.php');
	require_once('UKM/innslag.class.php');
/* 	UKM_loader('toolkit|api/ukmAPI|api/related.class'); */
	require_once( 'controller.php' );
	
	$controller = $_GET['c']; // The Controller
	$action 	= $_GET['a']; // Action (refers to method in controller class)
	
	if( ! strlen( $controller ) > 0 )
		$controller = 'default';
	
	if( ! class_exists( $controller ) )
		require_once( 'controllers/' . $controller . '.php' );
	
	$cName = ucfirst( $controller ) . 'Controller'; // Controller name
	$c = new $cName();
	
	$c->prerender();
	
	if( strlen( $action ) > 0 ) // Action is set
		$aName = strtolower( $action ) . 'Action';
		
	if( method_exists( $c, $aName ) ) // Check if method exists in controller
		$c->$aName(); // Run action
	
	$c->render();
	
}

require_once( 'ajax_upload.php' );