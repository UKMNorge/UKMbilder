<?php
require_once('UKM/Autoloader.php');

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Database\SQL\Query;


$arrangement = new Arrangement( get_option('pl_id') );

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

$result = $sql->run();

$nonConvertedImages = [];

while ($row = Query::fetch($result)) {
    $nextImage = new stdClass;
    $nextImage->id = $row['id'];
    $nextImage->filename = $row['filename'];
    $nextImage->originalFilename = $row['original_filename'];

    $nonConvertedImages[] = $nextImage;
}

$blogUsers = get_users([
    'blog_id' => get_current_blog_id()
]);


UKMbilder::addViewData('nonConvertedImages', $nonConvertedImages);
UKMbilder::addViewData('arrangement', $arrangement);
UKMbilder::addViewData('forestillinger', $arrangement->getProgram()->getAbsoluteAll());
UKMbilder::addViewData('brukere', $blogUsers);

