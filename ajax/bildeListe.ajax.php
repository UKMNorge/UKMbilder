<?php

use UKMNorge\Arrangement\Arrangement;


if (! isset($_REQUEST['innslagId'])) {
    http_response_code(400);
    die(); // TODO: handle invalid AJAX request
}

$innslagId = intval($_REQUEST['innslagId']);

$arrangement = new Arrangement( get_option('pl_id') );
$innslag = $arrangement->getInnslag()->get( $innslagId );


// var_dump($innslag->getBilder()->getAll());
// var_dump($innslag);
// die();


$blogUsers = get_users([
    'blog_id' => get_current_blog_id()
]);




$bilderHtml = TWIG(
    'bildeListe.ajax.html.twig', 
    [
        'innslag' => $innslag,
        'brukere' => $blogUsers,
        'arrangement' => $arrangement
   ],
    UKMbilder::getPluginPath()
); //returns string

UKMbilder::addResponseData('bilderHtml', $bilderHtml);