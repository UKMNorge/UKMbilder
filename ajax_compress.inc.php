<?php
$running = new SQL("SELECT `id`
					FROM `ukm_bilder`
					WHERE `pl_id` = '#pl_id'
					AND `season` = '#season'
					AND `status` = 'compressing'",
					array('pl_id' => get_option('pl_id'),
						  'season' => get_option('season')
						  )
				);
$res = $running->run();
if(mysql_num_rows($res) != 0) {
	die(json_encode(array('success' => true, 'update' => false)));
}

// SHOULD GO ON IF CRASH ON ONE FILE (IF TIMEAGO LAST CHANGE > 6 MIN)

$next = new SQL("SELECT `id`
				FROM `ukm_bilder`
				WHERE `pl_id` = '#pl_id'
				AND `season` = '#season'
				AND `status` = 'uploaded'
				ORDER BY `id` ASC
				LIMIT 1",
				array('pl_id' => get_option('pl_id'),
					  'season' => get_option('season')
					  )
			);
$res = $next->run();
while($r = mysql_fetch_assoc($res)) {
	/// WORDPRESS GENERATE ATTACHMENT AND THUMBS
	$wp_filetype = wp_check_filetype(basename($path), null );
	$attachment = array(
			  	  'post_mime_type' => $wp_filetype['type'],
				  'post_title' => preg_replace('/\.[^.]+$/', '', basename($path)),
				  'post_content' => '',
				  'post_status' => 'inherit'
				);
	$attach_id = wp_insert_attachment( $attachment, $path);
	// you must first include the image.php file
	// for the function wp_generate_attachment_metadata() to work
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	$attach_data = wp_generate_attachment_metadata( $attach_id, $path );
	wp_update_attachment_metadata( $attach_id, $attach_data );
	/// EOWP-GEN 
	return $attach_id;

}


