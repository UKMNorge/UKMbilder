<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

require_once('UKM/sql.class.php');
require_once('UKM/innslag.class.php');

$innslag = new innslag($_POST['new_b_id']);

if((int)$innslag->g('b_id') == 0)
	die(json_encode(array('success' => false, 'message' => 'Did not find moveto band')));

foreach($_POST['images'] as $post_id) {
	if((int)$post_id == 0)
		continue;
		
	// UPDATE WORDPRESS
	global $wpdb;
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	require_once(ABSPATH . 'wp-includes/post.php');
	$wpdb->update($wpdb->posts,
				  array('post_title' => $innslag->get('b_name')),
			      array('ID' => $post_id)
				 );

	global $blog_id;
	// UPDATE UKM_BILDER
	$sql = new SQLins('ukm_bilder', array('wp_post' => $post_id, 'wp_blog' => $blog_id));
	$sql->add('b_id', $innslag->g('b_id'));
	$sql->run();

	// UPDATE WP_RELATED
	$sql = new SQLins('ukmno_wp_related', array('post_id' => $post_id,
												'blog_id' => $blog_id,
												'post_type' => 'image'));
	$sql->add('b_id', $innslag->g('b_id'));
	$sql->run();
}
die(json_encode(array('success' => true, 'b_id' => $_POST['b_id'])));