<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Database\SQL\Query;
use UKMNorge\Database\SQL\Update;

if( ! isset($_POST['tagData']) ){
    http_response_code(400);
    die();
} 

// split POST-data


$tagData    = $_POST['tagData'];
$innslagId  = $tagData['innslagId'];
$imageId    = $tagData['imageId'];
$fotografId = $tagData['fotografId'];

if ( ! ($fotografId && $innslagId && $fotografId ) ) {
    // TODO: handle invalid request to ajax-controller
}



// declare UKM-data
$arrangement = new Arrangement( get_option('pl_id') );
$innslag = $arrangement->getInnslag()->get( $innslagId );


$sql = new Query("SELECT `wp_post`
					FROM `ukm_bilder`
					WHERE `id` = '#id'",
					array('id' => $imageId));
$wpPostId = $sql->getArray();
$wpPostId = $wpPostId['wp_post'];
if ( ! $wpPostId  ) {
    //TODO: Handle error when non-valid imageId is passed
}

//testing retrieval of photographer

/* @var WP_Post $fotograf */
$fotograf = get_user_by( 'id', $fotografId );

update_post_meta($wpPostId, '_wp_attachment_image_alt', $innslag->getNavn());
wp_update_post([
    'ID' => $wpPostId,
    'post_title' => $innslag->getNavn(),
    'post_author' => $fotografId
]);

// UPDATE BILDER-TABLE
$update = new Update('ukm_bilder', array('id' => $imageId));
$update->add('wp_uid', $fotografId );
$update->add('b_id', $innslag->getId() );
// if( isset( $_POST['c_id'] ) ) {
//     $update->add('c_id', $_POST['c_id']);
// }
$update->add('status', 'tagged');
$update->run();


// RELATE IMAGE
$meta = wp_get_attachment_metadata( $wpPostId );
$folder = substr( $meta['file'] , 0 , strrpos( $meta['file'] , '/' )+1 );
foreach($meta['sizes'] as $size => $info) {
    $meta['sizes'][$size]['file'] = str_replace('//','/',$folder.$meta['sizes'][$size]['file']);
}

require_once('UKM/related.class.php');
$rel = new related($innslag->getId());
$rel->set( $image['wp_post'], 'image', [
    'file'		=> $meta['file'],
    'sizes'	=> $meta['sizes'],
    'author'	=> $fotografId 
]);


// $imageId = $_POST['imageId'] ? intval($_POST['imageId']) : 0; //TODO: handle non-integer input with UKM-approved method


UKMbilder::addResponseData('storedTag', [
    'postId' => intval($wpPostId),
    'imageId' => intval($imageId),
    'innslagId' => $innslag->getId(), 
    'fotografId' => intval($fotografId), 
]);

