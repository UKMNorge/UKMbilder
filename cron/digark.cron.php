<?php

use MariusMandal\Flickr\Exception;
use MariusMandal\Flickr\App;
use MariusMandal\Flickr\Flickr;
use MariusMandal\Flickr\Auth;
use MariusMandal\Flickr\Request\Upload\Syncron as FlickrUploadSyncron;
use MariusMandal\Flickr\Request\Photosets\AddPhoto as FlickrPhotosetsAddPhoto;
use MariusMandal\Flickr\Request\Photosets\Create as FlickrPhotosetsCreate;
use MariusMandal\Flickr\Request\Photosets\GetList as FlickrPhotosetsGetList;
use Kunnu\Dropbox\DropboxFile;

require_once('Flickr/autoloader.php');
require_once('UKM/inc/dropbox.inc.php');


define('TIME_LIMIT', 45);
ini_set('max_execution_time', round(TIME_LIMIT*1.1));

$time_start = microtime(true);

@session_start();
require_once('UKMconfig.inc.php');
require_once('UKM/inc/dropbox.inc.php');
require_once('UKM/sql.class.php');
require_once('UKM/monstring.class.php');
require_once('UKM/innslag.class.php');
require_once('UKM/flickr_album.class.php');
#require_once('/home/ukmno/public_html/wp-config.php');

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


// SET LOG LEVEL (throw, default:log)
Exception::setLogMethod('throw');

// SET APP DETAILS
App::setId(FLICKR_API_KEY);
App::setSecret(FLICKR_API_SECRET);
App::setPermissions('write');

// As long as input parameters is valid or null, it is always best to test
Auth::authenticate( FLICKR_AUTH_USER, FLICKR_AUTH_TOKEN, FLICKR_AUTH_SECRET );


/// START-DATA
$file_path = '/home/ukmno/private_sync/';
$cache_monstringer = array();
$cache_innslag = array();
$cache_fotograf = array();

$files = scandir( $file_path );

