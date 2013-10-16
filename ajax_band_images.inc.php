<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

require_once('UKM/innslag.class.php');

$innslag = new innslag($_POST['band']);
$related = $innslag->related_items();

die(json_encode(array('images' => $related['image'], 'b_id' => $innslag->get('b_id'))));