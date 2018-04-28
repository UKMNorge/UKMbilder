<?php

## BLOGS FOLDER
$path = explode('wp-content', dirname( __FILE__ ));
$path_wordpress = $path[0];
$path_blogs_dir = $path_wordpress.'wp-content/blogs.dir/';

## LOOP ALL FOLDERS
foreach( scandir( $path_blogs_dir ) as $blog_id ) {
	if( $blog_id == '.' || $blog_id == '..' || !is_numeric( $blog_id ) ) {
		continue;
	}	

	// ALLE ÅR MØNSTRINGEN HAR LASTET OPP DATA
	foreach( scandir( $path_blogs_dir . $blog_id .'/files/' ) as $year ) {
		if( !is_numeric( $year ) ) {
			continue;
		}

		// ALLE MÅNEDER PER ÅR
		foreach( scandir( $path_blogs_dir . $blog_id .'/files/'. $year ) as $month ) {
			if( $month == '.' || $month == '..' ) {
				continue;
			}

			// ALLE FILER DENNE MÅNEDEN
			foreach( scandir( $path_blogs_dir . $blog_id .'/files/'. $year .'/'. $month ) as $file ) {
				if( $file == '.' || $file == '..' ) {
					continue;
				}
				
				// HVIS ORIGINAL-FILEN ALLEREDE ER EN SYMLINK ER JOBBEN GJORT
				$full_path = $path_blogs_dir . $blog_id .'/files/'. $year .'/'. $month .'/';
				if( is_link( $full_path . $file ) && is_dir( readlink( $full_path . $file ) ) ) {
					echo 'SYMLINK: '. $full_path . $file .' -> '. readlink( $full_path . $file ) .'<br />';
					#unlink( $full_path . $file );
				}
			}
		}
	}
}