foreach( $files as $file_name ) {
	if( $file_name == '.' || $file_name == '..' ) {
		continue;
	}
	// TIME COUNTER
	if( TIME_LIMIT < ( microtime(true) - $time_start ) ) {
		out( 'Nådd tidsbegrensning ('. TIME_LIMIT .'sek) og stopper' );
		die();
	}

	out( $file_name, 'h3');
	// Metadata fra filename
	list( $year, $pl_id, $image_id, $file, $size ) = infos_from_filename( $file_name, $file_path );
	// Metadata fra database
	$metadata = metadata( $image_id );
	
	// CASE 1: BILDET ER IKKE FERDIGBEHANDLET
	if( 'tagged' != $metadata['status'] ) {
		out( 'Ikke tagget - la ligge inntil videre');
		continue;
	}
	// CASE 2: BILDET ER ALLEREDE SYNKRONISERT TIL DROPBOX (OG DERMED FLICKR?)
	elseif( 'true' == $metadata['synced_dropbox'] && 'true' == $metadata['synced_flickr'] ) {
		out('Allerede synkronisert med dropbox og flickr');
		if( file_exists( $file_path . $file_name ) ) {
			out('Slett fil: '. $file_path . $file_name );
			#unlink( $file_path . $file_name );
			out('DEAKTIVERT');
		}
		continue;
	}
	// CASE 3: BILDET SKAL LASTES OPP TIL DROPBOX OG FLICKR
	 else {
	 	out( 'DROPBOX STATUS: '. $metadata['synced_dropbox'] );
	 	out( 'FLICKR STATUS: '. $metadata['synced_dropbox'] );
		$pl = get_pl( $metadata['pl_id'] );
		$path = path_from_pl( $pl );
		$innslag = get_innslag( $metadata['b_id'] );
		$fotograf = get_fotograf( $metadata['wp_uid'] );
	 	out( 'FLICKR STATUS: '. $metadata['wp_uid'] );
	 	out( 'FLICKR STATUS: '. $fotograf );

		$dropbox_name = name_from_innslag( $innslag );
		$dropbox_name .= ' (PHOTO by UKM Media '. ucfirst($fotograf).')';
		$dropbox_name = preg_replace('/[^\da-z \- æøå]/i', '', $dropbox_name );
		$dropbox_name = preg_replace($regex, '$1', $dropbox_name);

		out( 'DROPBOX NAME:'. $dropbox_name );
		// UPLOAD TO DROPBOX
		if( 'true' != $metadata['synced_dropbox'] ) {
			$path_info = pathinfo( $file_path . $file_name );
			$dropboxFile = new DropboxFile( $file_path . $file_name );

			$file = $DROPBOX->simpleUpload(
				$dropboxFile, 
				'/UKMdigark/Bilder/'. $path . $dropbox_name .'.'. strtolower( $path_info['extension'] ),
				['autorename' => true]
			);

			$success = $file->getSize() == $size;
			out( 'DROPBOX UPLOAD: '. ($success ? ' SUCCESS!' : ' FAILURE' ), 'b' );
			if( $success ) {		
				$SQLins = new SQLins('ukm_bilder', array('id' => $image_id ) );
				$SQLins->add('synced_dropbox', 'true');
				$SQLins->run();
			}
		} else {
			$success = true;
		}
		
		// CASE 3:A UKM-festival-bilde. Last opp til flickr
		if( 'true' != $metadata['synced_flickr'] && 'land' == $pl->get('type') ) {
			// DATA-BEREGNING
			list( $tittel, $beskrivelse, $tags ) = flickr_data( $fotograf, $pl, $innslag );
			
			
			$imageUpload = new FlickrUploadSyncron( $file_path . $file_name );
			$imageUpload->setTitle( $tittel )->setDescription( $beskrivelse )->setTags( $tags );
			$res = $imageUpload->execute();

			// Retry if flickr's a bitch
			if( !is_numeric( $res->getData() ) ) {
				$res = $imageUpload->execute();
			}
			if( !is_numeric( $res->getData() ) ) {
				Exception::handle('Opplasting feilet på tross av to forsøk');
			}
			$flickr_image_id = $res->getData();


			if( is_numeric( $image_id ) ) {
				$album = flickr_find_album( $flickr_image_id, $metadata['c_id'], $metadata['pl_id'] );
				// Albumet er nytt, og er blitt opprettet med dette bildet som det første
				if( true === $album ) {
					out( 'Album opprettet nå' );
				} else {
					out( 'Legg til bilde '. $flickr_image_id .' i album '. $flickr_image_id );
					$addPhoto = new FlickrPhotosetsAddPhoto( $album, $flickr_image_id );
					$res = $addPhoto->execute();
					
					out( var_export( $res->getData() ), 'b' );
				}
			}

			if( $success ) {		
				$SQLins = new SQLins('ukm_bilder', array('id' => $image_id ) );
				$SQLins->add('synced_flickr', 'true');
				$SQLins->run();
			}
		}
		// CASE 3:B Bilder som ikke er fra festivalen skal ikke lastes opp til flickr
		elseif( 'land' != $pl->get('type') ) {
			if( $success ) {		
				$SQLins = new SQLins('ukm_bilder', array('id' => $image_id ) );
				$SQLins->add('synced_flickr', 'true');
				$SQLins->run();
			}	
		}
	}
}




function flickr_find_album( $flickr_image_id, $c_id, $pl_id ) {
	global $flickr;
	require_once('UKM/monstring.class.php');	

	$monstring = new monstring( $pl_id );
	if( empty( $c_id ) ) {
		$album = new flickr_album( 'monstring', $pl_id );
		$album_name = $monstring->get('pl_name');
		$album_type = 'monstring';
		$album_id = $pl_id;
	} else {
		require_once('UKM/forestilling.class.php');
		$forestilling = new forestilling( $c_id );

		$album = new flickr_album( 'forestilling', $c_id );
		$album_name = $monstring->get('pl_name') .' - '. $forestilling->get('c_name');
		$album_type = 'forestilling';
		$album_id = $c_id;
	}

	// Album finnes i lokal database
	if( $album->getFlickrId() ) {
		return $album->getFlickrId();
	}
	
	$albumRequest = new FlickrPhotosetsGetList();
	$albumRequest->setPerPage(500)->setUserId( Auth::getUserId() );
	$res = $albumRequest->execute();
	
	foreach( $res->getData()->photosets->photoset as $flickr_album ) {
		// Albumet eksisterer hos flickr, men ikke i lokal database
		if( $flickr_album->title->_content == $album_name ) {
			$album->create( $album_type, $album_id, $flickr_album->id, $album_name);
			return $album->getFlickrId();
		}
	}

	$createAlbum = new FlickrPhotosetsCreate( $album_name, $flickr_image_id );
	$res = $createAlbum->execute();

	$album->create( $album_type, $album_id, $res->getData()->photoset->id, $album_name);
	return true;
}


