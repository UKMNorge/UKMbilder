<?php

require_once('UKM/sql.class.php');

$sql = new SQL('ukm_bilder');
$sql->where('pl_id', get_option('pl_id'));
$sql->where('season', get_option('season'));
$res = $sql->run();

$images = array();
while( $r = mysql_fetch_assoc($res) ) {
	$images[] = $r;
}

die(json_encode(array('images' => $images)));