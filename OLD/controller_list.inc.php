<?php
require_once('UKM/monstring.class.php');
require_once('UKM/innslag.class.php');

$monstring = new monstring(get_option('pl_id'));
$innslagene = $monstring->innslag();

$alle_innslag = array();
foreach($innslagene as $innslag) {
	$inn = new innslag($innslag['b_id']);
	$related = $inn->related_items();
	
	$innslagdata = array('name' => $inn->g('b_name'),
					 'id' => $inn->g('b_id'),
					 'num_images' => is_array( $related['image'] ) ? sizeof($related['image']) : 0,
					);
	$alle_innslag[] = $innslagdata;
}


$INFOS = array('alle_innslag' => $alle_innslag );