function metadata( $image_id ) {
	$metadata = new SQL("SELECT * 
						 FROM `ukm_bilder`
						 WHERE `id` = '#image_id'",
						 array('image_id' => $image_id )
						);
	return $metadata->run('array');
}


function infos_from_filename( $file_name, $file_path ) {
	$file = fopen( $file_path.$file_name, 'rb' );
	$size = filesize( $file_path.$file_name );
	
	$dot_pos = strrpos( $file_name, '.' );
	$ext = substr( $file_name, $dot_pos );
	$infos = str_replace( $ext, '', $file_name );
	$filename_metadata = explode('_', $infos );
	return array( $filename_metadata[0], $filename_metadata[1], $filename_metadata[2], $file, $size );
}


function get_fotograf( $wp_uid ) {
	global $cache_fotograf;
	if( !isset( $cache_fotograf[ $wp_uid ] ) ) {
		$cache_fotograf[ $wp_uid ] = fotograf_from_wpuid( $wp_uid );
	}
	return $cache_fotograf[ $wp_uid ];
}
function fotograf_from_wpuid( $wp_uid ) {
	$wordpress = mysql_connect( UKM_WP_DB_HOST, UKM_WP_DB_USER, UKM_WP_DB_PASSWORD );
	mysql_select_db( UKM_WP_DB_NAME );
	
	$query = "SELECT `display_name`
						   FROM `wpms2012_users`
						   WHERE `ID` = '". $wp_uid . "'";
	$res = mysql_query( $query );
	echo mysql_error();
	$row = mysql_fetch_assoc( $res );
	
	return $row['display_name'];
}


function get_innslag( $b_id ) {
	global $cache_innslag;
	if( !isset( $cache_innslag[ $b_id ] ) ) {
		$cache_innslag[ $b_id ] = new innslag( $b_id );
	}
	return $cache_innslag[ $b_id ];
}

function name_from_innslag( $innslag ) {
	return $innslag->g('b_name');
}


function get_pl( $pl_id ) {
	global $cache_monstringer;
	
	if( !isset( $cache_monstringer[ $pl_id ] ) ) {
		$cache_monstringer[ $pl_id ] = new monstring( $pl_id );
	}
	return $cache_monstringer[ $pl_id ];
}

function path_from_pl( $pl ) {
	switch( $pl->g('type') ) {
		case 'land':
			return $pl->g('season') .'/UKM-festivalen/';
		case 'fylke':
			return $pl->g('season') .'/'. $pl->g('fylke_name') .'/_Fylkesmønstringen (PLID'. $pl->g('pl_id') .')/';
		default:
			$kommunestreng = '';
			foreach( $pl->g('kommuner') as $kommune ) {
				$kommunestreng .= $kommune['name'] .', ';
			}
			$kommunestreng = rtrim( $kommunestreng, ', ' );
			
			return $pl->g('season') .'/'. $pl->g('fylke_name') .'/'. $kommunestreng .' (PLID'. $pl->g('pl_id') .')/';
	}
}

function flickr_data( $fotograf, $pl, $innslag ) {
	$beskrivelse = 'Foto: '. $fotograf. "\r\n\r\n";
	
	// Tags: sted
	$tags =  $pl->get('pl_name').','
			.$pl->get('season').','
			.$pl->get('pl_name').' '.$pl->get('season').',';

	// Innslagets navn (inkl tags)
	$tittel = $innslag->g('b_name');
	$tags .= $tittel.',';

	// Personer (inkl tags)
	$personer = $innslag->personObjekter();
	foreach( $personer as $person ) {
		$beskrivelse .= $person->get('name') .' ('. $person->get('instrument') .'), ';
		$tags .= $person->get('name').',';
	}

	// Geografi
	$innslag->loadGEO();
	$tags .= 'UKM '. $innslag->get('kommune_utf8').',';
	
	$tags .= 'UKM';
	
	// Cleanup
	rtrim(', ', $beskrivelse );
	rtrim(', ', $tags );

	return array( $tittel, $beskrivelse, $tags );
}



function out( $message, $wrapper='p' ) {
	if( 'b' == $wrapper ) {
		echo "<p><b>$message</b></p>";
	} else {
		echo "<$wrapper>$message</$wrapper>";
	}
}
