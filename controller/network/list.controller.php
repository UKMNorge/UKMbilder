<?php

require_once('UKM/innslag.class.php');

$innslag = new innslag_v2( $_GET['innslagId'] );
$TWIGdata['innslag'] = $innslag;

foreach( $innslag->getBilder()->getAll() as $bilde ) {
    $TWIGdata['bilder'][] = $bilde;
}