<?php

use UKMNorge\Arrangement\Arrangement;

require_once('UKM/Autoloader.php');


$arrangement = new Arrangement(get_option( 'pl_id' ));
$hendelse = $arrangement->getProgram()->get($_REQUEST['hendelseId']);
$innslag = $hendelse->getInnslag()->getAll();


$innslagHtml = TWIG(
     'innslagListe.html.twig', 
     ['innslag' => $innslag],
     UKMbilder::getPluginPath()
); //returns string


UKMbilder::addResponseData('innslagInputs', $innslagHtml);