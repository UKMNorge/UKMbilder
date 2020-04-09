<?php

use UKMNorge\Database\SQL\Update;
use UKMNorge\Innslag\Media\Bilder\Bilde;

// Marker som cancelled
$db_update = new Update('ukm_bilder', array('id' => $_POST['imageId'] ));
$db_update->add('status', 'cancelled');
$res = $db_update->run();

if( $res == 1 ) {
	$success = true;	
} else {
	// Klarte ikke å oppdatere status
	$success = false;
	UKMbilder::addResponseData('message', "Klarte ikke å kansellere konvertering.".$db_update->debug());
}

UKMbilder::addResponseData(
    'success',
    $success
);
