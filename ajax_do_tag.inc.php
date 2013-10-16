<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

require_once('UKM/sql.class.php');
require_once('UKM/innslag.class.php');
require_once('UKM/related.class.php');

$innslag = new innslag($_GET['band'], false);
$PHOTO_BY_WP_UID = get_current_user_id();


foreach($_POST['image'] as $id) {
	$sql = new SQL("SELECT `wp_post`
					FROM `ukm_bilder`
					WHERE `id` = '#id'",
					array('id' => $id));
	$image = $sql->run('array');
	
	// UPDATE WORDPRESS IMAGE NAMES
	global $wpdb;
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	require_once(ABSPATH . 'wp-includes/post.php');
	
	
	update_post_meta($image['wp_post'], '_wp_attachment_image_alt', $innslag->get('b_name'));
	$wpdb->update($wpdb->posts,
			      array('post_author'	=> $PHOTO_BY_WP_UID,
				  		'post_title'	=> $innslag->get('b_name')),
			      array('ID' => $image['wp_post'])
				 );
	
	// UPDATE BILDER-TABLE
	$update = new SQLins('ukm_bilder', array('id' => $id));
	$update->add('wp_uid', $PHOTO_BY_WP_UID );
	$update->add('b_id', $innslag->get('b_id'));
	$update->add('status', 'tagged');
	$update->run();
	
	// RELATE IMAGE
	$meta = wp_get_attachment_metadata( $image['wp_post'] );
	$folder = substr($meta['file'],0,strrpos($meta['file'],'/')+1);
	foreach($meta['sizes'] as $size => $info)
		$meta['sizes'][$size]['file'] = $folder.$meta['sizes'][$size]['file'];
	
	$rel = new related($innslag->get('b_id'));
	$rel->set( $image['wp_post'],
			   'image',
			   array('file'		=> $meta['file'],
					 'sizes'	=> $meta['sizes'],
					 'author'	=> $PHOTO_BY_WP_UID )
			 );
}
die(json_encode(array('success' => true)));