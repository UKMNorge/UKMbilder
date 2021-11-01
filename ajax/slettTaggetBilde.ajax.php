<?php


use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Database\SQL\Delete;
use UKMNorge\Innslag\Titler\Write as WriteTitler;

$arrangement = new Arrangement(intval(get_option('pl_id')));
$innslag = $arrangement->getInnslag()->get($_POST['innslagId']);
$bilde = $innslag->getBilder()->get($_POST['bildeId']);

if ($bilde->getBlogId() != get_current_blog_id()) {
    UKMbilder::addResponseData('success', false);
    UKMbilder::addResponseData('message', 'Det er ikke mulig å slette bilder som er lastet opp fra et annet arrangement.');
} else {

    // hvis arrangement er kunstgalleri så slett kobling mellom utstilling og bilde og mellom utstilling og playback
    if($arrangement->erKunstgalleri()) {
        if($innslag->getType()->getKey() == 'utstilling') {
            foreach($innslag->getTitler()->getAll() as $utstilling ){
                if($bilde->getId() == $utstilling->getBildeId()) {
                    $utstilling->setBildeId(-1);
                    $utstilling->setPlaybackId(-1);
                    WriteTitler::save($utstilling);
                }
            }
        }
    }

    // Slett fra relatert
    $delete_rel = new Delete(
        'ukmno_wp_related',
        [
            'blog_id' => get_current_blog_id(),
            'post_id' => $bilde->getPostId(),
            'post_type' => 'image'
        ]
    );
    $res_rel = $delete_rel->run();

    // Slett fra bilder
    $delete_bilde = new Delete(
        'ukm_bilder',
        [
            'id' => $_POST['bildeId']
        ]
    );
    $res_bilde = $delete_bilde->run();

    // Slett fra wordpress
    $res_wp = wp_delete_attachment($bilde->getPostId(), true);


    UKMbilder::addResponseData(
        'success',
        $res_bilde && $res_rel && $res_wp
    );
}
