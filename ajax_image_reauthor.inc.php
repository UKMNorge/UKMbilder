<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

require_once('UKM/sql.class.php');

$photo_by = $_POST['author'];

if((int)$photo_by == 0)
	die(json_encode(array('success' => false, 'message' => 'Missing author')));

foreach($_POST['images'] as $post_id) {
	if((int)$post_id == 0)
		continue;
	// UPDATE WORDPRESS
	global $wpdb;
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	require_once(ABSPATH . 'wp-includes/post.php');
#	$wpdb->update($wpdb->posts,
#				  array('post_author' => $photo_by),
#			      array('ID' => $post_id)
#				 );
	// UPDATE UKM_BILDER
	$sql = new SQLins('ukm_bilder', array('wp_post' => $post_id));
	$sql->add('wp_uid', $photo_by);
	echo $sql->debug();
//	$sql->run();
	
	// UPDATE RELATED TABLE
	$related = new SQL("SELECT *
						FROM `ukmno_wp_related`
						WHERE `post_id` = '#post_id'
						AND `blog_id` = '#blog_id'
						AND `post_type` = 'image'",
						array('post_id' => $post_id,
							  'blog_id' => $blog_id)
					   );
	$related = $related->run('array');
	
	$post_meta = unserialize($related['post_meta']);
	$post_meta['author'] = $photo_by;
	$post_meta = serialize($post_meta);
	
	$update_related = new SQLins('ukmno_wp_related', array('rel_id' => $related['rel_id']);
	$update_related->add('post_meta' => $post_meta);
	echo $update_related->debug();
//	$update_related->run();
}
die(json_encode(array('success' => true, 'b_id' => $post_meta['b_id'])));