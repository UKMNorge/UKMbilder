<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

require_once('UKM/forestilling.class.php');
require_once('UKM/innslag.class.php');

$forestilling = new forestilling($_POST['c_id']);
$program = $forestilling->innslag();

$alle_innslag = array();
foreach($program as $b_info) {
	$innslag = new innslag($b_info['b_id']);
	$data = new StdClass;
	$data->name = $innslag->get('b_name');
	$data->id = $innslag->get('b_id');
	$alle_innslag[] = $data;
}

die(json_encode(array('innslag' => $alle_innslag)));