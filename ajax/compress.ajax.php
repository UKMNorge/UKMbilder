<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Database\SQL\Query;


$arrangement = new Arrangement( get_option( 'pl_id' ) );




$sql = new Query("SELECT * FROM `ukm_bilder`
WHERE `pl_id` = '#pl_id'
AND `season` = '#season'
AND `status` = 'uploaded'
ORDER BY `id` ASC
LIMIT 1", [
    'pl_id' => $arrangement->getId(),
    'season' => $arrangement->getSesong()
]);

$currentImageData = $sql->getArray();

die (json_encode($$currentImageData));
