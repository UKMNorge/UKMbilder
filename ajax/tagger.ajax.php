<?php

use UKMNorge\Arrangement\Arrangement;


if( ! isset($_POST['tagData']) ){
    http_response_code(400);
    die();
} 

$tagData    = $_POST['tagData'];
$innslagId  = $tagData->innslagId;
$imageId    = $tagData->imageId;
$fotografId = $tagData->fotografId;



$imageId = $_POST['imageId'] ? intval($_POST['imageId']) : 0; //TODO: handle non-integer input with UKM-approved method

$arrangement = new Arrangement( get_option('pl_id') );



UKMbilder::addResponseData('tagStatus', $tagData);

