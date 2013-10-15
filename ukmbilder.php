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
if(is_admin())
	add_action('admin_menu', 'UKMimages_menu');

## CREATE A MENU
function UKMimages_menu() {
	add_menu_page('UKM_images', 'Bilder', 'publish_posts', 'UKM_images','UKM_images', 'http://ico.ukm.no/photocamera-20.png', 11);
}

function UKM_images() {

	require_once('UKM/related.class.php');
	require_once('UKM/innslag.class.php');
	require_once('UKM/monstring.class.php');
	$monstring = new monstring(get_option('pl_id'));

	echo TWIG('hendelser.twig.html', array('program' => $monstring->forestillinger()), dirname(__FILE__));

}