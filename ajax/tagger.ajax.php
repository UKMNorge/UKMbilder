<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Database\SQL\Query;
use UKMNorge\Database\SQL\Update;

require_once( UKMbilder::$path_plugin . 'class/TaggerBilde.class.php');

if (!isset($_POST['tagData'])) {
    http_response_code(400);
    die();
}

// split POST-data
$tagData    = $_POST['tagData'];
$innslagId  = $tagData['innslagId'];
$imageId    = $tagData['imageId'];
$fotografId = $tagData['fotografId'];
$hendelseId = isset( $tagData['hendelseId'] ) ? $tagData['hendelseId'] : NULL;

return UKMbilder::addResponseData('storedTag', TaggerBilde::taggBilde($innslagId, $imageId, $fotografId, $hendelseId));