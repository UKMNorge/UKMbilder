<?php
use Kunnu\Dropbox\DropboxFile;
use GuzzleHttp\Exception\ClientException;

// REGEXP TO ENSURE UTF-8
$regex = <<<'END'
/
  (
    (?: [\x00-\x7F]                 # single-byte sequences   0xxxxxxx
    |   [\xC0-\xDF][\x80-\xBF]      # double-byte sequences   110xxxxx 10xxxxxx
    |   [\xE0-\xEF][\x80-\xBF]{2}   # triple-byte sequences   1110xxxx 10xxxxxx * 2
    |   [\xF0-\xF7][\x80-\xBF]{3}   # quadruple-byte sequence 11110xxx 10xxxxxx * 3 
    ){1,100}                        # ...one or more times
  )
| .                                 # anything else
/x
END;

define('TIME_LIMIT', 45);
ini_set('max_execution_time', round(TIME_LIMIT*1.2));
define('TIME_START', microtime(true));

@session_start();
require_once('UKMconfig.inc.php');
require_once('UKM/inc/dropbox.inc.php');
require_once('UKM/monstring.class.php');

## BLOGS FOLDER
$path = explode('wp-content', dirname( __FILE__ ));
$path_wordpress = $path[0];
$path_blogs_dir = $path_wordpress.'wp-content/blogs.dir/';

## ORIGINAL FILE PATTERN
$pattern_original_or_not = "/(.*)-([0-9]{2,4})x([0-9]{2,4}).([a-zA-Z]{2,4})/";

if( !file_exists( $path_blogs_dir .'OLD' ) ) {
	mkdir( $path_blogs_dir .'OLD/' );
}

## DATABASE
#define( 'SHORTINIT', true );
require( $path_wordpress .'wp-load.php' );
global $wpdb;

$dropbox = [];
$symlink = [];
$images = [];
$monstringer = [];

## LOOP ALL FOLDERS
foreach( scandir( $path_blogs_dir ) as $blog_id ) {
	if( $blog_id == '.' || $blog_id == '..' || !is_numeric( $blog_id ) ) {
		continue;
	}
	
	echo '<h1>Blog ID: '. $blog_id .'</h1>';
	// BLOGGEN ER SLETTET - FJERN MAPPA OG GÅ VIDERE
	if( false === get_blog_details( $blog_id ) ) {
		printline( 'DELETED BLOG' );
		$from = $path_blogs_dir . $blog_id .'/';
		$to = $path_blogs_dir .'OLD/'. $blog_id .'/';
		printline( 'Moving '. $from .' to '. $to );
		rename($from, $to);
	}
	// FINN ALLE BILDER
	else {
		$blog_details = get_blog_details( $blog_id );
		$site_type = getOption($blog_id, 'site_type');

		// FYLKESSIDER
		if( $site_type == 'fylke' || $site_type == 'kommune' ) {
			// Opprett mønstringen
			$pl_id = getOption($blog_id, 'pl_id');
			if( !isset( $monstringer[ $pl_id ] ) ) {
				$monstringer[ $pl_id ] = new monstring_v2( $pl_id );
			}
			echo '<h2 style="color: #ff0000;">'. $monstringer[ $pl_id ]->getNavn() .'</h2>';
			// ALLE ÅR MØNSTRINGEN HAR LASTET OPP DATA
			foreach( scandir( $path_blogs_dir . $blog_id .'/files/' ) as $year ) {
				if( !is_numeric( $year ) ) {
					continue;
				}

				echo '<h3>'. $monstringer[ $pl_id ]->getNavn() .' - '. $year .'</h3>';
				// ALLE MÅNEDER PER ÅR
				foreach( scandir( $path_blogs_dir . $blog_id .'/files/'. $year ) as $month ) {
					if( $month == '.' || $month == '..' ) {
						continue;
					}
					echo '<h4>'. $monstringer[ $pl_id ]->getNavn() .' - '. $year .':'. $month .'</h4>';
					// ALLE FILER DENNE MÅNEDEN
					foreach( scandir( $path_blogs_dir . $blog_id .'/files/'. $year .'/'. $month ) as $file ) {
						if( $file == '.' || $file == '..' ) {
							continue;
						}
						
						// HOPP OVER ALT SOM IKKE ER BILDER
						$ext = strtolower( substr($file, strrpos($file, '.')+1 ));
						if( !in_array( $ext, ['jpg','jpeg','png','gif'] ) ) {
							echo '<h5>SKIPPING '. $file .'</h5>';	
							continue;
						}
						
						// ER DETTE ORIGINAL-FILA?
						$is_original = !preg_match($pattern_original_or_not, $file );
						printline( ($is_original ? 'ORIG':'COPY') .': '. $file );

						$folder = $path_blogs_dir . $blog_id .'/files/'. $year .'/'. $month .'/';

						// DETTE ER ORIGINAL-FILEN
						if( $is_original ) {
							$image = registerImage( $folder, $file );
						}
						// DETTE ER EN KOMPRIMERT UTGAVE
						else {
							$filename_parts = [];
							preg_match($pattern_original_or_not, $file, $filename_parts );
							
							if( (int) $filename_parts[2] > (int) $filename_parts[3] ) {
								$resolution = (int) $filename_parts[2];
							} else {
								$resolution = (int) $filename_parts[3];
							}
							$image = registerImage( $folder, ($filename_parts[1] .'.'. $filename_parts[4]), $resolution, $file );
						}
						
						// HVIS ORIGINAL-FILEN ALLEREDE ER EN SYMLINK ER JOBBEN GJORT
						if( is_link( $image->fullpath ) ) {
							printline('Filen er symlink - skip it');
							continue;
						}
						
						// Lagre ekstra bildedata
						$image->pl_id = $pl_id;
						
						// KUN FYLKESBILDER SKAL TIL DROPBOX
						if( $site_type == 'fylke' ) {
							$image->dropbox_folder = $year .'/'.
								$monstringer[ $pl_id ]->getFylke()->getNavn() .'/'.
								'_Fylkesmønstringen (PLID'. $pl_id .')/'.
								' Stemning/';
						}
							
						$filename_analyze = explode('_', $file);
						if( is_numeric( $filename_analyze[0] ) && $filename_analyze[0] == $year && is_numeric( $filename_analyze[1] ) ) {
							$image->innslag = true;
						} else {
							$image->innslag = false;
						}
						
						// BILDET ER IKKE AV ET INNSLAG OG FRA I ÅR
						if( date('Y') == $year && !$image->innslag && $site_type == 'fylke' ) {
							// Add to dropbox queue
							$dropbox[ $image->fullpath ] = $image;
						}
						// BILDET ER IKKE AV INNSLAG, OG IKKE ÅRETS SESONG
						elseif( date('Y') != $year ) {
							// Add to symlink queue
							$symlink[ $image->fullpath ] = $image;
						}
					}
				}
			}
		}
	}
}


