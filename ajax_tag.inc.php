	private function __wpAttUpdate($wpattach, $b_name, $post_author = '') {
		global $wpdb;
		
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		require_once(ABSPATH . 'wp-includes/post.php');
		
		if ($post_author != '' && is_numeric($post_author))
			$arrUpdate['post_author'] = $post_author;
		
		$arrUpdate['post_title'] = $b_name; // Array of key(col) => val(value to update to)
		
		update_post_meta($wpattatch, '_wp_attachment_image_alt', $b_name);
		$wpdb->update($wpdb->posts, // Table
				      $arrUpdate, // Array of key(col) => val(value to update to)
				      array('ID' => $wpattach) // Where
					 );
	}