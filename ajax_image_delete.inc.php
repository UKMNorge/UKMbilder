<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

require_once('UKM/sql.class.php');
require_once('UKM/innslag.class.php');
require_once('UKM/related.class.php');

$innslag = new innslag($_POST['b_id']);

if((int)$innslag->g('b_id') == 0)
	die(json_encode(array('success' => false, 'message' => 'Did not find image owner (band)')));


$deleted = 0;
foreach($_POST['images'] as $post_id) {
	if((int)$post_id == 0)
		continue;

	$deleted++;
	// DELETE FROM WORDPRESS
	wp_delete_post($post_id, true);
	error_log('UKMBILDER_DELETE_POST: '. $post_id);
	
	global $blog_id;
	
	// DELETE FROM RELATED
	$related = new related($_POST['b_id']);
	$related->delete($post_id, 'image');
	error_log('UKMBILDER_DELETE_RELATED_IMAGE: B_ID:'. $_POST['b_id'] .' P_ID:'. $post_id);
}

if( $deleted == 0 ) {
	die(json_encode(array('success'=>false, 'b_id'=>$_POST['b_id'])));
}

die(json_encode(array('success' => true, 'b_id' => $_POST['b_id'], 'count' => sizeof($_POST['images']))));