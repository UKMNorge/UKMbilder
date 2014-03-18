<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

$running = new SQL("SELECT `id`,`timestamp`
					FROM `ukm_bilder`
					WHERE `pl_id` = '#pl_id'
					AND `season` = '#season'
					AND `status` = 'compressing'",
					array('pl_id' => get_option('pl_id'),
						  'season' => get_option('season')
						  )
				);
$res = $running->run();
if(mysql_num_rows($res) > 0) {
	$r = mysql_fetch_assoc( $res );
	$timeago = strtotime( $r['timestamp'] );
	$now = time();
	
	if( $_SERVER['REMOTE_ADDR'] == '81.0.146.162') {
		var_dump( $r );
		var_dump( $timeago );
		var_dump( $now );
		
		var_dump( $timeago - $now );
	}
	// Hvis convertert mer enn 6 minutter er det på tide å gi opp
#	if( ( $timeago - $now ) > 360 ) {
	if( ( $timeago - $now ) > 90 ) {
		$db_update = new SQLins('ukm_bilder', array('id' => $r['id']));
		$db_update->add('status', 'crash');
		$db_update->run();
	} else {
		die(json_encode(array('success' => true, 'reload' => false, 'message' => 'Already compressing one')));
	}
}

// SHOULD GO ON IF CRASH ON ONE FILE (IF TIMEAGO LAST CHANGE > 6 MIN)

$next = new SQL("SELECT *
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
	$db_update = new SQLins('ukm_bilder', array('id' => $r['id']));
	$db_update->add('status', 'compressing');
	$db_update->run();


	$SYNCFOLDER = UKMbilder_syncfolder();
	$path = $SYNCFOLDER . $r['filename'];
	
	$wp_upload_dir = wp_upload_dir();
	$wp_path = $wp_upload_dir['path'] .'/'. $r['filename'];
	
	// COMPRESS AND MOVE TO WORDPRESS UPLOAD DIR (wp_ins_att requirement)
	$image = new Imagick( $path );
	$imageprops = $image->getImageGeometry();
	
		// Find proportions
		$width = $height = 2048;
		if($imageprops['width'] > $imageprops['height']) {
			$height = 0;
			$compare = 'width';
		} else {
			$width = 0;
			$compare = 'height';
		}
	
	// IF IMAGE IS LARGER THAN TARGET, RESIZE
	if($imageprops[$compare] > $$compare) {
		$image->scaleImage($width, $height);
	}
	$image->writeImage($wp_path);
	
	/// WORDPRESS GENERATE ATTACHMENT AND THUMBS
	$wp_filetype = wp_check_filetype(basename($wp_path), null );
	$attachment = array(
			  	  'post_mime_type' => $wp_filetype['type'],
				  'post_title' => $r['filename'],
				  'post_content' => '',
				  'post_status' => 'inherit'
				);
	$attach_id = wp_insert_attachment( $attachment, $wp_path);
	// you must first include the image.php file
	// for the function wp_generate_attachment_metadata() to work
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	$attach_data = wp_generate_attachment_metadata( $attach_id, $wp_path );
	wp_update_attachment_metadata( $attach_id, $attach_data );
	
	
	$db_update = new SQLins('ukm_bilder', array('id' => $r['id']));
	$db_update->add('wp_post', $attach_id);
	$db_update->add('status', 'compressed');
	$db_update->add('url', wp_get_attachment_thumb_url($attach_id));
	$db_update->run();
	die(json_encode(array('success'=>true, 'reload' => $r['id'], 'message' => 'Image compressed')));
}

die(json_encode(array('success' => true, 'reload' => false, 'message' => 'Nothing to compress')));
