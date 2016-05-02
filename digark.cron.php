<?php
define('TIME_LIMIT', 45);
ini_set('max_execution_time', round(TIME_LIMIT*1.1));

$time_start = microtime(true);

@session_start();
require_once('UKMconfig.inc.php');
require_once('UKM/inc/dropbox.inc.php');
require_once('UKM/sql.class.php');
require_once('UKM/monstring.class.php');
require_once('UKM/innslag.class.php');
require_once('/home/ukmno/public_html/wp-config.php');

$client = new Dropbox\Client( DROPBOX_AUTH_ACCESS_TOKEN, $appName, 'UTF-8' );

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
		die('Reached time limit ('. TIME_LIMIT .'sec)');
	}


	echo '<br />'. $file_name .': ';
		
	$file = fopen( $file_path.$file_name, 'rb' );
	$size = filesize( $file_path.$file_name );
	
	$dot_pos = strrpos( $file_name, '.' );
	$ext = substr( $file_name, $dot_pos );
	$infos = str_replace( $ext, '', $file_name );

	list( $year, $pl_id, $image_id ) = explode('_', $infos );
	
	// INFO FRA FILNAVN OG BILDETABELL
	$metadata = new SQL("SELECT * 
						 FROM `ukm_bilder`
						 WHERE `id` = '#image_id'",
						 array('image_id' => $image_id )
						);
	$metadata = $metadata->run('array');

	if( $metadata['synced_dropbox'] == 'true' ) {
		echo 'already synced';
		continue;
	} else {
		// Hent path
		if( !isset( $cache_monstringer[ $pl_id ] ) ) {
			$cache_monstringer[ $pl_id ] = path_from_pl_id( $pl_id );
		}
		$path = $cache_monstringer[ $pl_id ];
	
		// Hent innslaget
		if( !isset( $cache_innslag[ $metadata['b_id'] ] ) ) {
			$cache_innslag[ $metadata['b_id'] ] = dropboxname_from_b_id( $metadata['b_id'] );
		}
		$dropbox_name = $cache_innslag[ $metadata['b_id'] ];
		
		// Hent fotograf
		if( !isset( $cache_fotograf[ $metadata['wp_uid'] ] ) ) {
			$cache_fotograf[ $metadata['wp_uid'] ] = fotograf_from_wpuid( $metadata['wp_uid'] );
		}
		$fotograf = $cache_fotograf[ $metadata['wp_uid'] ];
		
		$dropbox_name .= ' (PHOTO by UKM Media '. ucfirst($fotograf).')';
		
		// UPLOAD TO DROPBOX
		
		$res = $client->uploadFile('/Bilder/'. $path . $dropbox_name. strtolower($ext) , Dropbox\WriteMode::add(), $file, $size);
		$success = $res['bytes'] == $size;

		echo $dropbox_name .' '. ( $success ? ' SUCCESS!' : ' FAILURE' ) . '<br />';

		if( $success ) {		
			$SQLins = new SQLins('ukm_bilder', array('id' => $image_id ) );
			$SQLins->add('synced_dropbox', 'true');
			$SQLins->run();
		}
	}
}


function fotograf_from_wpuid( $wp_uid ) {
	global $table_prefix;
	$wordpress = mysql_connect( DB_HOST, DB_USER, DB_PASSWORD );
	mysql_select_db( DB_NAME );
	
	$query = mysql_query( "SELECT `display_name`
						   FROM `". $table_prefix ."users`
						   WHERE `ID` = '".$wp_uid. "'");
	echo mysql_error();
	$row = mysql_fetch_assoc( $query );
	return $row['display_name'];
}

function dropboxname_from_b_id( $b_id ) {
	$innslag = new innslag( $b_id );
	return preg_replace('/[^\da-z -æøå]/i', '', $innslag->g('b_name') );
}

function path_from_pl_id( $pl_id ) {
	$pl = new monstring( $pl_id );
	
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