<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

require_once('UKM/innslag.class.php');
require_once('UKM/monstring.class.php');

$monstring = new monstring_v2( get_option('pl_id' ) );

if($_POST['c_id']==0) {
    $innslag_collection = $monstring->getInnslag();
} else {
    $forestilling = $monstring->getProgram()->get( $_POST['c_id'] );
	$innslag_collection = $forestilling->getInnslag();
}

foreach( $innslag_collection->getAll() as $innslag ) {
	$data = new StdClass;
	$data->name = $innslag->getNavn();
    $data->id = $innslag->getId();
    $data->type = $innslag->getType()->getNavn();
    $data->personer = $innslag->getPersoner()->getAntall();
    $data->harTitler = $innslag->getType()->harTitler();
    $data->samtykke_harNei = $innslag->getSamtykke()->harNei();
    $data->samtykke_countNei = $innslag->getSamtykke()->getNeiCount();
	$alle_innslag[] = $data;
}

die(json_encode(array('innslag' => $alle_innslag)));