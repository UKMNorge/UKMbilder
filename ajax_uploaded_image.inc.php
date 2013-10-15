<?php
if(!isset($_FILES) || sizeof($_FILES) == 0)
	die(false);

var_dump($_POST);
var_dump($_FILES);

die();
// 
$SYNC_FOLDER = '/home/ukmno/private_sync/';

$season = get_option('season');
$place	= get_option('pl_id');

require_once('UKM/sql.class.php');

$sql = new SQLins('ukm_bilder');
$sql->add('season', $season);
$sql->add('pl_id', $place);
$res = $sql->run();

$id = $res->insId();

$filename = $file['name'];
$extension = end($filename);

$name = "$season_$place_$id.$extension";

move_uploaded_file($file['tmp_name'], $SYNC_FOLDER.$name);