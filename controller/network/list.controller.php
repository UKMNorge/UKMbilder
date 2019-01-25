<?php

require_once('UKM/innslag.class.php');
require_once('UKM/write_bilde.class.php');

$innslag = new innslag_v2( $_GET['innslagId'] );
$TWIGdata['innslag'] = $innslag;

if( isset( $_GET['delete'] ) ) {
    $bilde = $innslag->getBilder()->get( $_GET['delete'] );

    $TWIGdata['message'] = new stdClass();
    if( write_bilde::delete( $bilde, $bilde->getBlogId() ) ) {
        $TWIGdata['message']->level = 'success';
        $TWIGdata['message']->title = 'Bildet er slettet fra innslaget';
    } else {
        $TWIGdata['message']->level = 'danger';
        $TWIGdata['message']->title = 'Kunne ikke slette bilde';
    }
}

foreach( $innslag->getBilder()->getAll() as $bilde ) {
    $TWIGdata['bilder'][] = $bilde;
}