echo '<h1>FILES TO UPLOAD ('.sizeof( $dropbox ).')</h1>';
$count = 0;
foreach( $dropbox as $image ) {
	$count++;
	echo '<a name="#dropbox"></a><h3>'. $image->file .'</h3>';
	printline('DROPBOX: '. $image->dropbox_folder . $image->file);
	$res = uploadToDropbox( $image );
	#if( $res ) {
		#printline('SYMLINK: '. $image->fullpath .' => '. $image->path . $image->largest_file );
		#createSymlink( $image->fullpath, $image->path . $image->largest_file );
	#}
	
	// TIME COUNTER
	if( TIME_LIMIT < ( microtime(true) - TIME_START ) ) {
		echo 'Nådd tidsbegrensning ('. TIME_LIMIT .'sek) og stopper';
		die();
	}
}

echo '<h1>FILES TO SYMLINK ('.sizeof( $symlink ).')</h1>';
$count = 0;
foreach( $symlink as $image ) {
	$count++;
	echo '<h3>'. $image->file .'</h3>';
	printline('SYMLINK: '. $image->fullpath .' => '. $image->path . $image->largest_file );
	if( !is_dir( $image->path . $image->largest_file ) ) {
		#createSymlink( $image->fullpath, $image->path . $image->largest_file );
	}

	// TIME COUNTER
	if( TIME_LIMIT < ( microtime(true) - TIME_START ) ) {
		echo 'Nådd tidsbegrensning ('. TIME_LIMIT .'sek) og stopper';
		die();
	}
}


function createSymlink( $from, $to ) {
	unlink( $from );
	symlink( $to, $from );
}

function uploadToDropbox( $image ) {
	global $regex, $DROPBOX;

	$dropbox_name = preg_replace('/[^\da-z \- æøå]/i', '', $image->file );
	$dropbox_name = preg_replace($regex, '$1', $dropbox_name);
	$dropboxFile = new DropboxFile( $image->fullpath );
	$dropboxFilePath = '/UKMdigark/Bilder/'. $image->dropbox_folder . $image->file;
	
	// Check if file exists
	try {
		$existing = $DROPBOX->getMetadata( $dropboxFilePath );
		$exists = is_object( $existing );
	} catch( Exception $e ) {
		$exists = false;
	}
	
	// Upload if not exists
	if( !$exists ) {
		try {		
			$file = $DROPBOX->simpleUpload(
				$dropboxFile, 
				$dropboxFilePath,
				['autorename' => true]
			);
			$success = $file->getSize() == filesize( $image->fullpath );
		} catch( Exception $e ) {
			$success = false;
			throw $e;
		}
	}

	return $success;
}


function printline( $print='' ) {
	echo $print .'<br />';
}

function getOption( $blog_id, $option ) {
	global $wpdb;
	$res = $wpdb->get_var(
		"SELECT `option_value`
		FROM `wpms2012_". $blog_id ."_options`
		WHERE `option_name` = '". $option ."'"
	);
	return $res;
}
function registerImage( $folder, $file, $resolution=0, $resolution_file=null ) {
	global $images;
	
	// Bildet finnes fra før
	if( isset( $images[ $folder . $file ] ) ) {
		$image = $images[ $folder . $file ];
		
		// Hvis gitt oppløsning er større enn tidligere registrert, oppdater
		if( $resolution > $image->largest_resolution ) {
			$image->largest_resolution = $resolution;
			$image->largest_file = $resolution_file;
		}
		
		return $images[ $folder . $file ];
	}
	
	// Bildet opprettes og legges til
	$image = new stdClass();
	$image->path = $folder;
	$image->file = $file;
	$image->fullpath = $folder . $file;
	$image->largest_resolution = $resolution;
	$image->largest_file = $resolution_file;
	
	$images[ $folder . $file ] = $image;
	return $image;
}