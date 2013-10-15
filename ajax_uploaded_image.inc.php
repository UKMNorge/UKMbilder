<?php
if(!isset($_FILES) || sizeof($_FILES) == 0)
	die(false);

$SYNC_FOLDER = '/home/ukmno/private_sync/';

$season = get_option('season');
$place	= get_option('pl_id');

require_once('UKM/sql.class.php');

$sql = new SQLins('ukm_bilder');
$sql->add('season', $season);
$sql->add('pl_id', $place);
$res = $sql->run();

$id = $sql->insId();

$filename = $_FILES['image']['name'];
$extension = end($filename);

$name = "$season_$place_$id.$extension";

move_uploaded_file($_FILES['image']['tmp_name'], $SYNC_FOLDER.$name);

die(true);