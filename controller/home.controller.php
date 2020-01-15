<?php
require_once('UKM/Autoloader.php');

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Database\SQL\Query;
use UKMNorge\Innslag\Samling;


$arrangement = new Arrangement( get_option('pl_id') );

// Opprett brukere, i tilfelle vi mangler noen
if( method_exists('UKMusers','createLoginsForParticipantsInArrangement')) {
    UKMusers::createLoginsForParticipantsInArrangement($arrangement);
}

// Hent bilder
$sql = new Query("SELECT *
    FROM `ukm_bilder`
    WHERE `pl_id` = '#pl_id'
    AND `season` = '#season'
    AND `status` = 'uploaded'
    ORDER BY `id` ASC" ,  
    [
        'pl_id' => $arrangement->getId(), 
        'season' => $arrangement->getSesong()
    ]
);

$nonTaggedImagesSql = new Query("SELECT *
    FROM `ukm_bilder`
    WHERE `pl_id` = '#pl_id'
    AND `season` = '#season'
    AND `status` = 'compressed'
    ORDER BY `id` ASC" ,  
    [
        'pl_id' => $arrangement->getId(), 
        'season' => $arrangement->getSesong()
    ]
);

$result = $sql->run();
$nonTaggedImagesResult = $nonTaggedImagesSql->run();

$nonConvertedImages = [];
while ($row = Query::fetch($result)) {
    $nextImage = new stdClass;
    $nextImage->id = $row['id'];
    $nextImage->filename = $row['filename'];
    $nextImage->originalFilename = $row['original_filename'];

    $nonConvertedImages[] = $nextImage;
}

$nonTaggedImages = [];
while($row = Query::fetch($nonTaggedImagesResult)) {
    $nextImage = new stdClass;
    $nextImage->imageId = $row['id'];
    $nextImage->imageUrl = $row['url'];
    $nextImage->originalFilename = $row['original_filename'];

    $nonTaggedImages[] = $nextImage;
}

$brukere = [];
foreach( $arrangement->getInnslag()->getAll() as $innslag ) {
    if( !in_array($innslag->getType()->getId(), ['nettredaksjon','media','arrangor'] ) ) {
        continue;   
    }

    $bruker = $innslag->getPersoner()->getSingle()->getWordpressBruker()->getWordpressObject();
    $brukere[ $bruker->ID ] = $bruker;
}


$blogUsers = get_users([
    'blog_id' => get_current_blog_id()
]);
foreach( $blogUsers as $user ) {
    if( isset( $brukere[ $user->ID])){
        continue;
    }
    $brukere[ $user->ID ] = $user;
}

foreach( $brukere as $bruker ) {
    $sorterte_brukere[ $bruker->display_name ] = $bruker;
}
ksort( $sorterte_brukere );

UKMbilder::addViewData('nonConvertedImages', $nonConvertedImages);
UKMbilder::addViewData('nonTaggedImagesJson', json_encode($nonTaggedImages));
UKMbilder::addViewData('arrangement', $arrangement);
UKMbilder::addViewData('forestillinger', $arrangement->getProgram()->getAbsoluteAll());
UKMbilder::addViewData('brukere', $sorterte_brukere);

