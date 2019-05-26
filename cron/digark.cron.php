<?php
header('Cache-Control: no-store');

use MariusMandal\Flickr\Exception;
use MariusMandal\Flickr\App;
use MariusMandal\Flickr\Flickr;
use MariusMandal\Flickr\Auth;
use MariusMandal\Flickr\Request\Upload\Syncron as FlickrUploadSyncron;
use MariusMandal\Flickr\Request\Photosets\AddPhoto as FlickrPhotosetsAddPhoto;
use MariusMandal\Flickr\Request\Photosets\Create as FlickrPhotosetsCreate;
use MariusMandal\Flickr\Request\Photosets\GetList as FlickrPhotosetsGetList;
use Kunnu\Dropbox\DropboxFile;
use GuzzleHttp\Exception\ClientException;

require_once('UKMconfig.inc.php');
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
	list( $year, $pl_id, $image_id, $file, $size ) = getInfosFromFilename( $file_name, $file_path );
	// Metadata fra database
	$metadata = metadata( $image_id );
	
	// CASE 1: BILDET ER IKKE FERDIGBEHANDLET
	if( 'tagged' != $metadata['status'] ) {
		out( 'Ikke tagget - la ligge inntil videre');
		continue;
	}
	// CASE 2: BILDET ER ALLEREDE SYNKRONISERT TIL DROPBOX OG DERMED FLICKR
	elseif( in_array($metadata['synced_dropbox'], ['true','nogo']) && in_array($metadata['synced_flickr'], ['true','nogo']) ) {
		out('Allerede synkronisert med dropbox og flickr');
		if( file_exists( $file_path . $file_name ) ) {
			out('Slett fil: '. $file_path . $file_name );
			unlink( $file_path . $file_name );
#			out('DEAKTIVERT');
		}
		continue;
	}
	// CASE 3: BILDET SKAL LASTES OPP TIL DROPBOX OG FLICKR
	 else {
	 	out( 'DROPBOX STATUS: '. $metadata['synced_dropbox'] );
	 	out( 'FLICKR STATUS: '. $metadata['synced_dropbox'] );
		$monstring = getMonstring( $metadata['pl_id'] );
		$path = getStoragePathFromMonstring( $monstring );
		try {
			$innslag = getInnslag( $monstring, $metadata['b_id'] );
		} catch( \Exception $e ) {
			// Innslaget er avmeldt - da skal vi ikke laste opp bilder
			if( $e->getCode() == 2 ) {
				$SQLins = new SQLins('ukm_bilder', array('id' => $image_id ) );
				$SQLins->add('synced_flickr', 'nogo');
				$SQLins->add('synced_dropbox', 'nogo');
				$SQLins->run();
				continue;
			}
		}
		$fotograf = getFotograf( $metadata['wp_uid'] );
	 	out( 'FOTOGRAF-ID: '. $metadata['wp_uid'] );
	 	out( 'FOTOGRAF-NAVN: '. $fotograf );

		$dropbox_name = $innslag->getNavn();
		$dropbox_name .= ' (PHOTO by UKM Media '. $fotograf.')';
		$dropbox_name = preg_replace('/[^\da-z \- æøå]/i', '', $dropbox_name );
		$dropbox_name = preg_replace($regex, '$1', $dropbox_name);

		out( 'DROPBOX NAME:'. $dropbox_name );
		// UPLOAD TO DROPBOX
		if( 'true' != $metadata['synced_dropbox'] ) {
			$path_info = pathinfo( $file_path . $file_name );
			$dropboxFile = new DropboxFile( $file_path . $file_name );
			$dropboxFilePath = '/UKMdigark/Bilder/'. $path . $dropbox_name .'.'. strtolower( $path_info['extension'] );

			out( 'DROPBOX PATH:'. $dropboxFilePath );
			try {
				$file = $DROPBOX->simpleUpload(
					$dropboxFile, 
					$dropboxFilePath,
					['autorename' => true]
				);
				$success = $file->getSize() == $size;
			} catch( Exception $e ) {
				$success = false;
			}
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
		if( 'true' != $metadata['synced_flickr'] && 'land' == $monstring->getType() ) {
			// DATA-BEREGNING
			list( $tittel, $beskrivelse, $tags ) = flickr_data( $fotograf, $monstring, $innslag );
			
			$imageUpload = new FlickrUploadSyncron( $file_path . $file_name );
#			$imageUpload->setTitle( 'test' )->setDescription( 'test' )->setTags( 'test' );
			$imageUpload->setTitle( $tittel )->setDescription( $beskrivelse )->setTags( $tags );
			$res = $imageUpload->execute();
var_dump( $res );

			// Retry if flickr's a bitch
			if( !is_numeric( $res->getData() ) ) {
				$res = $imageUpload->execute();
var_dump( $res );
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
				} elseif( false === $album ) {
					out( 'Klarte ikke å opprette album');
				} else {
					out( 'Legg til bilde '. $flickr_image_id .' i album '. $flickr_image_id );
					$addPhoto = new FlickrPhotosetsAddPhoto( $album, $flickr_image_id );
					$res = $addPhoto->execute();
					out( var_export( $res ), 'b' );
					
					// Retry if flickr's acting up
					if( is_object( $res->getData() )  && $res->getData()->stat == 'ok' ) {
						out( 'RETRY 1');
						$res = $addPhoto->execute();
						out( var_export( $res ), 'b' );
					}

					// Retry if flickr's acting up
					if( is_object( $res->getData() )  && $res->getData()->stat == 'ok' ) {
						out('RETRY 2');
						$res = $addPhoto->execute();
						out( var_export( $res ), 'b' );
					}
				}

				$SQLins = new SQLins('ukm_bilder', array('id' => $image_id ) );
				$SQLins->add('synced_flickr', 'true');
				$SQLins->add('flickr_data', json_encode(['id' => $flickr_image_id]));
				$SQLins->run();
			}
		}
		// CASE 3:B Bilder som ikke er fra festivalen skal ikke lastes opp til flickr
		elseif( 'land' != $monstring->getType() ) {
			if( $success ) {
				$SQLins = new SQLins('ukm_bilder', array('id' => $image_id ) );
				$SQLins->add('synced_flickr', 'nogo');
				$SQLins->run();
			}	
		}
	}
}




