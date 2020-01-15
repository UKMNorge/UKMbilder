<?php

#var_dump($_POST);

use UKMNorge\Database\SQL\Delete;
use UKMNorge\Innslag\Media\Bilder\Bilde;

$bilde = Bilde::getById(intval($_POST['bildeId']));

// Slett fra bilder
$delete = new Delete(
    'ukm_bilder',
    [
        'id' => $_POST['bildeId']
    ]
);
$res_ukm = $delete->run();

// Slett fra wordpress
$res_wp = wp_delete_attachment($bilde->getPostId(), true);


UKMbilder::addResponseData(
    'success',
    $res_ukm && $res_wp
);
