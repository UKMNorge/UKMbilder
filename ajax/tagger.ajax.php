<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Database\SQL\Query;

if( ! isset($_POST['tagData']) ){
    http_response_code(400);
    die();
} 

// split POST-data
$tagData    = $_POST['tagData'];
$innslagId  = $tagData->innslagId;
$imageId    = $tagData->imageId;
$fotografId = $tagData->fotografId;

// declare UKM-data
$arrangement = new Arrangement( get_option('pl_id') );
$innslag = $arrangement->getInnslag()->get( $innslagId );


// // get wp_post <-> ukm_bilder relation

// $sql = new Query("SELECT `wp_post`
// 					FROM `ukm_bilder`
// 					WHERE `id` = '#id'",
// 					array('id' => $id));
// 	$image = $sql->getArray();

// // UPDATE WORDPRESS IMAGE NAMES
// global $wpdb;
// require_once(ABSPATH . 'wp-admin/includes/image.php');
// require_once(ABSPATH . 'wp-includes/post.php');


// update_post_meta($image['wp_post'], '_wp_attachment_image_alt', $innslag->get('b_name'));
// $wpdb->update($wpdb->posts,
//               array('post_author'	=> $fotografId,
//                       'post_title'	=> $innslag->get('b_name')),
//               array('ID' => $image['wp_post'])
//              );




$imageId = $_POST['imageId'] ? intval($_POST['imageId']) : 0; //TODO: handle non-integer input with UKM-approved method





UKMbilder::addResponseData('tagStatus', $tagData);