function flickr_find_album( $flickr_image_id, $c_id, $pl_id ) {
	require_once('UKM/monstring.class.php');	

	$monstring = getMonstring( $pl_id );
	if( empty( $c_id ) ) {
		$album = new flickr_album( 'monstring', $pl_id );
		$album_name = $monstring->getNavn() .' '. $monstring->getSesong();
		$album_type = 'monstring';
		$album_id = $pl_id;
	} else {
		require_once('UKM/forestilling.class.php');
		$forestilling = $monstring->getProgram()->get( $c_id );

		$album = new flickr_album( 'forestilling', $c_id );
		$album_name = $forestilling->getNavn() .' @ '. $monstring->getNavn() .' '. $monstring->getSesong();
		$album_type = 'forestilling';
		$album_id = $c_id;
	}

	// Album finnes i lokal database
	if( $album->getFlickrId() && !empty( $album->getFlickrId() ) ) {
		echo 'ALBUM: lokal og remote kopi i sync.';
		return $album->getFlickrId();
	}
	
	$albumRequest = new FlickrPhotosetsGetList();
	$albumRequest->setPerPage(500)->setUserId( Auth::getUserId() );
	$res = $albumRequest->execute();
	
	foreach( $res->getData()->photosets->photoset as $flickr_album ) {
		// Albumet eksisterer hos flickr, men ikke i lokal database
		if( $flickr_album->title->_content == $album_name ) {
			echo 'ALBUM: opprettet lokal kopi';
			$res = $album->create( $album_type, $album_id, $flickr_album->id, $album_name);
			var_dump( $res );
			return $album->getFlickrId();
		}
	}

	$createAlbum = new FlickrPhotosetsCreate( $album_name, $flickr_image_id );
	$res = $createAlbum->execute();
	if( !$res ) {
		echo 'ALBUM: kunne ikke opprette remote kopi';
		var_dump( $res );

		echo 'ALBUM: RETRY';
		$res = $createAlbum->execute();

		if( !$res ) {
			return false;
		}
	}

	echo 'ALBUM: opprettet remote kopi';
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


/**
 * Henter metadata fra filnavnet
 * $year, $pl_id, $image_id, $file, $size
 *
 * @param String $file_name
 * @param String $file_path
 * @return list[ år, pl_id, bilde_id, file-handle, størrelse]
 */
function getInfosFromFilename( $file_name, $file_path ) {
	$file = fopen( $file_path.$file_name, 'rb' );
	$size = filesize( $file_path.$file_name );
	
	$dot_pos = strrpos( $file_name, '.' );
	$ext = substr( $file_name, $dot_pos );
	$infos = str_replace( $ext, '', $file_name );
	$filename_metadata = explode('_', $infos );
	return array( $filename_metadata[0], $filename_metadata[1], $filename_metadata[2], $file, $size );
}


/**
 * Hent informasjon om fotograf. 
 * Brukes som cache for å begrense db-change til wordpress
 *
 * @param Integer $wp_uid
 * @return String $display_name
 */
function getFotograf( $wp_uid ) {
	global $cache_fotograf;
	if( !isset( $cache_fotograf[ $wp_uid ] ) ) {
		$cache_fotograf[ $wp_uid ] = getFotofrafFromWpUID( $wp_uid );
	}
	return $cache_fotograf[ $wp_uid ];
}

/**
 * Faktisk hent informasjon om wordpress-bruker (fotograf)
 *
 * @param Integer $wp_uid
 * @return String $display_name
 */
function getFotofrafFromWpUID( $wp_uid ) {
	$sql = new SQL("SELECT `display_name`
		FROM `wpms2012_users`
		WHERE `ID` = #id",
		[
			'id' => $wp_uid
		],
		'wordpress'
	);
	return ucfirst( $sql->run('field', 'display_name') );
}


/**
 * Hent informasjon om et innslag
 *
 * @param [type] $b_id
 * @return void
 */
function getInnslag( $monstring,  $b_id ) {
	global $cache_innslag;
	if( !isset( $cache_innslag[ $b_id ] ) ) {
		$cache_innslag[ $b_id ] = $monstring->getInnslag()->get( $b_id );
	}
	return $cache_innslag[ $b_id ];
}


/**
 * Hent mønstrings-objekt
 * Internal cache
 *
 * @param Integer $pl_id
 * @return Monstring_V2
 */
function getMonstring( $pl_id ) {
	global $cache_monstringer;
	
	if( !isset( $cache_monstringer[ $pl_id ] ) ) {
		$cache_monstringer[ $pl_id ] = new monstring_v2( $pl_id );
	}
	return $cache_monstringer[ $pl_id ];
}

/**
 * Beregn lagringsbane for dropbox
 *
 * @param Monstring_V2 $monstring
 * @return void
 */
function getStoragePathFromMonstring( $monstring ) {
	switch( $monstring->getType() ) {
		case 'land':
			return $monstring->getSesong() .'/UKM-festivalen/';
		case 'fylke':
			return $monstring->getSesong() .'/'. $monstring->getFylke()->getNavn() .'/_Fylkesmønstringen (PLID'. $monstring->getId() .')/';
		default:
			$kommunestreng = $monstring->getKommuner()->__toString();
			return $monstring->getSesong() .'/'. $monstring->getFylke()->getNavn() .'/'. $kommunestreng .' (PLID'. $monstring->getId() .')/';
	}
}

/**
 * Lag flickr meta-data
 *
 * @param String $fotograf
 * @param Monstring_V2 $monstring
 * @param Innslag_V2 $innslag
 * @return list[ film-navn, beskrivelse, tags ]
 */
function flickr_data( $fotograf, $monstring, $innslag ) {
	$beskrivelse = 'Foto: '. $fotograf. "\r\n\r\n";
	
	// Tags: sted
	$tags =  $monstring->getNavn().','
			.$monstring->getSesong().','
			.$monstring->getNavn().' '.$monstring->getSesong().',';

	// Innslagets navn (inkl tags)
	$tittel = $innslag->getNavn();
	$tags .= $tittel.',';

	// Personer (inkl tags)
	foreach( $innslag->getPersoner()->getAll() as $person ) {
		$beskrivelse .= $person->getNavn() .' ('. $person->getInstrument() .'), ';
		$tags .= $person->getNavn() .',';
	}

	// Geografi
	$tags .= 'UKM '. $innslag->getKommune()->getNavn() .',';
	$tags .= 'UKM';
	
	// Cleanup
	rtrim(', ', $beskrivelse );
	rtrim(', ', $tags );

	return array( $tittel, $beskrivelse, $tags );
}


/**
 * Skriv ut tekst som html.
 * Default-wrapper: '<p></p>'
 *
 * @param String $message
 * @param String navn på wrapper-tag
 *
 * @output HTML-kode <wrapper>message</wrapper>;
 * @return void
 */
function out( $message, $wrapper='p' ) {
	if( 'b' == $wrapper ) {
		echo "<p><b>$message</b></p>";
	} else {
		echo "<$wrapper>$message</$wrapper>";
	}
}
