<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

require_once('UKM/innslag.class.php');
require_once('UKM/monstring.class.php');

$monstring = new monstring(get_option('pl_id'));
$alle_innslag = $monstring->innslag();

$innslag = new innslag($_POST['band']);
$related = $innslag->related_items();

if(!isset($related['image']))
	$related['image'] = array();

$images = array();	
foreach($related['image'] as $key => $image) {
	$image['thumb'] = $image['blog_url'].'/files/'.$image['post_meta']['sizes']['thumbnail']['file'];
	$images[] = $image;
}

die(
	json_encode(	array('images' => $images, 
						  'b_id' => $innslag->get('b_id'), 
						  'alle_innslag' => $alle_innslag, 
						  'users' => UKMbilder_users()
						 )
				)
	);